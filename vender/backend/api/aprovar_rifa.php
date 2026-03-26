<?php
header('Content-Type: application/json');
require_once '../config.php';

// Proteção da Página (Somente Admin Global)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['rifa_id'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos.']);
    exit;
}

$rifa_id = (int)$data['rifa_id'];

try {
    $stmt = $pdo->prepare("UPDATE rifas SET status = 'ativa' WHERE id = ?");
    $stmt->execute([$rifa_id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
