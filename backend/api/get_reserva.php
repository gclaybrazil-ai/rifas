<?php
header('Content-Type: application/json');
require_once '../config.php';

$id = intval($_GET['id'] ?? 0);

if($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de reserva inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            r.id, r.rifa_id, r.status, r.valor_total, r.data_reserva, 
            r.pix_txid, r.pix_qrcode, r.pix_copiacola,
            rf.nome, rf.imagem_url,
            (SELECT GROUP_CONCAT(n.numero ORDER BY n.numero ASC SEPARATOR ', ') FROM numeros n WHERE n.reserva_id = r.id) as numeros
        FROM reservas r
        JOIN rifas rf ON r.rifa_id = rf.id
        WHERE r.id = ?
    ");
    $stmt->execute([$id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if($res) {
        echo json_encode(['success' => true, 'data' => $res]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Reserva não encontrada']);
    }

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao buscar dados']);
}    
?>
