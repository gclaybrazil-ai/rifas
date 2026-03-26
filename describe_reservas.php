<?php
require_once 'backend/config.php';
$stmt = $pdo->query("DESC reservas");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
