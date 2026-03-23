<?php 
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require 'backend/config.php';
$stmt = $pdo->query('SELECT * FROM saques WHERE id = 1');
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
