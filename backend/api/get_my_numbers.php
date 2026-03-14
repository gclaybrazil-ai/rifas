<?php
header('Content-Type: application/json');
require_once '../config.php';

$whatsapp = $_GET['whatsapp'] ?? '';
$whatsapp = preg_replace('/[^0-9]/', '', $whatsapp); // clean masks

if(empty($whatsapp)) {
    echo json_encode(['success' => false, 'error' => 'WhatsApp não informado']);
    exit;
}

try {
    // Buscar reservas associadas a esse whatsapp, agrupando os numeros e informações da rifa
    $stmt = $pdo->prepare("
        SELECT 
            r.id as reserva_id,
            r.rifa_id,
            r.status,
            r.valor_total,
            r.data_reserva,
            rf.nome as rifa_nome,
            rf.imagem_url,
            GROUP_CONCAT(n.numero ORDER BY n.numero ASC SEPARATOR ', ') as numeros
        FROM reservas r
        JOIN rifas rf ON r.rifa_id = rf.id
        LEFT JOIN numeros n ON n.reserva_id = r.id
        WHERE REPLACE(REPLACE(REPLACE(REPLACE(r.whatsapp, '(', ''), ')', ''), '-', ''), ' ', '') LIKE ?
        GROUP BY r.id
        ORDER BY r.data_reserva DESC
    ");
    
    // We'll match ends with since user might type with or without country code
    $likeWhat = '%' . $whatsapp; 
    
    $stmt->execute([$likeWhat]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $reservas]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>
