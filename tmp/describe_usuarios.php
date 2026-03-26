<?php
require_once 'backend/config.php';
$stmt = $pdo->query('DESCRIBE usuarios');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
