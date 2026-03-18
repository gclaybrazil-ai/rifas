<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);
$user = $data['username'] ?? '';
$pass = $data['password'] ?? '';
$lat = $data['lat'] ?? null;
$lng = $data['lng'] ?? null;
$action = $data['action'] ?? 'login';

if ($action === 'check_auth') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("SELECT u.id, u.username FROM usuarios u 
                           JOIN login_autorizacoes la ON u.id = la.user_id 
                           WHERE u.username = ? AND la.ip = ? AND la.autorizado = 1 
                           AND la.user_type = 'admin' 
                           AND la.data_autenticacao > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $stmt->execute([$user, $ip]);
    $auth = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($auth) {
        $_SESSION['admin_logged'] = true;
        registrarLog('acao_admin', "Acesso via novo local autorizado e logado automaticamente", null, $auth['id']);
        echo json_encode(['authorized' => true]);
    } else {
        echo json_encode(['authorized' => false]);
    }
    exit;
}

if ($action === 'test_location') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $address = getAddressFromCoords($lat, $lng);
    echo json_encode(['success' => true, 'ip' => $ip, 'address' => $address]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, password FROM usuarios WHERE username = ?");
    $stmt->execute([$user]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if($usuario && password_verify($pass, $usuario['password'])) {
        // Fetch Admin Email for challenge
        $stmtEmail = $pdo->query("SELECT email, username FROM usuarios WHERE id = " . $usuario['id']);
        $adm = $stmtEmail->fetch(PDO::FETCH_ASSOC);

        $check = checkLocationChallenge('admin', $usuario['id'], $adm['email'] ?? 'admin@seusite.com', $adm['username'] ?? 'Administrador', $lat, $lng);
        if (isset($check['challenge'])) {
            echo json_encode(['challenge_required' => true, 'message' => 'Novo local detectado. Um link de autorização foi enviado para seu email.']);
            exit;
        }

        $_SESSION['admin_logged'] = true;
        registrarLog('acao_admin', "Login administrativo realizado", null, $usuario['id'], $lat, $lng);
        echo json_encode(['success' => true]);
    } else {
        registrarLog('acao_admin', "Tentativa de login falhou (usuário: $user)", null, null, $lat, $lng);
        echo json_encode(['error' => 'Usuário ou senha inválidos']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro interno ao validar login: ' . $e->getMessage()]);
}
?>
