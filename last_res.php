<?php
require_once 'backend/config.php';
$stmt = $pdo->query("SELECT id, status, afiliado_id, pix_txid FROM reservas ORDER BY id DESC LIMIT 5");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
