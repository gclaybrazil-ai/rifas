<?php
require_once 'backend/config.php';

try {
    // 1. Alter 'usuarios' to include role and other useful fields if not exists
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS role ENUM('admin', 'criador') DEFAULT 'criador'");
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS whatsapp VARCHAR(20) DEFAULT NULL");
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS status ENUM('ativo', 'suspenso', 'pendente') DEFAULT 'ativo'");
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS created_at DATETIME DEFAULT CURRENT_TIMESTAMP");

    // 2. Alter 'rifas' to link to a creator
    // If not exists, add usuario_id. Admin-created rifas can have usuario_id = NULL or point to an admin ID.
    $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS usuario_id INT DEFAULT NULL");

    // 3. New Table: criador_config (for API keys per creator)
    $pdo->exec("CREATE TABLE IF NOT EXISTS criador_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        gateway ENUM('efi', 'mercado_pago') DEFAULT 'efi',
        client_id TEXT,
        client_secret TEXT,
        pix_key VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY(usuario_id)
    )");

    echo "Migração concluída com sucesso!";
} catch (Exception $e) {
    echo "Erro na migração: " . $e->getMessage();
}
