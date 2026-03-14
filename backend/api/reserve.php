<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

$rifa_id = isset($data['rifa_id']) ? intval($data['rifa_id']) : 1;
$nome = $data['nome'] ?? '';
$whatsapp = $data['whatsapp'] ?? '';
$numerosSelecionados = $data['numeros'] ?? [];

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
    $stmt = $pdo->prepare("SELECT preco_numero FROM rifas WHERE id = ?");
    $stmt->execute([$rifa_id]);
    $preco = $stmt->fetchColumn();
    $total = count($numerosSelecionados) * $preco;

    // Prepara gateway (Try catch para não quebrar caso a tabela não exista)
    $gateway = '';
    $token = '';
    $tempo_pagamento = 3; // Padrão
    try {
        $stmtConf = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('gateway', 'gateway_token', 'tempo_pagamento')");
        if($stmtConf) {
            $configs = $stmtConf->fetchAll(PDO::FETCH_KEY_PAIR);
            $gateway = $configs['gateway'] ?? '';
            $token = $configs['gateway_token'] ?? '';
            if(isset($configs['tempo_pagamento']) && is_numeric($configs['tempo_pagamento'])) {
                $tempo_pagamento = (int)$configs['tempo_pagamento'];
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
        $txid_efi = str_replace('.', '', uniqid('RESERVA', true)); 
        $body = [
            "calendario" => ["expiracao" => $tempo_pagamento * 60],
            "valor" => ["original" => number_format($total, 2, '.', '')],
            "chave" => $token, 
            "solicitacaoPagador" => "Pagamento da Rifa $rifa_id"
        ];

        $ch = curl_init("https://pix.api.efipay.com.br/v2/cob/" . $txid_efi);
        curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $access_token",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $cob_res = curl_exec($ch);
        curl_close($ch);
        $cob_data = json_decode($cob_res, true);

        if(!isset($cob_data['loc']['id'])) {
            $err_msg = $cob_data['mensagem'] ?? 'Falha ao criar cobrança (Verifique a Chave PIX).';
            throw new Exception("Erro Cob Efí: " . $err_msg);
        }

        // 3. GERAR QR CODE
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
            $txid = $txid_efi;
        } else {
            throw new Exception("Erro QR Efí: Falha ao gerar código.");
        }
    }

    // Inserir reserva
    $stmt = $pdo->prepare("INSERT INTO reservas (rifa_id, nome, whatsapp, valor_total, data_reserva, status, pix_txid, pix_qrcode, pix_copiacola) VALUES (?, ?, ?, ?, NOW(), 'pendente', ?, ?, ?)");
    $stmt->execute([$rifa_id, $nome, $whatsapp, $total, $txid, $pix_qrcode, $pix_copiacola]);
    $reserva_id = $pdo->lastInsertId();

    // Atualizar números
    $updateStmt = $pdo->prepare("UPDATE numeros SET status = 'reservado', reserva_id = ? WHERE rifa_id = ? AND numero = ? AND status = 'disponivel'");
    foreach($numerosSelecionados as $num) {
        $updateStmt->execute([$reserva_id, $rifa_id, $num]);
    }

    $pdo->commit();

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
