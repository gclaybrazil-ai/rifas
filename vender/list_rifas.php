<?php
require_once 'backend/config.php';
$r = $pdo->query('SELECT id, titulo, status FROM rifas')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($r, JSON_PRETTY_PRINT);
