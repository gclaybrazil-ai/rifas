<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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

    $pdo->exec("CREATE TABLE IF NOT EXISTS afiliado_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        afiliado_id INT NOT NULL,
        token VARCHAR(100) NOT NULL,
        tipo ENUM('reset_senha', 'update_pix', 'update_email') NOT NULL,
        novo_valor TEXT, 
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

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        admin_id INT NULL,
        ip VARCHAR(45) NOT NULL,
        categoria ENUM('acesso_site', 'acao_admin', 'acao_afiliado') NOT NULL,
        acao VARCHAR(255) NOT NULL,
        pagina VARCHAR(255),
        pais VARCHAR(100),
        cidade VARCHAR(100),
        latitude VARCHAR(50),
        longitude VARCHAR(50),
        data_hora DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS login_autorizacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_type ENUM('admin', 'afiliado') NOT NULL,
        user_id INT NOT NULL,
        ip VARCHAR(45) NOT NULL,
        location_slug VARCHAR(255),
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
 
    $pdo->exec("CREATE TABLE IF NOT EXISTS banidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) UNIQUE NOT NULL,
        motivo VARCHAR(255),
        admin_id INT,
        data_bloqueio DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
 
    // IP Blocking Security Check (Now using established $pdo and after table creation)
    $client_ip = $_SERVER['REMOTE_ADDR'];
    $stmtBanned = $pdo->prepare("SELECT id FROM banidos WHERE ip = ?");
    $stmtBanned->execute([$client_ip]);
    if ($stmtBanned->fetch()) {
        http_response_code(403);
        die("<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1 style='color:#e74c3c;'>ACESSO BLOQUEADO 🚫</h1><p>Seu endereço IP ($client_ip) foi bloqueado por motivos de segurança.</p><p>Se acredita que isso é um erro, entre em contato com o suporte.</p></div>");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS assistant_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pergunta VARCHAR(255) NOT NULL,
        resposta TEXT NOT NULL,
        icone VARCHAR(50) DEFAULT '✨'
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes (
        chave VARCHAR(100) PRIMARY KEY,
        valor TEXT
    )");

    // Adicionar colunas faltantes de uma vez
    $cols = [
        'afiliados' => ['email', 'senha', 'data_ultimo_saque'],
        'rifas' => ['imagem_url', 'premio1', 'premio2', 'premio3', 'premio4', 'premio5', 'sorteio_por'],
        'usuarios' => ['email'],
        'reservas' => ['afiliado_id'],
        'site_logs' => ['latitude', 'longitude', 'dispositivo']
    ];
    
    foreach($cols as $table => $tableCols) {
        foreach($tableCols as $col) {
            try {
                $check = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
                if (!$check->fetch()) {
                    if($table == 'usuarios' && $col == 'email') $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` VARCHAR(255) DEFAULT 'admin@seusite.com'");
                    else if($table == 'afiliados' && ($col == 'email' || $col == 'senha')) $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` VARCHAR(255) NOT NULL");
                    else if($table == 'afiliados' && $col == 'data_ultimo_saque') $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` DATETIME DEFAULT CURRENT_TIMESTAMP");
                    else if($table == 'rifas' && $col == 'sorteio_por') $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` VARCHAR(50) DEFAULT 'Loteria Federal'");
                    else if($table == 'reservas' && $col == 'afiliado_id') $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` INT DEFAULT NULL");
                    else $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` VARCHAR(255) DEFAULT ''");
                }
            } catch(Exception $e) {}
        }
    }

    // Site Defaults
    $defaults = [
        'minimo_saque' => '20.00',
        'comissao_padrao' => '10.00', 
        'afiliados_ativo' => '1',
        'ciclo_pagamento_dias' => '15',
        'admin_email' => 'admin@seusite.com',
        'assistant_enabled' => '1',
        'assistant_name' => 'Assistente Top Sorte',
        'assistant_attendant' => 'David',
        'assistant_whatsapp' => '5511999999999'
    ];
    foreach ($defaults as $chave => $valor) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO configuracoes (chave, valor) VALUES (?, ?)");
        $stmt->execute([$chave, $valor]);
    }

    $countMsg = $pdo->query("SELECT COUNT(*) FROM assistant_messages")->fetchColumn();
    if($countMsg == 0) {
        $msgDefaults = [
            ['💰 Qual o valor?', 'Cada número custa apenas **valor fixo**! 💰<br><br>Quanto mais números, mais chances de ganhar! 🍀', '💰'],
            ['🎯 Como funciona?', 'É super simples! 😊<br><br>1️⃣ Escolha os números<br>2️⃣ Pague via PIX<br>3️⃣ Seus números são confirmados na hora! 🎯', '🎯'],
            ['💳 Como pagar?', 'O pagamento é via **PIX**! ⚡<br><br>Após escolher seus números, você verá os dados do PIX e a confirmação é automática! 🎉', '💳'],
            ['💬 Falar com atendente', 'Claro! Nosso atendente está à disposição! 😊<br><br>Clique no botão abaixo para falar com ele:', '💬']
        ];
        foreach($msgDefaults as $md) {
            $pdo->prepare("INSERT INTO assistant_messages (pergunta, resposta, icone) VALUES (?, ?, ?)")->execute($md);
        }
    }

} catch (PDOException $e) {
    die(json_encode(['error' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]));
}

// Global Maintenance Mode Check
try {
    $stmtM = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
    $stmtM->execute(['modo_manutencao']);
    $is_maintenance = ($stmtM->fetchColumn() === '1');

    if ($is_maintenance) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $allowed_paths = [
            '/admin/',
            'manutencao.php',
            'pix_webhook.php',
            'registrar_webhook.php',
            'backend/api/admin.php',
            'backend/api/login.php',
            'backend/api/logout.php'
        ];

        $is_allowed = false;
        foreach ($allowed_paths as $path) {
            if (strpos($script, $path) !== false) {
                $is_allowed = true;
                break;
            }
        }

        if (!$is_allowed) {
            // Se for uma chamada de API (dentro da pasta api/), retorna JSON
            if (strpos($script, '/api/') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['maintenance' => true, 'success' => false, 'message' => 'Site em manutenção']);
                exit;
            }
            
            // Caso contrário, redireciona para a página de manutenção
            // Garante que o redirecionamento funcione tanto na raiz quanto em subpastas
            header("Location: manutencao.php");
            exit;
        }
    }
} catch (Exception $e) {}

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
            $mail->isHTML(true); // Set email format to HTML
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
                $info = [
                    'pais' => $data['country'] ?? 'Desconhecido', 
                    'cidade' => $data['city'] ?? 'Desconhecido', 
                    'lat' => $data['lat'] ?? null,
                    'lon' => $data['lon'] ?? null,
                    'slug' => strtolower(($data['city']??'city').'-'.($data['country']??'country'))
                ];
                $_SESSION['geo_cache'][$ip] = $info;
            }
        }
    } catch (Exception $e) {}
    return $info;
}

