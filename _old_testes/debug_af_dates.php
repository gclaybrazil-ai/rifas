<?php
require 'backend/config.php';
$res = $pdo->query('SELECT id, data_cadastro, data_ultimo_saque FROM afiliados WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
print_r($res);
