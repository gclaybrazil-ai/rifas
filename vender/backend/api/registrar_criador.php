<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// Validações Básicas
if (empty($nome) || empty($whatsapp) || empty($email) || empty($senha)) {
    echo json_encode(['error' => 'Preencha todos os campos obrigatórios']);
    exit;
}

if ($senha !== $confirmar_senha) {
    echo json_encode(['error' => 'As senhas não coincidem']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'E-mail inválido']);
    exit;
}

if (strlen($senha) < 6) {
    echo json_encode(['error' => 'A senha deve ter pelo menos 6 caracteres']);
    exit;
}

try {
    // Verificar se e-mail já existe no novo banco SaaS
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Este e-mail já está cadastrado no SaaS']);
        exit;
    }

    // Verificar se WhatsApp já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE whatsapp = ?");
    $stmt->execute([$whatsapp]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Este WhatsApp já está cadastrado no SaaS']);
        exit;
    }

    // Inserir Usuário Criador no novo banco
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Username gerado automaticamente
    $username = strtolower(explode(' ', $nome)[0]) . rand(100, 999);
    
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, username, email, password, whatsapp, role, status) VALUES (?, ?, ?, ?, ?, 'criador', 'ativo')");
    $stmt->execute([$nome, $username, $email, $senha_hash, $whatsapp]);
    
    $usuario_id = $pdo->lastInsertId();

    // Criar configuração inicial de gateway (vazio)
    $stmt = $pdo->prepare("INSERT INTO criador_config (usuario_id) VALUES (?)");
    $stmt->execute([$usuario_id]);

    echo json_encode(['success' => true, 'message' => 'Conta SaaS criada com sucesso!']);

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao processar cadastro SaaS: ' . $e->getMessage()]);
}
