<?php
require_once 'backend/config.php';
$stmt = $pdo->query("DESCRIBE reservas");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
