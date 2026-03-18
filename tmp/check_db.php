<?php
require_once 'c:/xampp/htdocs/clone130326/backend/config.php';
$stmt = $pdo->query("DESCRIBE rifas");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
