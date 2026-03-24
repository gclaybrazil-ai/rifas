<?php 
require 'backend/config.php';
$stmt = $pdo->query('SELECT nome, pix_key FROM afiliados WHERE id = 1');
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
