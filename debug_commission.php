<?php
require 'backend/config.php';
$stmt = $pdo->prepare("SELECT SUM(valor_total) as subtotal FROM reservas WHERE afiliado_id = 1 AND status = 'pago'");
$stmt->execute();
$subtotal = (float)$stmt->fetchColumn();

$stmtC = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'comissao_padrao'");
$pct = (float)($stmtC->fetchColumn() ?: 10);
$expectCom = round(($subtotal * $pct) / 100, 2);

$stmtAf = $pdo->prepare("SELECT saldo, total_ganho, vendas_pagas FROM afiliados WHERE id = 1");
$stmtAf->execute();
$af = $stmtAf->fetch(PDO::FETCH_ASSOC);

echo "Subtotal Sales for AF 1: R$ " . number_format($subtotal, 2) . "\n";
echo "Expected Commission ($pct%): R$ " . number_format($expectCom, 2) . "\n";
echo "Actual Af Table Saldo: R$ " . number_format($af['saldo'], 2) . "\n";
echo "Actual Af Table Vendas Pagas: " . $af['vendas_pagas'] . "\n";

$stmtAll = $pdo->query("SELECT SUM(valor_total) as total, COUNT(*) as qtd FROM reservas WHERE status = 'pago'");
$all = $stmtAll->fetch(PDO::FETCH_ASSOC);
echo "\nTOTAL SYSTEM SALES (ALL): R$ " . number_format($all['total'], 2) . " ($all[qtd] sales)\n";
