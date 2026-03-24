<?php 
require 'backend/config.php';
$stmt = $pdo->query('SELECT * FROM saques WHERE id = 3');
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
