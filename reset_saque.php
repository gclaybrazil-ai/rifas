<?php 
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require 'backend/config.php';
$pdo->exec("UPDATE saques SET status='pendente', pix_id=NULL WHERE id=1");
echo 'OK';
?>
