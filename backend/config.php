<?php
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/SMTP.php';
require_once __DIR__ . '/libs/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$db_host = 'localhost';

$is_local = (
    ($_SERVER['SERVER_ADDR'] ?? '') == '127.0.0.1' ||
    ($_SERVER['SERVER_ADDR'] ?? '') == '::1' ||
    ($_SERVER['HTTP_HOST'] ?? '') == 'localhost' ||
    php_sapi_name() == 'cli'
);

if ($is_local) {
    // Local (XAMPP)
    $db_name = 'u422005024_riffas';
    $db_user = 'root';
    $db_pass = '';
} else {
    // Servidor (Produção)
    $db_name = 'u422005024_riffas';
    $db_user = 'u422005024_riffas';
    $db_pass = 'S3creta@';
}

try {
    // Connect to mysql server first to create db if needed
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    $pdo->exec("USE `$db_name`");

    // Sincronizar Horário do MySQL com o do PHP
    $pdo->exec("SET time_zone = '-03:00'");

    // Tabelas do Sistema de Afiliados
    $pdo->exec("CREATE TABLE IF NOT EXISTS afiliados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        whatsapp VARCHAR(20) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        pix_key VARCHAR(255) NOT NULL,
        saldo DECIMAL(10,2) DEFAULT 0.00,
        total_ganho DECIMAL(10,2) DEFAULT 0.00,
        vendas_pagas INT DEFAULT 0,
        data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Garantir colunas novas se a tabela já existia
    $checkAfEmail = $pdo->query("SHOW COLUMNS FROM afiliados LIKE 'email'");
    if (!$checkAfEmail->fetch()) {
        $pdo->exec("ALTER TABLE afiliados ADD COLUMN email VARCHAR(255) NOT NULL UNIQUE AFTER whatsapp");
    }
    $checkAfSenha = $pdo->query("SHOW COLUMNS FROM afiliados LIKE 'senha'");
    if (!$checkAfSenha->fetch()) {
        $pdo->exec("ALTER TABLE afiliados ADD COLUMN senha VARCHAR(255) NOT NULL AFTER email");
    }
    $checkAfDataSaque = $pdo->query("SHOW COLUMNS FROM afiliados LIKE 'data_ultimo_saque'");
    if (!$checkAfDataSaque->fetch()) {
        $pdo->exec("ALTER TABLE afiliados ADD COLUMN data_ultimo_saque DATETIME DEFAULT CURRENT_TIMESTAMP AFTER data_cadastro");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS afiliado_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        afiliado_id INT NOT NULL,
        token VARCHAR(100) NOT NULL,
        tipo ENUM('reset_senha', 'update_pix', 'update_email') NOT NULL,
        novo_valor TEXT, -- Para guardar o novo email ou pix temporariamente
        data_expiracao DATETIME NOT NULL,
        usado TINYINT(1) DEFAULT 0
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS saques (
        id INT AUTO_INCREMENT PRIMARY KEY,
        afiliado_id INT NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        chave_pix VARCHAR(255) NOT NULL,
        status ENUM('pendente', 'pago', 'erro') DEFAULT 'pendente',
        pix_id VARCHAR(255),
        data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Adicionar afiliado_id na tabela reservas se não existir
    $checkReserva = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'afiliado_id'");
    if (!$checkReserva->fetch()) {
        $pdo->exec("ALTER TABLE reservas ADD COLUMN afiliado_id INT DEFAULT NULL");
    }

    // Log System Tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL, -- ID do afiliado
        admin_id INT NULL, -- ID do admin (tabela usuarios)
        ip VARCHAR(45) NOT NULL,
        categoria ENUM('acesso_site', 'acao_admin', 'acao_afiliado') NOT NULL,
        acao VARCHAR(255) NOT NULL,
        pagina VARCHAR(255),
        pais VARCHAR(100),
        cidade VARCHAR(100),
        data_hora DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS login_autorizacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_type ENUM('admin', 'afiliado') NOT NULL,
        user_id INT NOT NULL,
        ip VARCHAR(45) NOT NULL,
        location_slug VARCHAR(255), -- cidade-pais
        token VARCHAR(100) UNIQUE,
        autorizado TINYINT(1) DEFAULT 0,
        data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_autenticacao DATETIME NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS online_tracking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sessao_id VARCHAR(100) UNIQUE,
        ip VARCHAR(45),
        user_id INT NULL,
        user_type ENUM('visitante', 'afiliado', 'admin') DEFAULT 'visitante',
        ultima_pagina VARCHAR(255),
        ultima_atividade DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Adicionar admin_email padrão
    $defaults = [
        'minimo_saque' => '20.00',
        'comissao_padrao' => '10.00', // 10%
        'afiliados_ativo' => '1',
        'ciclo_pagamento_dias' => '15',
        'admin_email' => 'admin@seusite.com'
    ];
    foreach ($defaults as $chave => $valor) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO configuracoes (chave, valor) VALUES (?, ?)");
        $stmt->execute([$chave, $valor]);
    }

} catch (PDOException $e) {
    die(json_encode(['error' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]));
}

// Global Help Functions for Logs & Security
function sendMailer($to_email, $to_name, $subject, $message) {
    global $pdo;
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
    } catch (Exception $e) { return false; }
}

function getGeoInfo($ip) {
    if (isset($_SESSION['geo_cache'][$ip])) return $_SESSION['geo_cache'][$ip];
    $info = ['pais' => 'Local/Desconhecido', 'cidade' => 'Local/Desconhecido', 'slug' => 'local'];
    if ($ip == '127.0.0.1' || $ip == '::1') return $info;
    try {
        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        $res = @file_get_contents("http://ip-api.com/json/$ip?fields=status,country,city", false, $ctx);
        if ($res) {
            $data = json_decode($res, true);
            if ($data['status'] == 'success') {
                $info = ['pais' => $data['country'] ?? 'Desconhecido', 'cidade' => $data['city'] ?? 'Desconhecido', 'slug' => strtolower(($data['city']??'city').'-'.($data['country']??'country'))];
                $_SESSION['geo_cache'][$ip] = $info;
            }
        }
    } catch (Exception $e) {}
    return $info;
}

function registrarLog($categoria, $acao, $user_id = null, $admin_id = null) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'];
    $geo = getGeoInfo($ip);
    $pagina = $_SERVER['REQUEST_URI'] ?? '/';
    $stmt = $pdo->prepare("INSERT INTO site_logs (user_id, admin_id, ip, categoria, acao, pagina, pais, cidade) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $admin_id, $ip, $categoria, $acao, $pagina, $geo['pais'], $geo['cidade']]);
}

function checkLocationChallenge($user_type, $user_id, $email, $nome) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'];
    $geo = getGeoInfo($ip);
    
    // Check if this location (or IP if same city) is already authorized for this user
    $stmt = $pdo->prepare("SELECT id FROM login_autorizacoes WHERE user_type = ? AND user_id = ? AND (ip = ? OR location_slug = ?) AND autorizado = 1");
    $stmt->execute([$user_type, $user_id, $ip, $geo['slug']]);
    if ($stmt->fetch()) return ['success' => true];

    // Challenge Required
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("INSERT INTO login_autorizacoes (user_type, user_id, ip, location_slug, token) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_type, $user_id, $ip, $geo['slug'], $token]);

    $baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . str_replace('backend/config.php', '', $_SERVER['PHP_SELF']);
    $link = rtrim($baseUrl, '/') . "/backend/confirmar_acesso.php?token=" . $token;

    $subject = "🚨 Acesso Suspeito Detectado - " . ($user_type === 'admin' ? 'Painel Admin' : 'Painel Afiliado');
    $msg = "Olá {$nome},\n\nDetectamos uma tentativa de login em sua conta a partir de um novo local:\n\n🌍 Local: {$geo['cidade']}, {$geo['pais']}\n🖥 IP: {$ip}\n\nSe foi você, autorize este acesso clicando no link abaixo:\n\n{$link}\n\nSe não foi você, ignore este email e considere alterar sua senha.";
    
    sendMailer($email, $nome, $subject, $msg);
    registrarLog($user_type === 'admin' ? 'acao_admin' : 'acao_afiliado', "Tentativa de login de novo local ({$geo['cidade']}) bloqueada", $user_type === 'afiliado' ? $user_id : null, $user_type === 'admin' ? $user_id : null);
    
    return ['challenge' => true];
}

// Track online activity
if (session_status() === PHP_SESSION_NONE) session_start();
$sessao_id = session_id();
if (!empty($sessao_id)) {
    $ip_track = $_SERVER['REMOTE_ADDR'];
    $pagina_track = $_SERVER['REQUEST_URI'] ?? '/';
    $user_id_track = $_SESSION['afiliado_id'] ?? null;
    $user_type_track = isset($_SESSION['admin_logged']) ? 'admin' : ($user_id_track ? 'afiliado' : 'visitante');

    $stmtTrack = $pdo->prepare("INSERT INTO online_tracking (sessao_id, ip, user_id, user_type, ultima_pagina) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE ip = ?, user_id = ?, user_type = ?, ultima_pagina = ?, ultima_atividade = CURRENT_TIMESTAMP");
    $stmtTrack->execute([$sessao_id, $ip_track, $user_id_track, $user_type_track, $pagina_track, $ip_track, $user_id_track, $user_type_track, $pagina_track]);
    
    // Auto-log page access if not an API call
    if (strpos($pagina_track, '/api/') === false && strpos($pagina_track, '.php') !== false) {
        registrarLog('acesso_site', "Acessou página: $pagina_track", $user_id_track);
    }
}
