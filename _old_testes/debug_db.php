<?php
require_once 'backend/config.php';
$stmt = $pdo->query("SELECT id, status, afiliado_id, pix_txid, valor_total FROM reservas ORDER BY id DESC LIMIT 5");
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($reservas, JSON_PRETTY_PRINT);
