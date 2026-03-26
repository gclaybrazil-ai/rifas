<?php
require_once 'backend/api/admin.php'; // already has session check but I need config
require_once 'backend/config.php';
echo "--- RESERVAS ---\n";
$stmt = $pdo->query("DESCRIBE reservas status");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
echo "\n--- NUMEROS ---\n";
$stmt = $pdo->query("DESCRIBE numeros status");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
