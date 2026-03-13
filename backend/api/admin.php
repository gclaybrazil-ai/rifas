<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    die(json_encode(['error' => 'Não autorizado']));
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'stats';

if($action === 'stats') {
    $stmt = $pdo->query("SELECT status, COUNT(*) as qtd FROM numeros GROUP BY status");
    $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $stmt = $pdo->query("SELECT SUM(valor_total) FROM reservas WHERE status = 'pago'");
    $faturamento = $stmt->fetchColumn() ?: 0;
    
    $stmt = $pdo->query("SELECT * FROM reservas ORDER BY data_reserva DESC LIMIT 50");
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'stats' => $stats,
        'faturamento' => $faturamento,
        'reservas' => $reservas
    ]);
} 
else if($action === 'mark_paid') {
    $id = intval($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE reservas SET status = 'pago' WHERE id = ?")->execute([$id]);
    $pdo->prepare("UPDATE numeros SET status = 'pago' WHERE reserva_id = ?")->execute([$id]);
    echo json_encode(['success' => true]);
}
else if($action === 'reset_rifa') {
    $pdo->exec("UPDATE numeros SET status = 'disponivel', reserva_id = NULL");
    $pdo->exec("TRUNCATE TABLE reservas");
    echo json_encode(['success' => true]);
}
else if($action === 'draw') {
    $stmt = $pdo->query("SELECT numero FROM numeros WHERE status = 'pago' ORDER BY RAND() LIMIT 1");
    $winner = $stmt->fetchColumn();
    if($winner) {
        $stmt2 = $pdo->prepare("SELECT r.nome, r.whatsapp FROM reservas r JOIN numeros n ON n.reserva_id = r.id WHERE n.numero = ? LIMIT 1");
        $stmt2->execute([$winner]);
        $user = $stmt2->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['winner' => $winner, 'user' => $user]);
    } else {
        echo json_encode(['error' => 'Nenhum número pago para sortear']);
    }
}
?>
