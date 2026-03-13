<?php
header('Content-Type: application/json');
require_once '../config.php';

// Limpar reservas expiradas automaticamente (mais de 5 minutos)
// Primeiro precisamos obter os IDs das reservas expiradas
$stmtExpiradas = $pdo->query("SELECT id FROM reservas WHERE status='pendente' AND data_reserva < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
$ids = $stmtExpiradas->fetchAll(PDO::FETCH_COLUMN);

if(count($ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    // Atualizar status da reserva
    $stmt1 = $pdo->prepare("UPDATE reservas SET status='expirado' WHERE id IN ($placeholders)");
    $stmt1->execute($ids);
    // Liberar números
    $stmt2 = $pdo->prepare("UPDATE numeros SET status='disponivel', reserva_id=NULL WHERE reserva_id IN ($placeholders)");
    $stmt2->execute($ids);
}

// Obter a rifa ativa (simplificando pegamos a primeira ID 1)
$rifa_id = 1;
$stmt = $pdo->prepare("SELECT id, nome, preco_numero, status, quantidade_numeros FROM rifas WHERE id = ?");
$stmt->execute([$rifa_id]);
$rifa = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$rifa) {
    die(json_encode(['error' => 'Rifa não encontrada']));
}

// Obter os números da rifa
$stmt = $pdo->prepare("SELECT numero, status, reserva_id FROM numeros WHERE rifa_id = ? ORDER BY numero ASC");
$stmt->execute([$rifa_id]);
$numeros = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'rifa' => $rifa,
    'numeros' => $numeros
]);
?>
