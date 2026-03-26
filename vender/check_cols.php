<?php
require_once 'backend/config.php';
$cols = $pdo->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_ASSOC);
echo "USUARIOS COLS: " . json_encode($cols, JSON_PRETTY_PRINT) . "\n\n";
$rifa_cols = $pdo->query('SHOW COLUMNS FROM rifas')->fetchAll(PDO::FETCH_ASSOC);
echo "RIFAS COLS: " . json_encode($rifa_cols, JSON_PRETTY_PRINT) . "\n\n";
