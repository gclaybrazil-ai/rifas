<?php 
require 'backend/config.php';
$pdo->exec("UPDATE saques SET status='pendente', pix_id=NULL WHERE id IN (1, 3)");
echo 'RESET OK';
?>
