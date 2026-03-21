<?php
require_once 'c:/xampp/htdocs/clone130326/backend/config.php';
try {
    // Ensure gemini_api_key exists in configuracoes table
    $stmt = $pdo->prepare("INSERT IGNORE INTO configuracoes (chave, valor) VALUES (:ch, :val)");
    $stmt->execute(['ch' => 'gemini_api_key', 'val' => '']);
    echo "Sucesso: O campo gemini_api_key foi criado no banco de dados.";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
