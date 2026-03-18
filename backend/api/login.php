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
        // Fetch Admin Email for challenge
        $stmtEmail = $pdo->query("SELECT email, username FROM usuarios WHERE id = " . $usuario['id']);
        $adm = $stmtEmail->fetch(PDO::FETCH_ASSOC);

        $check = checkLocationChallenge('admin', $usuario['id'], $adm['email'] ?? 'admin@seusite.com', $adm['username'] ?? 'Administrador');
        if (isset($check['challenge'])) {
            echo json_encode(['challenge_required' => true, 'message' => 'Novo local detectado. Um link de autorização foi enviado para seu email.']);
            exit;
        }

        $_SESSION['admin_logged'] = true;
        registrarLog('acao_admin', "Login administrativo realizado com sucesso", null, $usuario['id']);
        echo json_encode(['success' => true]);
    } else {
        registrarLog('acao_admin', "Tentativa de login falhou (usuário: $user)");
        echo json_encode(['error' => 'Usuário ou senha inválidos']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro interno ao validar login: ' . $e->getMessage()]);
}
?>
