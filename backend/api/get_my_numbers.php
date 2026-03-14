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
    $tempo_pagamento = 3; // Padrão
    try {
        $stmtConf = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'tempo_pagamento'");
        if($stmtConf) {
            $val = $stmtConf->fetchColumn();
            if($val && is_numeric($val)) $tempo_pagamento = (int)$val;
        }
    } catch(PDOException $e) {}

    // Auto expiração
    $stmtExpiradas = $pdo->query("SELECT id FROM reservas WHERE status='pendente' AND data_reserva < DATE_SUB(NOW(), INTERVAL $tempo_pagamento MINUTE)");
    $ids = $stmtExpiradas->fetchAll(PDO::FETCH_COLUMN);

    if(count($ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt1 = $pdo->prepare("UPDATE reservas SET status='expirado' WHERE id IN ($placeholders)");
        $stmt1->execute($ids);
        $stmt2 = $pdo->prepare("UPDATE numeros SET status='disponivel', reserva_id=NULL WHERE reserva_id IN ($placeholders)");
        $stmt2->execute($ids);
    }

    // Buscar reservas associadas a esse whatsapp, agrupando os numeros e informações da rifa
    $stmt = $pdo->prepare("
        SELECT 
            r.id as reserva_id,
            r.rifa_id,
            r.status,
            r.valor_total,
            r.data_reserva,
            TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(r.data_reserva, INTERVAL ? MINUTE)) as remaining_seconds,
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
    
    $stmt->execute([$tempo_pagamento, $likeWhat]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obter Link Suporte
    $link_suporte = '';
    try {
        $stmtS = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'link_suporte'");
        if($stmtS) $link_suporte = $stmtS->fetchColumn() ?: '';
    } catch(PDOException $e) {}

    echo json_encode(['success' => true, 'data' => $reservas, 'link_suporte' => $link_suporte]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>
