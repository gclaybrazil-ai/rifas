<?php
header('Content-Type: application/json');
require_once '../config.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT status FROM rifas WHERE id = ?");
$stmt->execute([$id]);
$rifa = $stmt->fetch();

echo json_encode(['status' => $rifa['status'] ?? 'unknown']);
