<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
date_default_timezone_set('America/Sao_Paulo');

// --- DATABASE CONFIG FOR SAAS ---
$db_host = 'localhost';

$is_local = (
    ($_SERVER['SERVER_ADDR'] ?? '') == '127.0.0.1' ||
    ($_SERVER['SERVER_ADDR'] ?? '') == '::1' ||
    ($_SERVER['HTTP_HOST'] ?? '') == 'localhost' ||
    php_sapi_name() == 'cli'
);

if ($is_local) {
    // Local (XAMPP)
    $db_name = 'u422005024_saas';
    $db_user = 'root';
    $db_pass = '';
} else {
    // Servidor (Produção)
    $db_name = 'u422005024_saas';
    $db_user = 'u422005024_saas';
    $db_pass = 'S3creta@';
}

try {
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    $pdo->exec("USE `$db_name`");
    $pdo->exec("SET time_zone = '-03:00'");

    // 1. Usuarios (Criadores e Admins do SaaS)
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        whatsapp VARCHAR(20) UNIQUE DEFAULT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'criador') DEFAULT 'criador',
        status ENUM('ativo', 'suspenso', 'pendente') DEFAULT 'ativo',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Rifas (Tenancy)
    $pdo->exec("CREATE TABLE IF NOT EXISTS rifas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        subtitulo TEXT,
        valor_numero DECIMAL(10,2) NOT NULL,
        status ENUM('ativa', 'ativo', 'pausado', 'finalizado', 'pendente_ativacao') DEFAULT 'pendente_ativacao',
        imagem_url TEXT,
        total_numeros INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Migração de coluna status (de ENUM para VARCHAR para evitar erros de valor)
    // Migração de coluna status (Restaurar ENUM com todos os valores para facilitar edição manual)
    try { $pdo->exec("ALTER TABLE rifas MODIFY COLUMN status ENUM('ativa', 'ativo', 'pausado', 'finalizado', 'pendente_ativacao') DEFAULT 'pendente_ativacao'"); } catch(Exception $e) {}

    // 3. Reservas (Vendas no SaaS)
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rifa_id INT NOT NULL,
        nome VARCHAR(255) NOT NULL,
        whatsapp VARCHAR(20) NOT NULL,
        valor_total DECIMAL(10,2) NOT NULL,
        status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente',
        pix_id VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Numeros Vendidos
    $pdo->exec("CREATE TABLE IF NOT EXISTS numeros (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reserva_id INT NOT NULL,
        rifa_id INT NOT NULL,
        numero INT NOT NULL,
        status ENUM('reservado', 'pago') DEFAULT 'reservado'
    )");

    // 5. Configurações por Criador (Gateways)
    $pdo->exec("CREATE TABLE IF NOT EXISTS criador_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT UNIQUE NOT NULL,
        gateway ENUM('mercado_pago', 'efi', 'pix_manual') DEFAULT 'pix_manual',
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Migração de colunas para criador_config (caso a tabela já exista)
    $cols = [
        'chave_pix' => 'VARCHAR(255) DEFAULT NULL',
        'mp_access_token' => 'TEXT DEFAULT NULL',
        'mp_public_key' => 'TEXT DEFAULT NULL',
        'efi_client_id' => 'TEXT DEFAULT NULL',
        'efi_client_secret' => 'TEXT DEFAULT NULL',
        'habilitar_cartao' => 'INT DEFAULT 0',
        'repassar_taxas' => 'INT DEFAULT 0'
    ];
    foreach($cols as $col => $type) {
        $check = $pdo->query("SHOW COLUMNS FROM criador_config LIKE '$col'")->fetch();
        if(!$check) {
            $pdo->exec("ALTER TABLE criador_config ADD COLUMN $col $type");
        }
    }

    // 6. Configurações Globais (Para o Dono do SaaS receber ativações)
    $pdo->exec("CREATE TABLE IF NOT EXISTS global_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chave_pix VARCHAR(255) DEFAULT NULL,
        mp_access_token TEXT DEFAULT NULL,
        mp_public_key TEXT DEFAULT NULL,
        whatsapp_suporte VARCHAR(20) DEFAULT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Migração de colunas para global_config
    $gcols = ['mp_public_key' => 'TEXT DEFAULT NULL'];
    foreach($gcols as $col => $type) {
        $check = $pdo->query("SHOW COLUMNS FROM global_config LIKE '$col'")->fetch();
        if(!$check) { $pdo->exec("ALTER TABLE global_config ADD COLUMN $col $type"); }
    }

    // 7. Títulos Premiados (Instant Wins)
    $pdo->exec("CREATE TABLE IF NOT EXISTS titulos_premiados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rifa_id INT NOT NULL,
        numero INT NOT NULL,
        descricao VARCHAR(255) NOT NULL,
        ganhador_nome VARCHAR(255) DEFAULT NULL,
        status ENUM('disponivel', 'ganho') DEFAULT 'disponivel',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 8. Caixa Premiada (Metas de Compra)
    $pdo->exec("CREATE TABLE IF NOT EXISTS caixas_premiadas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rifa_id INT NOT NULL,
        qtd_minima INT NOT NULL,
        premio_descricao VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

} catch (PDOException $e) {
    die("Erro de conexão (SaaS): " . $e->getMessage());
}

// Global Helper (Logging for SaaS)
function registrarLogSaaS($categoria, $acao, $user_id = null)
{
    global $pdo;
    // Log simpler logic for SaaS maintenance
}
