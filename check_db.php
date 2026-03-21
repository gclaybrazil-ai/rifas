<?php
require_once 'backend/config.php';
$stmt = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave LIKE 'evolution%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows);
