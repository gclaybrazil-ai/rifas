<?php
/*
  Simulação de Webhook (MercadoPago ou Gerencianet)
  Na vida real, a API de pagamento fará um POST nesta URL quando um PIX for pago.
  Para testar: Envie um POST via Postman ou curl:
  {
      "txid": "SEU_TXID_CRIADO",
      "status": "approved"
  }
*/
header('Content-Type: application/json');
require_once '../config.php';

$payload = file_get_contents('php://input');
file_put_contents('webhook_log.txt', "[" . date('Y-m-d H:i:s') . "] Payload: " . $payload . PHP_EOL, FILE_APPEND);
$data = json_decode($payload, true);
$txid = $data['txid'] ?? '';
$statusPost = $data['status'] ?? ''; 

// Suporte para Efí Bank (Envia uma lista em 'pix')
if (isset($data['pix']) && is_array($data['pix'])) {
    foreach ($data['pix'] as $item) {
        if (isset($item['txid'])) {
            $txid = $item['txid'];
            $statusPost = 'approved'; // Na Efí, se chegou no webhook de PIX, é porque foi pago
            break; 
        }
    }
}

if (empty($txid)) {
    die(json_encode(['error' => 'Faltam dados do webhook']));
}

// Aceita 'approved' (MP) ou vácuo da Efí que já definimos acima
if ($statusPost !== 'approved' && !empty($statusPost)) {
    die(json_encode(['msg' => 'Ignorado']));
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("SELECT id, status, afiliado_id, valor_total FROM reservas WHERE pix_txid = ?");
    $stmt->execute([$txid]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reserva && $reserva['status'] === 'pendente') {
        // Marca como pago
        $pdo->prepare("UPDATE reservas SET status = 'pago' WHERE id = ?")->execute([$reserva['id']]);
        $pdo->prepare("UPDATE numeros SET status = 'pago' WHERE reserva_id = ?")->execute([$reserva['id']]);
        
        // Lógica de Comissão de Afiliado
        if (!empty($reserva['afiliado_id'])) {
            $afId = intval($reserva['afiliado_id']);
            $valorTotal = (float)$reserva['valor_total'];
            
            // Busca % de comissão
            $stmtC = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'comissao_padrao'");
            $comissionPct = (float)($stmtC->fetchColumn() ?: 10.00);
            $comissao = round(($valorTotal * $comissionPct) / 100, 2);
            
            // Atualiza saldo do afiliado
            $pdo->prepare("UPDATE afiliados SET saldo = saldo + ?, total_ganho = total_ganho + ?, vendas_pagas = vendas_pagas + 1 WHERE id = ?")
                ->execute([$comissao, $comissao, $afId]);

            // Verificar saque automático
            $stmtBal = $pdo->prepare("SELECT id, saldo, pix_key FROM afiliados WHERE id = ?");
            $stmtBal->execute([$afId]);
            $af = $stmtBal->fetch(PDO::FETCH_ASSOC);
            
            $stmtMin = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'minimo_saque'");
            $minSaque = (float)($stmtMin->fetchColumn() ?: 20.00);
            
            if ($af && $af['saldo'] >= $minSaque) {
                $valorSaque = $af['saldo'];
                // Zera saldo para o saque
                $pdo->prepare("UPDATE afiliados SET saldo = 0 WHERE id = ?")->execute([$afId]);
                // Registra o saque como pendente (o envio real pode ser via cron ou automação de API)
                $pdo->prepare("INSERT INTO saques (afiliado_id, valor, chave_pix, status) VALUES (?, ?, ?, 'pendente')")
                    ->execute([$afId, $valorSaque, $af['pix_key']]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } else {
        $pdo->rollBack();
        echo json_encode(['error' => 'Reserva não encontrada ou já processada']);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => $e->getMessage()]);
}
?>