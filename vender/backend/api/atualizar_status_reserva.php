<?php
header('Content-Type: application/json');
require_once '../config.php';

// Proteção
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['reserva_id'], $data['novo_status'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos.']);
    exit;
}

$reserva_id = (int)$data['reserva_id'];
$novo_status = $data['novo_status']; // 'pago' ou 'cancelado'

try {
    $pdo->beginTransaction();

    // 1. Fetch Reservation (Verify Ownership)
    $stmt = $pdo->prepare("SELECT res.*, r.usuario_id 
                           FROM reservas res 
                           JOIN rifas r ON res.rifa_id = r.id 
                           WHERE res.id = ?");
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch();

    if (!$reserva || ($reserva['usuario_id'] != $_SESSION['usuario_id'] && $_SESSION['usuario_role'] !== 'admin')) {
        throw new Exception("Reserva não encontrada ou permissão insuficiente.");
    }

    if ($novo_status == 'pago') {
        // Update Reserva
        $stmt = $pdo->prepare("UPDATE reservas SET status = 'pago' WHERE id = ?");
        $stmt->execute([$reserva_id]);

        // Update Numbers
        $stmt = $pdo->prepare("UPDATE numeros SET status = 'pago' WHERE reserva_id = ?");
        $stmt->execute([$reserva_id]);
    } else if ($novo_status == 'cancelado') {
        // Update Reserva
        $stmt = $pdo->prepare("UPDATE reservas SET status = 'cancelado' WHERE id = ?");
        $stmt->execute([$reserva_id]);

        // DELETE Numbers to free them up
        $stmt = $pdo->prepare("DELETE FROM numeros WHERE reserva_id = ?");
        $stmt->execute([$reserva_id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
