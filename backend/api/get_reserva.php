<?php
header('Content-Type: application/json');
require_once '../config.php';

$id = intval($_GET['id'] ?? 0);

if($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de reserva inválido']);
    exit;
}

try {
    $tempo_pagamento = 3;
    try {
        $stmtConf = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'tempo_pagamento'");
        if($stmtConf) {
            $val = $stmtConf->fetchColumn();
            if($val && is_numeric($val)) $tempo_pagamento = (int)$val;
        }
    } catch(PDOException $e) {}

    $stmt = $pdo->prepare("
        SELECT 
            r.id, r.rifa_id, r.status, r.valor_total, r.data_reserva, 
            r.pix_txid, r.pix_qrcode, r.pix_copiacola,
            rf.nome, rf.imagem_url,
            TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(r.data_reserva, INTERVAL ? MINUTE)) as remaining_seconds,
            (SELECT GROUP_CONCAT(n.numero ORDER BY n.numero ASC SEPARATOR ', ') FROM numeros n WHERE n.reserva_id = r.id) as numeros
        FROM reservas r
        JOIN rifas rf ON r.rifa_id = rf.id
        WHERE r.id = ?
    ");
    $stmt->execute([$tempo_pagamento, $id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if($res) {
        $group_vip = '';
        $gateway = '';
        $card_active = '0';
        $mp_public_key = '';

        try {
            $stmtConf = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('group_vip', 'gateway', 'card_active', 'mp_public_key')");
            if($stmtConf) {
                $configs = $stmtConf->fetchAll(PDO::FETCH_KEY_PAIR);
                $group_vip = $configs['group_vip'] ?? '';
                $gateway = $configs['gateway'] ?? '';
                $card_active = $configs['card_active'] ?? '0';
                $mp_public_key = $configs['mp_public_key'] ?? '';
            }
        } catch(PDOException $e) {}

        $res['group_vip'] = $group_vip;
        $res['gateway'] = $gateway;
        $res['card_active'] = $card_active;
        $res['mp_public_key'] = $mp_public_key;
        
        echo json_encode(['success' => true, 'data' => $res]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Reserva não encontrada']);
    }

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erro ao buscar dados']);
}    
?>
