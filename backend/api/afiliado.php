<?php
header('Content-Type: application/json');
require_once '../config.php';
require_once '../libs/PHPMailer/PHPMailer.php';
require_once '../libs/PHPMailer/SMTP.php';
require_once '../libs/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Helper function to send emails
function sendMailer($to_email, $to_name, $subject, $message, $pdo) {
    $stmtConf = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave LIKE 'smtp_%'");
    $conf = $stmtConf->fetchAll(PDO::FETCH_KEY_PAIR);

    $host = $conf['smtp_host'] ?? '';
    $port = (int)($conf['smtp_port'] ?? 465);
    $user_smtp = $conf['smtp_user'] ?? '';
    $pass_smtp = $conf['smtp_pass'] ?? '';
    $from_name = $conf['smtp_from_name'] ?? 'Admin Sorte';
    $from_email = $conf['smtp_from_email'] ?? 'noreply@seusite.com';

    $mail = new PHPMailer(true);
    try {
        if (!empty($host) && !empty($user_smtp) && !empty($pass_smtp)) {
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user_smtp;
            $mail->Password   = $pass_smtp;
            $mail->SMTPSecure = ($port == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($to_email, $to_name);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            return $mail->send();
        } else {
            $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
            return mail($to_email, $subject, $message, $headers);
        }
    } catch (Exception $e) {
        return false;
    }
}

if ($action === 'login_register') {
    $whatsapp = preg_replace('/\D/', '', $_POST['whatsapp'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $pix_key = trim($_POST['pix_key'] ?? '');

    if (empty($whatsapp)) die(json_encode(['error' => 'WhatsApp é obrigatório.']));

    $stmt = $pdo->prepare("SELECT * FROM afiliados WHERE whatsapp = ?");
    $stmt->execute([$whatsapp]);
    $afiliado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($afiliado) {
        // Login flow
        if (empty($senha)) die(json_encode(['error' => 'Informe sua senha.']));
        if (password_verify($senha, $afiliado['senha'])) {
            $_SESSION['afiliado_id'] = $afiliado['id'];
            echo json_encode(['success' => true, 'message' => 'Login realizado!']);
        } else {
            echo json_encode(['error' => 'Senha incorreta.']);
        }
    } else {
        // Register flow
        if (empty($nome) || empty($pix_key) || empty($email) || empty($senha)) {
            die(json_encode(['error' => 'Para novo cadastro, preencha todos os campos.']));
        }
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO afiliados (nome, whatsapp, email, senha, pix_key) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $whatsapp, $email, $hash, $pix_key]);
            $_SESSION['afiliado_id'] = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Cadastro realizado!']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) die(json_encode(['error' => 'Email ou WhatsApp já cadastrado.']));
            die(json_encode(['error' => 'Erro ao cadastrar: ' . $e->getMessage()]));
        }
    }

} else if ($action === 'forgot_password') {
    $whatsapp = preg_replace('/\D/', '', $_POST['whatsapp'] ?? '');
    if (empty($whatsapp)) die(json_encode(['error' => 'Informe o WhatsApp.']));

    $stmt = $pdo->prepare("SELECT id, nome, email FROM afiliados WHERE whatsapp = ?");
    $stmt->execute([$whatsapp]);
    $af = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$af) die(json_encode(['error' => 'WhatsApp não encontrado.']));

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $pdo->prepare("INSERT INTO afiliado_tokens (afiliado_id, token, tipo, data_expiracao) VALUES (?, ?, 'reset_senha', ?)")
        ->execute([$af['id'], $token, $expires]);

    $link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . str_replace('backend/api/afiliado.php', 'afiliado.php', $_SERVER['PHP_SELF']) . "?token=" . $token;
    
    $subject = "Recuperação de Senha - Afiliado";
    $message = "Olá {$af['nome']},\n\nPara redefinir sua senha de acesso ao painel de afiliados, clique no link abaixo:\n\n{$link}\n\nO link expira em 1 hora.";

    if (sendMailer($af['email'], $af['nome'], $subject, $message, $pdo)) {
        echo json_encode(['success' => true, 'message' => 'Link de recuperação enviado para seu email.']);
    } else {
        echo json_encode(['error' => 'Falha ao enviar email. ' . (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ? "Simulação (Local): $link" : "")]);
    }

} else if ($action === 'verify_token') {
    $token = $_GET['token'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM afiliado_tokens WHERE token = ? AND usado = 0 AND data_expiracao > NOW()");
    $stmt->execute([$token]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$t) die(json_encode(['error' => 'Link inválido ou expirado.']));
    echo json_encode(['success' => true, 'tipo' => $t['tipo']]);

} else if ($action === 'execute_token') {
    $token = $_POST['token'] ?? '';
    $valor = $_POST['valor'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM afiliado_tokens WHERE token = ? AND usado = 0 AND data_expiracao > NOW()");
    $stmt->execute([$token]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$t) die(json_encode(['error' => 'Link inválido ou expirado.']));

    $pdo->beginTransaction();
    try {
        if ($t['tipo'] === 'reset_senha') {
            if (strlen($valor) < 6) throw new Exception("A senha deve ter pelo menos 6 caracteres.");
            $hash = password_hash($valor, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE afiliados SET senha = ? WHERE id = ?")->execute([$hash, $t['afiliado_id']]);
        } else if ($t['tipo'] === 'update_pix') {
            $pdo->prepare("UPDATE afiliados SET pix_key = ? WHERE id = ?")->execute([$t['novo_valor'], $t['afiliado_id']]);
        } else if ($t['tipo'] === 'update_email') {
            $pdo->prepare("UPDATE afiliados SET email = ? WHERE id = ?")->execute([$t['novo_valor'], $t['afiliado_id']]);
        }

        $pdo->prepare("UPDATE afiliado_tokens SET usado = 1 WHERE id = ?")->execute([$t['id']]);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Operação realizada com sucesso!']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
    }

} else if ($action === 'get_stats') {
    if (!isset($_SESSION['afiliado_id'])) die(json_encode(['error' => 'Não logado']));

    $stmt = $pdo->prepare("SELECT id, nome, whatsapp, email, pix_key, saldo, total_ganho, vendas_pagas FROM afiliados WHERE id = ?");
    $stmt->execute([$_SESSION['afiliado_id']]);
    $af = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmtR = $pdo->query("SELECT id, nome FROM rifas WHERE status = 'aberta' ORDER BY id DESC");
    $rifas = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'afiliado' => $af,
        'rifas' => $rifas,
        'site_url' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . str_replace('/backend/api/afiliado.php', '', $_SERVER['PHP_SELF'])
    ]);

} else if ($action === 'request_update') {
    if (!isset($_SESSION['afiliado_id'])) die(json_encode(['error' => 'Não logado']));
    $tipo = $_POST['tipo'] ?? ''; // 'pix' ou 'email'
    $novo_valor = trim($_POST['valor'] ?? '');

    if (empty($novo_valor)) die(json_encode(['error' => 'Novo valor é obrigatório.']));

    $stmt = $pdo->prepare("SELECT id, nome, email FROM afiliados WHERE id = ?");
    $stmt->execute([$_SESSION['afiliado_id']]);
    $af = $stmt->fetch(PDO::FETCH_ASSOC);

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $tipo_token = ($tipo === 'pix') ? 'update_pix' : 'update_email';

    $pdo->prepare("INSERT INTO afiliado_tokens (afiliado_id, token, tipo, novo_valor, data_expiracao) VALUES (?, ?, ?, ?, ?)")
        ->execute([$af['id'], $token, $tipo_token, $novo_valor, $expires]);

    $link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . str_replace('backend/api/afiliado.php', 'afiliado.php', $_SERVER['PHP_SELF']) . "?token=" . $token;
    
    $label = ($tipo === 'pix') ? "PIX" : "Email";
    $subject = "Confirmação de Alteração - {$label}";
    $message = "Olá {$af['nome']},\n\nVocê solicitou a alteração do seu {$label} para: {$novo_valor}.\n\nPara confirmar esta alteração por segurança, clique no link abaixo:\n\n{$link}\n\nSe não foi você, ignore este email.";

    if (sendMailer($af['email'], $af['nome'], $subject, $message, $pdo)) {
        echo json_encode(['success' => true, 'message' => "Um link de confirmação foi enviado para o seu email atual ({$af['email']})."]);
    } else {
        echo json_encode(['error' => 'Falha ao enviar email. ' . (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ? "Simulação (Local): $link" : "")]);
    }

} else if ($action === 'logout') {
    unset($_SESSION['afiliado_id']);
    echo json_encode(['success' => true]);
}
