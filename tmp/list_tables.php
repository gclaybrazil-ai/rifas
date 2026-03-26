<?php
require_once 'backend/config.php';
$stmt = $pdo->query('SHOW TABLES');
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
