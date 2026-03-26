<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit;
}

$u_id = $_SESSION['usuario_id'];
$r_id = (int)($_POST['rifa_id'] ?? 0);
$numero = (int)($_POST['numero'] ?? -1);
$descricao = trim($_POST['descricao'] ?? '');

if ($r_id <= 0 || $numero < 0 || empty($descricao)) {
    echo json_encode(['error' => 'Preencha todos os campos corretamente.']);
    exit;
}

// Verificar se a rifa pertence ao usuário
$stmt = $pdo->prepare("SELECT id FROM rifas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$r_id, $u_id]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'Rifa não encontrada.']);
    exit;
}

// Verificar se o número já está premiado para essa rifa
$stmt = $pdo->prepare("SELECT id FROM titulos_premiados WHERE rifa_id = ? AND numero = ?");
$stmt->execute([$r_id, $numero]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Este número já possui um prêmio associado.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO titulos_premiados (rifa_id, numero, descricao) VALUES (?, ?, ?)");
    $stmt->execute([$r_id, $numero, $descricao]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao salvar título: ' . $e->getMessage()]);
}
