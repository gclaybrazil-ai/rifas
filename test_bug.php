<?php
require 'backend/config.php';

// Prepare a test order with NO affiliate
$stmt = $pdo->prepare("INSERT INTO reservas (rifa_id, nome, whatsapp, valor_total, data_reserva, status, afiliado_id) VALUES (1, 'Test No Af', '551100000000', 100.00, NOW(), 'pendente', NULL)");
$stmt->execute();
$reserva_id = $pdo->lastInsertId();

// Get balance BEFORE
$stmtAf = $pdo->prepare("SELECT saldo, total_ganho, vendas_pagas FROM afiliados WHERE id = 1");
$stmtAf->execute();
$before = $stmtAf->fetch(PDO::FETCH_ASSOC);

// Simulate confirmation (like admin.php)
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("SELECT id, status, afiliado_id, valor_total, rifa_id FROM reservas WHERE id = ?");
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reserva && $reserva['status'] === 'pendente') {
        $pdo->prepare("UPDATE reservas SET status = 'pago' WHERE id = ?")->execute([$reserva_id]);
        
        if (!empty($reserva['afiliado_id'])) {
            $afId = intval($reserva['afiliado_id']);
            $valorTotal = (float)$reserva['valor_total'];
            $comissionPct = 10.00;
            $comissao = round(($valorTotal * $comissionPct) / 100, 2);
            $pdo->prepare("UPDATE afiliados SET saldo = saldo + ?, total_ganho = total_ganho + ?, vendas_pagas = vendas_pagas + 1 WHERE id = ?")
                ->execute([$comissao, $comissao, $afId]);
        }
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}

// Get balance AFTER
$stmtAf->execute();
$after = $stmtAf->fetch(PDO::FETCH_ASSOC);

echo "Reservation ID: $reserva_id (No Affiliate)\n";
echo "BEFORE -> Saldo: {$before['saldo']} | Ganho: {$before['total_ganho']} | Vendas: {$before['vendas_pagas']}\n";
echo "AFTER  -> Saldo: {$after['saldo']}  | Ganho: {$after['total_ganho']}  | Vendas: {$after['vendas_pagas']}\n";

if ($before['saldo'] != $after['saldo']) {
    echo "BUG DETECTED! Saldo changed for NULL affiliate order.\n";
} else {
    echo "Correct! No change for NULL affiliate order.\n";
}
