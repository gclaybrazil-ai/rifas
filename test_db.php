<?php
require_once 'backend/config.php';
$stmt = $pdo->query("SELECT rifa_id, numero, status FROM numeros WHERE status = 'pago' LIMIT 30");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
