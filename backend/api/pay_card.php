<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

$reserva_id = isset($data['reserva_id']) ? intval($data['reserva_id']) : null;
$card_data = $data['card_data'] ?? null;

if(!$reserva_id || !$card_data) {
    die(json_encode(['error' => 'Dados incompletos para pagamento via cartão.']));
}

$pdo->beginTransaction();

try {
    // Look up reservation
    $stmt = $pdo->prepare("SELECT * FROM reservas WHERE id = ? FOR UPDATE");
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        throw new Exception("Reserva inválida.");
    }

    if ($reserva['status'] !== 'pendente') {
        throw new Exception("Esta reserva não está mais pendente (Já foi paga ou expirou).");
    }

    $total = (float)$reserva['valor_total'];
    $whatsapp = $reserva['whatsapp'];
    $nome = $reserva['nome'];
    $rifa_id = $reserva['rifa_id'];

    // MP API config
    $stmtConf = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('gateway', 'gateway_token')");
    $configs = $stmtConf->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $token = $configs['gateway_token'] ?? '';
    if (empty($token) || ($configs['gateway'] ?? '') !== 'mercadopago') {
        throw new Exception("Gateway inválido para cartão de crédito.");
    }

    $mp_data = $card_data;
    $mp_data['transaction_amount'] = $total;
    if(!isset($mp_data['payer']['email'])) {
         $mp_data['payer']['email'] = preg_replace('/\D/', '', $whatsapp) . "@supersorte.com.br";
    }

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

    if (isset($mp_result['status']) && ($mp_result['status'] === 'approved' || $mp_result['status'] === 'authorized')) {
        $txid = $mp_result['id'];
        
        $pdo->prepare("UPDATE reservas SET status = 'pago', pix_txid = ? WHERE id = ?")->execute([$txid, $reserva_id]);
        $pdo->prepare("UPDATE numeros SET status = 'pago' WHERE reserva_id = ?")->execute([$reserva_id]);
        
        $afId = !empty($reserva['afiliado_id']) ? intval($reserva['afiliado_id']) : 0;
        if ($afId > 0) {
            registrarVendaAfiliado($afId, $total, $rifa_id, $reserva_id);
        }
        $pdo->commit();

        // --- WHATSAPP NOTIFICATION ---
        try {
            require_once 'whatsapp_helper.php';
            $stmtW = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'whatsapp_notify_pago'");
            if ($stmtW->fetchColumn() === '1') {
                $stmtRi = $pdo->prepare("SELECT premio1, premio2, premio3, premio4, premio5 FROM rifas WHERE id = ?");
                $stmtRi->execute([$rifa_id]);
                $rifa_data = $stmtRi->fetch(PDO::FETCH_ASSOC);

                $stmtN = $pdo->prepare("SELECT numero FROM numeros WHERE reserva_id = ?");
                $stmtN->execute([$reserva_id]);
                $numerosArr = $stmtN->fetchAll(PDO::FETCH_COLUMN);

                $prizes = "";
                for($i=1; $i<=5; $i++) {
                    $prop = "premio" . $i;
                    if(!empty($rifa_data[$prop])) {
                        $prizes .= "\n- " . $i . "º Prêmio: " . $rifa_data[$prop];
                    }
                }
                
                $msgPago = "✅ *PAGAMENTO CONFIRMADO!*\n\n";
                $msgPago .= "Olá *" . $nome . "*,\n";
                $msgPago .= "Seu pagamento via Cartão de Crédito na Rifa *#" . $rifa_id . "* foi aprovado com sucesso!\n\n";
                $msgPago .= "🎁 *Prêmios em jogo:*" . $prizes . "\n\n";
                $msgPago .= "🎫 *Reserva #{$reserva_id}*\n";
                $msgPago .= "🎫 *Seus números:* " . implode(', ', $numerosArr) . "\n\n";
                $msgPago .= "Obrigado e Boa Sorte! 🍀 Acompanhe o sorteio no site.";
                sendWhatsAppMessage($whatsapp, $msgPago);
            }
        } catch (Exception $eW) {
            file_put_contents(__DIR__ . '/whatsapp_errors.log', "Erro Env Card: " . $eW->getMessage() . "\n", FILE_APPEND);
        }

        echo json_encode(['success' => true, 'status' => 'pago']);

    } else {
        $status_detail = $mp_result['status_detail'] ?? '';
        
        $error_messages = [
            'accredited' => 'Pagamento aprovado.',
            'pending_contigency' => 'Pagamento pendente. Aguardando confirmação.',
            'pending_review_manual' => 'Em análise manual pelo Mercado Pago. Como sua reserva expira rápido, recomendamos usar o PIX ou tentar outro cartão.',
            'cc_rejected_bad_filled_card_number' => 'Número do cartão incorreto.',
            'cc_rejected_bad_filled_date' => 'Data de validade incorreta.',
            'cc_rejected_bad_filled_other' => 'Dados do cartão incorretos.',
            'cc_rejected_bad_filled_security_code' => 'Código de segurança incorreto (CVV).',
            'cc_rejected_blacklist' => 'Não pudemos processar seu pagamento. Tente novamente com o PIX.',
            'cc_rejected_call_for_authorize' => 'Você precisa autorizar o pagamento junto ao emissor do cartão.',
            'cc_rejected_card_disabled' => 'O cartão está inativo. Ligue para a administradora.',
            'cc_rejected_card_error' => 'Não conseguimos processar o pagamento. Tente outro cartão.',
            'cc_rejected_duplicated_payment' => 'Você já efetuou um pagamento com este valor. Tente novamente após alguns minutos.',
            'cc_rejected_high_risk' => 'O pagamento foi recusado por medidas de segurança do Mercado Pago. Tente usar o PIX.',
            'cc_rejected_insufficient_amount' => 'Seu cartão possui saldo insuficiente.',
            'cc_rejected_invalid_installments' => 'O cartão não processa o número de parcelas escolhido.',
            'cc_rejected_max_attempts' => 'Você atingiu o limite de tentativas com este cartão.',
            'cc_rejected_other_reason' => 'Cartão recusado. Tente novamente com outro cartão ou use PIX.'
        ];

        $rejeicao_motivo = $error_messages[$status_detail] ?? ($mp_result['message'] ?? 'Pagamento recusado pelo modelo bancário.');
        
        throw new Exception($rejeicao_motivo);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => $e->getMessage()]);
}
?>
