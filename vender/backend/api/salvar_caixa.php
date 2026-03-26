<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit;
}

$u_id = $_SESSION['usuario_id'];
$r_id = (int)($_POST['rifa_id'] ?? 0);
$qtd = (int)($_POST['qtd_minima'] ?? 0);
$premio = trim($_POST['premio_descricao'] ?? '');

if ($r_id <= 0 || $qtd <= 0 || empty($premio)) {
    echo json_encode(['error' => 'Preecha todos os campos. Mínimo de 1 cota.']);
    exit;
}

// Verificar se a rifa pertence ao usuário
$stmt = $pdo->prepare("SELECT id FROM rifas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$r_id, $u_id]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'Rifa não encontrada.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO caixas_premiadas (rifa_id, qtd_minima, premio_descricao) VALUES (?, ?, ?)");
    $stmt->execute([$r_id, $qtd, $premio]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao salvar meta: ' . $e->getMessage()]);
}
