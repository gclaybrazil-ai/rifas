<?php 
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require 'backend/config.php';
$stmt = $pdo->query('SELECT * FROM site_logs ORDER BY id DESC LIMIT 5');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
