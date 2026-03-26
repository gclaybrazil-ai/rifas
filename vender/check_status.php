<?php
require_once 'backend/config.php';
$id = 7;
$stmt = $pdo->prepare("SELECT status FROM rifas WHERE id = ?");
$stmt->execute([$id]);
$status = $stmt->fetchColumn();
echo "STATUS_ID_7: " . ($status ?: "NÃO ENCONTRADA");
