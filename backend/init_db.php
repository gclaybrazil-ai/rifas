<?php
// Script para inicializar o banco de dados
require_once 'config.php';

$sql = "
CREATE TABLE IF NOT EXISTS rifas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    preco_numero DECIMAL(10,2) NOT NULL,
    status ENUM('aberta', 'fechada') DEFAULT 'aberta',
    quantidade_numeros INT DEFAULT 100
);

CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rifa_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(20) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    data_reserva DATETIME NOT NULL,
    status ENUM('pendente', 'pago', 'expirado') DEFAULT 'pendente',
    pix_txid VARCHAR(255),
    pix_qrcode TEXT,
    pix_copiacola TEXT,
    valor_taxa DECIMAL(10,2) DEFAULT 0.00
);

CREATE TABLE IF NOT EXISTS numeros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rifa_id INT NOT NULL,
    numero VARCHAR(5) NOT NULL,
    status ENUM('disponivel', 'reservado', 'pago') DEFAULT 'disponivel',
    reserva_id INT DEFAULT NULL,
    UNIQUE KEY rifa_numero (rifa_id, numero)
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS publicacoes_ganhadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_ganhador VARCHAR(255) NOT NULL,
    numero_premiado VARCHAR(50) NOT NULL,
    premio_descricao TEXT NOT NULL,
    imagem_url VARCHAR(255) DEFAULT '',
    data_publicacao DATETIME DEFAULT CURRENT_TIMESTAMP
);
";

$pdo->exec($sql);

// Seed if empty
$stmt = $pdo->query("SELECT COUNT(*) FROM rifas");
if($stmt->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO rifas (nome, preco_numero) VALUES ('Sorteio Eletrônico $UPER$ORTE', 13.00)");
    $rifa_id = $pdo->lastInsertId();
    
    // Insert 00 to 99
    $insert_stmt = $pdo->prepare("INSERT INTO numeros (rifa_id, numero) VALUES (?, ?)");
    for($i = 0; $i < 100; $i++) {
        $num = str_pad($i, 2, '0', STR_PAD_LEFT);
        $insert_stmt->execute([$rifa_id, $num]);
    }

    // Default admin
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->exec("INSERT INTO usuarios (username, password) VALUES ('admin', '$hash')");
}

echo "Banco de dados inicializado com sucesso!";
?>
