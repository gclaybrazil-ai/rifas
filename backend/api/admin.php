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
else if($action === 'draw_multiple') {
    $rifa_id = intval($_POST['rifa_id'] ?? 0);
    $qtd = intval($_POST['qtd'] ?? 1);
    if($qtd < 1) $qtd = 1;
    if($qtd > 5) $qtd = 5;

    $stmtCheck = $pdo->prepare("SELECT quantidade_numeros, (SELECT COUNT(*) FROM numeros n WHERE n.rifa_id = r.id AND n.status = 'pago') AS pagos FROM rifas r WHERE r.id = ?");
    $stmtCheck->execute([$rifa_id]);
    $rifaStatus = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if($rifaStatus['pagos'] < $rifaStatus['quantidade_numeros']) {
        die(json_encode(['error' => 'A rifa só pode ser finalizada e sorteada após ter 100% dos números vendidos e pagos.']));
    }

    $stmt = $pdo->prepare("SELECT n.numero, r.nome, r.whatsapp FROM numeros n JOIN reservas r ON n.reserva_id = r.id WHERE n.rifa_id = ? AND n.status = 'pago' ORDER BY RAND() LIMIT ?");
    $stmt->bindValue(1, $rifa_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $qtd, PDO::PARAM_INT);
    $stmt->execute();
    $winners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Finaliza a rifa
    $pdo->prepare("UPDATE rifas SET status = 'fechada' WHERE id = ?")->execute([$rifa_id]);

    echo json_encode(['success' => true, 'winners' => $winners]);
}
else if($action === 'save_integration') {
    $gateway = $_POST['gateway'] ?? '';
    $token = $_POST['token'] ?? '';
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes (chave VARCHAR(50) PRIMARY KEY, valor TEXT)");
    $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('gateway', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt->execute([$gateway, $gateway]);
    
    $stmt2 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('gateway_token', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt2->execute([$token, $token]);

    echo json_encode(['success' => true]);
}
else if($action === 'get_integration') {
    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes (chave VARCHAR(50) PRIMARY KEY, valor TEXT)");
    $stmt = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('gateway', 'gateway_token')");
    $conf = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    echo json_encode($conf ?: []);
}
else if($action === 'create_rifa') {
    $nome = $_POST['nome'] ?? 'Rifa Nova';
    $preco = $_POST['preco'] ?? 10.00;

    $stmtCheck = $pdo->query("SELECT COUNT(*) FROM rifas WHERE status = 'aberta'");
    if($stmtCheck->fetchColumn() >= 10) {
        die(json_encode(['error' => 'Limite atingido! Você só pode ter até 10 rifas ativas simultaneamente.']));
    }

    $pdo->beginTransaction();
    try {

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
    $stmt = $pdo->query("SELECT r.id, r.nome, r.preco_numero, r.status, r.quantidade_numeros, 
                        (SELECT COUNT(*) FROM numeros n WHERE n.rifa_id = r.id AND n.status = 'pago') AS pagos 
                        FROM rifas r ORDER BY r.id DESC");
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
