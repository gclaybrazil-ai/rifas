<?php
require_once 'backend/config.php';
$stmtR = $pdo->query("DESCRIBE reservas status");
$stmtN = $pdo->query("DESCRIBE numeros status");
echo "RESERVAS STATUS: " . print_r($stmtR->fetch(PDO::FETCH_ASSOC), true) . "\n";
echo "NUMEROS STATUS: " . print_r($stmtN->fetch(PDO::FETCH_ASSOC), true) . "\n";
