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
else if($action === 'create_rifa') {
    $nome = $_POST['nome'] ?? 'Rifa Nova';
    $preco = $_POST['preco'] ?? 10.00;

    $pdo->beginTransaction();
    try {
        // Fechar todas as anteriores para não bagunçar estatísticas do protótipo
        $pdo->exec("UPDATE rifas SET status = 'fechada'");

        $stmt = $pdo->prepare("INSERT INTO rifas (nome, preco_numero, status, quantidade_numeros) VALUES (?, ?, 'aberta', 100)");
        $stmt->execute([$nome, $preco]);
        $rifa_id = $pdo->lastInsertId();

        $insert_stmt = $pdo->prepare("INSERT INTO numeros (rifa_id, numero) VALUES (?, ?)");
        for($i = 0; $i < 100; $i++) {
            $num = str_pad($i, 2, '0', STR_PAD_LEFT);
            $insert_stmt->execute([$rifa_id, $num]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
    }
}
else if($action === 'get_rifas_list') {
    $stmt = $pdo->query("SELECT id, nome, preco_numero, status, quantidade_numeros FROM rifas ORDER BY id DESC");
    echo json_encode(['rifas' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}
else if($action === 'delete_rifa') {
    $id = intval($_POST['id'] ?? 0);
    $pdo->prepare("DELETE FROM reservas WHERE rifa_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM numeros WHERE rifa_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM rifas WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true]);
}
else if($action === 'set_rifa_status') {
    $id = intval($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'fechada';
    
    // Optional: Only 1 active
    if($status === 'aberta') {
        $pdo->exec("UPDATE rifas SET status = 'fechada'");
    }
    
    $pdo->prepare("UPDATE rifas SET status = ? WHERE id = ?")->execute([$status, $id]);
    echo json_encode(['success' => true]);
}
?>
