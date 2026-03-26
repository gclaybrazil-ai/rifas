<?php
require_once 'backend/config.php';
$stmt = $pdo->query("SELECT bonus_vendas, saldo FROM afiliados WHERE id = 1");
$af = $stmt->fetch(PDO::FETCH_ASSOC);
echo "BONUS ATUAL AF 1: " . $af['bonus_vendas'] . "\n";
echo "SALDO ATUAL AF 1: " . $af['saldo'] . "\n";
