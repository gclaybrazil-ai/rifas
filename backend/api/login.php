<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$user = $data['username'] ?? '';
$pass = $data['password'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT id, password FROM usuarios WHERE username = ?");
    $stmt->execute([$user]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if($usuario && password_verify($pass, $usuario['password'])) {
        $_SESSION['admin_logged'] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Usuário ou senha inválidos']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro interno ao validar login']);
}
?>
