<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$login_user = trim($_POST['login_user'] ?? '');
$login_pass = $_POST['login_pass'] ?? '';

if (empty($login_user) || empty($login_pass)) {
    echo json_encode(['error' => 'Preencha todos os campos']);
    exit;
}

try {
    // Buscar usuário no NOVO BANCO SaaS
    $stmt = $pdo->prepare("SELECT id, username, email, password, role, status FROM usuarios WHERE email = ? OR username = ?");
    $stmt->execute([$login_user, $login_user]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($login_pass, $usuario['password'])) {
        
        if ($usuario['status'] !== 'ativo') {
            echo json_encode(['error' => 'Sua conta SaaS está ' . $usuario['status']]);
            exit;
        }

        // Login Bem-sucedido (Somente no SaaS)
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_role'] = $usuario['role'];
        $_SESSION['usuario_nome'] = $usuario['username'];
        $_SESSION['usuario_logged_time'] = time();

        echo json_encode(['success' => true, 'role' => $usuario['role']]);

    } else {
        echo json_encode(['error' => 'E-mail/Usuário ou senha incorretos no SaaS']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro interno ao validar login SaaS: ' . $e->getMessage()]);
}
