<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM global_config");
$conf = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($conf, JSON_PRETTY_PRINT);
