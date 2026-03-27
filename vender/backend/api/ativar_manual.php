<?php
header('Content-Type: application/json');
require_once '../config.php';

// Apenas admin ou master pode ativar manualmente
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sem permissão.']);
    exit;
}

// Verificar se é admin
$stmtRole = $pdo->prepare("SELECT role FROM usuarios WHERE id = ?");
$stmtRole->execute([$_SESSION['usuario_id']]);
$role = $stmtRole->fetchColumn();

if ($role !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Apenas administradores podem ativar manualmente.']);
    exit;
}

$rifa_id = $_POST['rifa_id'] ?? 0;

if (!$rifa_id) {
    echo json_encode(['success' => false, 'error' => 'ID da rifa inválido.']);
    exit;
}

$stmt = $pdo->prepare("UPDATE rifas SET status = 'ativa' WHERE id = ?");
$stmt->execute([$rifa_id]);

file_put_contents(
    __DIR__ . '/webhook_log.txt',
    "Rifa $rifa_id ativada MANUALMENTE pelo admin ID {$_SESSION['usuario_id']} em " . date('Y-m-d H:i:s') . "\n",
    FILE_APPEND
);

echo json_encode(['success' => true, 'message' => 'Rifa ativada com sucesso!']);
