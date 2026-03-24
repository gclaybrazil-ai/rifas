<?php
chdir('backend/api');
require_once '../../backend/config.php';
$_GET['id'] = 0;
ob_start();
include 'get_rifa.php';
$output = ob_get_clean();
echo $output;
?>
