<?php
require 'backend/config.php';
try {
    $res = $pdo->query('SELECT status, COUNT(*) FROM numeros GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
    print_r($res);
    
    $conf = $pdo->query('SELECT chave, valor FROM configuracoes')->fetchAll(PDO::FETCH_KEY_PAIR);
    print_r($conf);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
