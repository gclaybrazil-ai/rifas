<?php 
require 'backend/config.php';
$s = $pdo->query('SELECT pix_id FROM saques WHERE id = 3')->fetchColumn();
echo $s;
?>
