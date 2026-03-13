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

$data = json_decode(file_get_contents('php://input'), true);
$txid = $data['txid'] ?? '';
$statusPost = $data['status'] ?? ''; // 'approved'

if(empty($txid)) {
    die(json_encode(['error' => 'Faltam dados do webhook']));
}

if($statusPost !== 'approved') {
    die(json_encode(['msg' => 'Ignorado']));
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("SELECT id, status FROM reservas WHERE pix_txid = ?");
    $stmt->execute([$txid]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if($reserva && $reserva['status'] === 'pendente') {
        // Marca como pago
        $pdo->prepare("UPDATE reservas SET status = 'pago' WHERE id = ?")->execute([$reserva['id']]);
        $pdo->prepare("UPDATE numeros SET status = 'pago' WHERE reserva_id = ?")->execute([$reserva['id']]);
        $pdo->commit();
        echo json_encode(['success' => true]);
    } else {
        $pdo->rollBack();
        echo json_encode(['error' => 'Reserva não encontrada ou já processada']);
    }
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => $e->getMessage()]);
}
?>
