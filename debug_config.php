<?php
require 'backend/config.php';
$stmt = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave = 'comissao_padrao'");
$res = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($res);
