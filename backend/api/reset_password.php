<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? '';
$password = $data['password'] ?? '';

if (empty($token) || empty($password)) {
    die(json_encode(['error' => 'Dados incompletos.']));
}

try {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die(json_encode(['error' => 'Token inválido ou expirado.']));
    }

    $valid = validatePasswordComplexity($password);
    if ($valid !== true) {
        die(json_encode(['error' => $valid]));
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password and clear token
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
    $stmt->execute([$hash, $user['id']]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao processar: ' . $e->getMessage()]);
}
