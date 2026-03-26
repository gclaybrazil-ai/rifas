<?php
require_once 'backend/config.php';
try {
    $pdo->exec("ALTER TABLE reservas ADD COLUMN is_bonus TINYINT(1) DEFAULT 0 AFTER afiliado_id");
    echo "Coluna is_bonus adicionada com sucesso!\n";
} catch(Exception $e) {
    echo "Erro (Provavelmente já existe): " . $e->getMessage() . "\n";
}
