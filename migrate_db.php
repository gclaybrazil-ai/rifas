<?php
require_once 'backend/config.php';
try {
    $pdo->exec("ALTER TABLE reservas ADD COLUMN valor_taxa DECIMAL(10,2) DEFAULT 0.00");
    echo "Coluna valor_taxa adicionada com sucesso!";
} catch (Exception $e) {
    echo "Erro ou coluna já existe: " . $e->getMessage();
}
?>
