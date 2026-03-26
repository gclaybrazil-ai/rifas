<?php
require_once 'config.php';
$stmt = $pdo->prepare("INSERT INTO global_config (chave_pix, whatsapp_suporte) VALUES (?, ?)");
$stmt->execute(['test-key-123', '5511999999999']);
echo "Inserido com sucesso!";
