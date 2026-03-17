<?php
date_default_timezone_set('America/Sao_Paulo');
$db_host = 'localhost';

if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1' || $_SERVER['HTTP_HOST'] == 'localhost') {
    // Local (XAMPP)
    $db_name = 'top_sorte';
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

    // Configurações Iniciais
    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes (chave VARCHAR(50) PRIMARY KEY, valor TEXT)");
    
    // Inserir padrões se não existirem
    $defaults = [
        'minimo_saque' => '20.00',
        'comissao_padrao' => '10.00', // 10%
        'afiliados_ativo' => '1'
    ];
    foreach($defaults as $chave => $valor) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO configuracoes (chave, valor) VALUES (?, ?)");
        $stmt->execute([$chave, $valor]);
    }

} catch(PDOException $e) {
    die(json_encode(['error' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]));
}
