<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['rifa_id'], $data['nome'], $data['whatsapp'], $data['cotas']) || empty($data['cotas'])) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos.']);
    exit;
}

$rifa_id = (int)$data['rifa_id'];
$nome = trim($data['nome']);
$whatsapp = trim($data['whatsapp']);
$cotas = $data['cotas'];

try {
    $pdo->beginTransaction();

    // 1. Fetch Raffle Info (Check if active)
    $stmt = $pdo->prepare("SELECT valor_numero, status FROM rifas WHERE id = ?");
    $stmt->execute([$rifa_id]);
    $rifa = $stmt->fetch();

    if (!$rifa) {
        throw new Exception("Rifa não encontrada.");
    }

    if ($rifa['status'] == 'pendente_ativacao') {
        throw new Exception("Esta campanha ainda não está ativa para vendas.");
    }

    // 2. Check Availability
    $placeholders = implode(',', array_fill(0, count($cotas), '?'));
    $stmt = $pdo->prepare("SELECT numero FROM numeros WHERE rifa_id = ? AND numero IN ($placeholders)");
    $stmt->execute(array_merge([$rifa_id], $cotas));
    $ja_ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($ja_ocupados)) {
        throw new Exception("Os seguintes números acabaram de ser reservados: " . implode(', ', $ja_ocupados));
    }

    // 3. Create Reservation
    $valor_total = count($cotas) * $rifa['valor_numero'];
    $stmt = $pdo->prepare("INSERT INTO reservas (rifa_id, nome, whatsapp, valor_total) VALUES (?, ?, ?, ?)");
    $stmt->execute([$rifa_id, $nome, $whatsapp, $valor_total]);
    $reserva_id = $pdo->lastInsertId();

    // 4. Record Numbers
    $stmt = $pdo->prepare("INSERT INTO numeros (reserva_id, rifa_id, numero, status) VALUES (?, ?, ?, 'reservado')");
    foreach ($cotas as $num) {
        $stmt->execute([$reserva_id, $rifa_id, $num]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'reserva_id' => $reserva_id]);

} catch (Exception $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
