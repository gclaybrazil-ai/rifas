<?php
require_once 'backend/config.php';
$_GET['id'] = 0; // Test default rifa
ob_start();
include 'backend/api/get_rifa.php';
$output = ob_get_clean();
echo $output;
?>
