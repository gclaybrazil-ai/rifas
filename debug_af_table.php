<?php
require 'backend/config.php';
$res = $pdo->query('SELECT id, nome, saldo, total_ganho, vendas_pagas FROM afiliados')->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
