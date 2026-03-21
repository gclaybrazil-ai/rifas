<?php
require 'backend/config.php';
$statsStartDate = '2026-03-21'; // Exemplo

try {
    $whereStats = " WHERE data_reserva >= '$statsStartDate 00:00:00' ";
    $sql = "SELECT n.status, COUNT(*) as qtd FROM numeros n " . 
                             (!empty($statsStartDate) ? " JOIN reservas r ON n.reserva_id = r.id $whereStats " : "") . 
                             " GROUP BY n.status";
    echo "SQL: $sql\n";
    $stmtStats = $pdo->query($sql);
    $stats = $stmtStats->fetchAll(PDO::FETCH_KEY_PAIR);
    print_r($stats);
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage();
}
?>
