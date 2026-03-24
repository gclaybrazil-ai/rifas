<?php
require_once 'backend/config.php';
$stmt = $pdo->query("SELECT id, nome, saldo FROM afiliados WHERE id = 1");
$af = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($af, JSON_PRETTY_PRINT);
