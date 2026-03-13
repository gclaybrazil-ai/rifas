<?php
header('Content-Type: application/json');
require_once '../config.php';

try {
    $stmt = $pdo->query("SELECT id, nome, preco_numero, status, quantidade_numeros FROM rifas ORDER BY id DESC");
    $rifas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ativas = [];
    $finalizadas = [];

    foreach($rifas as &$rifa) {
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM numeros WHERE rifa_id = ? AND status IN ('pago', 'reservado')");
        $stmt2->execute([$rifa['id']]);
        $compraCount = $stmt2->fetchColumn();
        
        $rifa['percentual'] = $rifa['quantidade_numeros'] > 0 ? floor(($compraCount / $rifa['quantidade_numeros']) * 100) : 0;
        
        if($rifa['id'] == 1) {
            $rifa['imagem_url'] = 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
            $rifa['tag'] = '🔥 AO VIVO';
            $rifa['nomeCurto'] = 'Kit Churrascão';
        } else {
            $rifa['imagem_url'] = 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
            $rifa['tag'] = '🌟 RECENTE';
            $rifa['nomeCurto'] = 'Prêmio Eletrônico';
        }

        if($rifa['status'] === 'aberta') {
            $ativas[] = $rifa;
        } else {
            $rifa['tag'] = 'FINALIZADO';
            $finalizadas[] = $rifa;
        }
    }

    echo json_encode([
        'success' => true,
        'ativas' => $ativas,
        'finalizadas' => $finalizadas
    ]);

} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