function getAddressFromCoords($lat, $lng) {
    if (empty($lat) || empty($lng)) return "Localização exata não disponível";
    try {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => ['User-Agent: PHP-Riffas-App/1.0']
            ]
        ];
        $ctx = stream_context_create($opts);
        $res = @file_get_contents("https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng&zoom=18&addressdetails=1", false, $ctx);
        if ($res) {
            $data = json_decode($res, true);
            if (isset($data['address'])) {
                $a = $data['address'];
                $road = $a['road'] ?? '';
                $number = $a['house_number'] ?? '';
                $suburb = $a['suburb'] ?? $a['neighbourhood'] ?? '';
                $city = $a['city'] ?? $a['town'] ?? $a['village'] ?? '';
                $state = $a['state'] ?? '';
                $postcode = $a['postcode'] ?? '';

                $formatted = "";
                if ($road) $formatted .= $road;
                if ($number) $formatted .= ", " . $number;
                if ($suburb) $formatted .= " - " . $suburb;
                if ($city) $formatted .= ", " . $city;
                if ($state) $formatted .= " - " . $state;
                if ($postcode) $formatted .= ", " . $postcode;

                return $formatted ?: ($data['display_name'] ?? "Endereço não formatado");
            }
        }
    } catch (Exception $e) {}
    return "Localização exata: $lat, $lng";
}

