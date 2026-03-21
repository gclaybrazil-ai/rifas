<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

$rifa_id = isset($data['rifa_id']) ? intval($data['rifa_id']) : 1;
$nome = $data['nome'] ?? '';
$whatsapp = $data['whatsapp'] ?? '';
$numerosSelecionados = $data['numeros'] ?? [];
$afiliado_id = isset($data['afiliado_id']) && is_numeric($data['afiliado_id']) ? intval($data['afiliado_id']) : null;

if(empty($nome) || empty($whatsapp) || empty($numerosSelecionados)) {
    die(json_encode(['error' => 'Por favor, preencha todos os dados.']));
}

if(count($numerosSelecionados) > 20) {
    die(json_encode(['error' => 'Máximo de 20 números por reserva.']));
}

$pdo->beginTransaction();

try {
    // Verificar se os números estão disponíveis travando a linha (FOR UPDATE)
    $placeholders = implode(',', array_fill(0, count($numerosSelecionados), '?'));
    $sql = "SELECT numero, status FROM numeros WHERE rifa_id = ? AND numero IN ($placeholders) FOR UPDATE";
    $stmt = $pdo->prepare($sql);
    $params = array_merge([$rifa_id], $numerosSelecionados);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($rows) !== count($numerosSelecionados)) {
        throw new Exception("Algum número informado não existe.");
    }

    foreach($rows as $r) {
        if($r['status'] !== 'disponivel') {
            throw new Exception("O número {$r['numero']} já foi reservado ou pago por outro usuário.");
        }
    }

    // Calcular valor
    $stmt = $pdo->prepare("SELECT preco_numero, premio1, premio2, premio3, premio4, premio5 FROM rifas WHERE id = ?");
    $stmt->execute([$rifa_id]);
    $rifa_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $preco = $rifa_data['preco_numero'];
    $valor_original = count($numerosSelecionados) * $preco;
    $total = $valor_original;
    $valor_taxa_calculada = 0;

    // Prepara gateway (Try catch para não quebrar caso a tabela não exista)
    $gateway = '';
    $token = '';
    $tempo_pagamento = 3; // Padrão
    try {
        $stmtConf = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('gateway', 'gateway_token', 'tempo_pagamento', 'repassar_taxa')");
        if($stmtConf) {
            $configs = $stmtConf->fetchAll(PDO::FETCH_KEY_PAIR);
            $gateway = $configs['gateway'] ?? '';
            $token = $configs['gateway_token'] ?? '';
            
            if(isset($configs['tempo_pagamento']) && is_numeric($configs['tempo_pagamento'])) {
                $tempo_pagamento = (int)$configs['tempo_pagamento'];
            }

            // Repassar Taxa Logic (Efí 1.19%, Mercado Pago 1%)
            if (isset($configs['repassar_taxa']) && $configs['repassar_taxa'] === '1') {
                $feeRate = ($gateway === 'mercadopago') ? 0.01 : 0.0119;
                $novo_total = $valor_original / (1 - $feeRate);
                $total = round($novo_total, 2);
                $valor_taxa_calculada = $total - $valor_original;
            }
        }
    } catch(PDOException $e) {}

    // Valores padrão de simulação
    $txid = uniqid('PIX_');
    $pix_qrcode = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkCgMAAQQAATCwnmAAAAAASUVORK5CYII="; 
    $pix_copiacola = "00020101021226840014br.gov.bcb.pix2562pix.bcb.gov.br/api/v2/$txid";

    // INTEGRAÇÃO REAL MARCADO PAGO
    if($gateway === 'mercadopago' && !empty($token)) {
        $mp_data = [
            "transaction_amount" => (float)$total,
            "payment_method_id" => "pix",
            "payer" => [
                "email" => preg_replace('/\D/', '', $whatsapp) . "@supersorte.com.br",
                "first_name" => substr($nome, 0, 20)
            ]
        ];

        $ch = curl_init('https://api.mercadopago.com/v1/payments');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mp_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer $token",
            'X-Idempotency-Key: ' . uniqid("", true)
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $mp_result = json_decode($response, true);
        
        if(isset($mp_result['point_of_interaction']['transaction_data']['qr_code'])) {
            $pix_copiacola = $mp_result['point_of_interaction']['transaction_data']['qr_code'];
            $pix_qrcode = "data:image/jpeg;base64," . $mp_result['point_of_interaction']['transaction_data']['qr_code_base64'];
            $txid = $mp_result['id']; 
        } else {
            throw new Exception("Erro API Mercado Pago: " . ($mp_result['message'] ?? 'Verifique se seu Access Token Production é válido.'));
        }
    } 
    // INTEGRAÇÃO REAL EFÍ BANK (GERENCIANET)
    else if($gateway === 'efi') {
        $stmtConfEfi = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('efi_client_id', 'efi_client_secret')");
        $efi_conf = $stmtConfEfi->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $clientId = trim($efi_conf['efi_client_id'] ?? '');
        $clientSecret = trim($efi_conf['efi_client_secret'] ?? '');
        $certificate = __DIR__ . '/../certs/certificado_producao.p12';

        if(empty($clientId) || empty($clientSecret) || !file_exists($certificate)) {
            throw new Exception("Configurações da Efí incompletas (Client ID, Secret ou Certificado P12 faltando).");
        }

        // 1. OAUTH
        $ch = curl_init("https://pix.api.efipay.com.br/oauth/token");
        curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"grant_type": "client_credentials"}');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($clientId . ":" . $clientSecret),
            'Content-Type: application/json'
        ]);
        // SSL Config
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $auth_res = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if($auth_res === false) {
            throw new Exception("Erro de conexão cURL (Efí): " . $curl_error . " - Verifique o host.");
        }

        $auth_data = json_decode($auth_res, true);
        $access_token = $auth_data['access_token'] ?? '';

        if(!$access_token) {
            $err_msg = $auth_data['error_description'] ?? $auth_data['mensagem'] ?? 'Falha ao autenticar na Efí. Verifique se o seu Certificado corresponde às suas chaves de Produção. (HTTP '.$http_code.')';
            throw new Exception("Erro Auth Efí: " . $err_msg);
        }

        // 2. CRIAR COBRANÇA (COB)
        // Se viermos do Mercado Pago, o token pode estar sujo com o Access Token do MP
        $pix_key = (strpos($token, 'APP_USR') === 0) ? '' : $token;
        
        // Busca automática da chave Pix (EVP) se for Efí para não precisar digitar no painel
        // Outros sites buscam direto, então vamos listar as chaves EVP da conta
        $ch = curl_init("https://pix.api.efipay.com.br/v2/gn/evp");
        curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $res_keys = curl_exec($ch);
        curl_close($ch);
        $keys_data = json_decode($res_keys, true);

        if(isset($keys_data['chaves']) && count($keys_data['chaves']) > 0) {
            $pix_key = $keys_data['chaves'][0];
        }

        if(empty($pix_key)) {
            throw new Exception("Erro Cob Efí: Nenhuma chave PIX (EVP) encontrada na sua conta Efí. Por favor, crie uma chave aleatória no painel da Efí.");
        }

        $ch = curl_init("https://pix.api.efipay.com.br/v2/cob");
        $body = [
            "calendario" => ["expiracao" => $tempo_pagamento * 60],
            "valor" => ["original" => number_format($total, 2, '.', '')],
            "chave" => $pix_key,
            "solicitacaoPagador" => "Pagamento da reserva na plataforma."
        ];
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $access_token",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $cob_res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201 || $httpCode === 200) {
            $cob_data = json_decode($cob_res, true);
            $txid = $cob_data['txid'] ?? '';
            
            // Gerar QR Code
            $ch = curl_init("https://pix.api.efipay.com.br/v2/loc/" . $cob_data['loc']['id'] . "/qrcode");
            curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            $qr_res = curl_exec($ch);
            curl_close($ch);
            $qr_data = json_decode($qr_res, true);

            if(isset($qr_data['imagemQrcode'])) {
                $pix_qrcode = $qr_data['imagemQrcode'];
                $pix_copiacola = $qr_data['qrcode'];
            } else {
                throw new Exception("Erro QR Efí: Falha ao gerar código.");
            }
        } else {
            $cob_data = json_decode($cob_res, true);
            $err_msg = $cob_data['mensagem'] ?? 'Falha ao criar cobrança.';
            throw new Exception("Erro Cob Efí: " . $err_msg);
        }
    }

    // Inserir reserva
    $stmt = $pdo->prepare("INSERT INTO reservas (rifa_id, nome, whatsapp, valor_total, data_reserva, status, pix_txid, pix_qrcode, pix_copiacola, valor_taxa, afiliado_id) VALUES (?, ?, ?, ?, NOW(), 'pendente', ?, ?, ?, ?, ?)");
    $stmt->execute([$rifa_id, $nome, $whatsapp, $total, $txid, $pix_qrcode, $pix_copiacola, $valor_taxa_calculada, $afiliado_id]);
    $reserva_id = $pdo->lastInsertId();

    // Atualizar números
    $updateStmt = $pdo->prepare("UPDATE numeros SET status = 'reservado', reserva_id = ? WHERE rifa_id = ? AND numero = ? AND status = 'disponivel'");
    foreach($numerosSelecionados as $num) {
        $updateStmt->execute([$reserva_id, $rifa_id, $num]);
    }

    $pdo->commit();

    // --- NOTIFICAÇÃO WHATSAPP (RESERVA) ---
    try {
        $stmtW = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'whatsapp_notify_reserva'");
        $notifyEnabled = $stmtW->fetchColumn();
        
        if ($notifyEnabled === '1') {
            require_once 'whatsapp_helper.php';
            $prizes = "";
            for($i=1; $i<=5; $i++) {
                $prop = "premio" . $i;
                if(!empty($rifa_data[$prop])) {
                    $prizes .= "\n- " . $i . "º Prêmio: " . $rifa_data[$prop];
                }
            }
            
            $msgReserva = "🎫 *RESERVA REALIZADA!*\n\n";
            $msgReserva .= "Olá *" . $nome . "*,\n";
            $msgReserva .= "Você reservou números na rifa *#" . $rifa_id . "*\n\n";
            $msgReserva .= "🎁 *Prêmios em jogo:*" . $prizes . "\n\n";
            $msgReserva .= "🎫 *Seus números:* " . implode(', ', $numerosSelecionados) . "\n\n";
            $msgReserva .= "💰 *Total:* R$ " . number_format($total, 2, ',', '.') . "\n\n";
            $msgReserva .= "👇 *Pague via PIX para garantir sua participação:* \n\n" . $pix_copiacola;
            
            sendWhatsAppMessage($whatsapp, $msgReserva);
        }
    } catch (Exception $eW) {}
    // ------------------------------------

    echo json_encode([
        'success' => true, 
        'reserva_id' => $reserva_id, 
        'pix_qrcode' => $pix_qrcode, 
        'pix_copiacola' => $pix_copiacola, 
        'total' => $total, 
        'txid' => $txid,
        'expire_in' => $tempo_pagamento * 60 // Segundos
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => $e->getMessage()]);
}
?>
