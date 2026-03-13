<?php
header('Content-Type: application/json');
require_once '../config.php';

$reserva_id = $_GET['id'] ?? 0;
if(!$reserva_id) {
    die(json_encode(['error' => 'No ID provided']));
}

$stmt = $pdo->prepare("SELECT status FROM reservas WHERE id = ?");
$stmt->execute([$reserva_id]);
$status = $stmt->fetchColumn();

if($status === 'pago') {
    echo json_encode(['status' => 'pago']);
} else if ($status === 'expirado') {
    echo json_encode(['status' => 'expirado']);
} else {
    echo json_encode(['status' => 'pendente']);
}
?>
