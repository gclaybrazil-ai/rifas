<?php 
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require 'backend/config.php';
$client_id = $pdo->query("SELECT valor FROM configuracoes WHERE chave='efi_client_id'")->fetchColumn();
echo $client_id;
?>
