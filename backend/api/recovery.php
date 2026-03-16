<?php
ob_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../config.php';
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

$data = json_decode(file_get_contents('php://input'), true);
$email_input = $data['email'] ?? '';

if (empty($email_input)) {
    die(json_encode(['error' => 'Informe o email cadastrado']));
}

try {
    // Add columns if not exists
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS email VARCHAR(255) DEFAULT ''");
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS reset_token VARCHAR(100) DEFAULT NULL");
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS reset_expires DATETIME DEFAULT NULL");

    $stmt = $pdo->prepare("SELECT id, username, email FROM usuarios WHERE email = ?");
    $stmt->execute([$email_input]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die(json_encode(['error' => 'Email não encontrado']));
    }

    // Generate link token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Save token (DO NOT change password yet)
    $stmt = $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_expires = ? WHERE id = ?");
    $stmt->execute([$token, $expires, $user['id']]);

    // Get SMTP Configuration
    $stmtConf = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave LIKE 'smtp_%'");
    $conf = $stmtConf->fetchAll(PDO::FETCH_KEY_PAIR);

    $host = $conf['smtp_host'] ?? '';
    $port = (int)($conf['smtp_port'] ?? 465);
    $user_smtp = $conf['smtp_user'] ?? '';
    $pass_smtp = $conf['smtp_pass'] ?? '';
    $from_name = $conf['smtp_from_name'] ?? 'Admin Sorte';
    $from_email = $conf['smtp_from_email'] ?? 'noreply@seusite.com';

    // Construct Link
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
    // Adjust to point to admin/reset-password.php
    $reset_link = str_replace('backend/api', 'admin', $base_url) . "/reset-password.php?token=" . $token;

    $subject = "Recuperacao de Acesso - Super Sorte";
    $message = "Olá, " . $user['username'] . ".\n\nVocê solicitou a redefinição de sua senha administrativa.\n\nClique no link abaixo para criar uma nova senha (link válido por 1 hora):\n\n" . $reset_link . "\n\nSe você não solicitou isso, ignore este email.";
    
    require_once '../libs/PHPMailer/PHPMailer.php';
    require_once '../libs/PHPMailer/SMTP.php';
    require_once '../libs/PHPMailer/Exception.php';

    $mail = new PHPMailer(true);
    $sent = false;

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
            $mail->addAddress($user['email'], $user['username']);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            if($mail->send()) {
                $sent = true;
            }
        } else {
            // Fallback to mail() if no SMTP is configured
            $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
            if (mail($user['email'], $subject, $message, $headers)) {
                $sent = true;
            }
        }
    } catch (Exception $e) {
        // Log error if needed, but let's fallback to simulation if local
    }

    if ($sent) {
        ob_clean();
        echo json_encode(['success' => true, 'message' => 'Um link de recuperação foi enviado para seu email.']);
    } else {
        ob_clean();
        $is_local = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
        if ($is_local) {
            echo json_encode(['success' => true, 'message' => 'Simulacao (Local): Link enviado! Verifique o log ou use este link: ' . $reset_link]);
        } else {
            echo json_encode(['error' => 'Erro ao enviar email. Verifique o SMTP no painel.']);
        }
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => 'Erro ao processar: ' . $e->getMessage()]);
}
?>
