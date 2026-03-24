<?php
require 'backend/config.php';
$res = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('minimo_saque', 'ciclo_pagamento_dias')")->fetchAll(PDO::FETCH_KEY_PAIR);
print_r($res);