function registrarLog($categoria, $acao, $user_id = null, $admin_id = null, $lat = null, $lng = null) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'];
    $geo = getGeoInfo($ip);
    $pagina = $_SERVER['REQUEST_URI'] ?? '/';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Simple Device Detect
    $disp = 'Desktop';
    if (preg_match('/(android|iphone|ipad|mobile)/i', $ua)) {
        if (preg_match('/android/i', $ua)) $disp = 'Android';
        else if (preg_match('/iphone|ipad/i', $ua)) $disp = 'iOS';
        else $disp = 'Mobile';
    } else if (preg_match('/(macintosh|mac os x)/i', $ua)) $disp = 'Mac';
    else if (preg_match('/windows/i', $ua)) $disp = 'Windows';
 
    $stmt = $pdo->prepare("INSERT INTO site_logs (user_id, admin_id, ip, categoria, acao, pagina, pais, cidade, latitude, longitude, dispositivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $admin_id, $ip, $categoria, $acao, $pagina, $geo['pais'], $geo['cidade'], $lat ?? ($geo['lat'] ?? null), $lng ?? ($geo['lon'] ?? null), $disp]);
}

function checkLocationChallenge($user_type, $user_id, $email, $nome, $lat = null, $lng = null) {
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

    // Better way to find Site Root
    $dir = str_replace('\\', '/', __DIR__);
    $root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $webPath = str_replace($root, '', $dir); // e.g. /rifas/backend
    $sitePath = rtrim(str_replace('/backend', '', $webPath), '/'); 
    
    $baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $sitePath;
    $link = $baseUrl . "/backend/confirmar_acesso.php?token=" . $token;

    // Get Detailed Address if Coordinates are available
    $address = getAddressFromCoords($lat, $lng);
    $mapsLink = (!empty($lat) && !empty($lng)) ? "https://www.google.com/maps/search/?api=1&query=$lat,$lng" : "";

    $subject = "🚨 Acesso Suspeito Detectado - " . ($user_type === 'admin' ? 'Painel Admin' : 'Painel Afiliado');
    
    $msg = "Olá <strong>{$nome}</strong>,<br><br>";
    $msg .= "Detectamos uma tentativa de login em sua conta a partir de um novo local:<br><br>";
    $msg .= "🌍 <strong>Endereço:</strong> {$address}<br>";
    $msg .= "🖥 <strong>IP:</strong> {$ip}<br>";
    if ($mapsLink) {
        $msg .= "📍 <strong>Ver no Mapa:</strong> <a href='{$mapsLink}'>Clique aqui para abrir o Google Maps</a><br>";
    }
    $msg .= "<br>Se foi você, autorize este acesso clicando no botão abaixo:<br><br>";
    $msg .= "<a href='{$link}' style='display:inline-block; padding:12px 24px; background:#2c3e50; color:white; text-decoration:none; border-radius:10px; font-weight:bold;'>AUTORIZAR ACESSO AGORA</a><br><br>";
    $msg .= "Se não foi você, ignore este email e considere alterar sua senha para sua segurança.";
    
    sendMailer($email, $nome, $subject, $msg);
    registrarLog($user_type === 'admin' ? 'acao_admin' : 'acao_afiliado', "Tentativa de login bloqueada. Local: $address", $user_type === 'afiliado' ? $user_id : null, $user_type === 'admin' ? $user_id : null, $lat, $lng);
    
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

function validatePasswordComplexity($password) {
    global $pdo;
    $stmt = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'password_complexity'");
    $complexity = $stmt->fetchColumn() ?: '1';

    if (strlen($password) < 8) {
        return "A senha deve ter pelo menos 8 caracteres.";
    }

    if ($complexity == '1') {
        // Alphanumeric min 8 (at least one letter and one number)
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return "A senha deve conter letras e números (mínimo 8 caracteres).";
        }
    } else if ($complexity == '2') {
        // Alphanumeric with special characters, uppercase, lowercase, and digit (min 8)
        if (!preg_match('/[A-Z]/', $password) || 
            !preg_match('/[a-z]/', $password) || 
            !preg_match('/[0-9]/', $password) || 
            !preg_match('/[^A-Za-z0-9]/', $password)) {
            return "A senha deve conter letras maiúsculas, minúsculas, números e caracteres especiais (@, #, $, etc) e no mínimo 8 caracteres.";
        }
    }

    return true; // Valid
}
