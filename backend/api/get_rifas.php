<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
require_once '../config.php';

try {
    // Check maintenance mode
    $stmtM = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'modo_manutencao'");
    $isMaintenance = ($stmtM && $stmtM->fetchColumn() === '1');

    if ($isMaintenance) {
        die(json_encode(['success' => true, 'maintenance' => true]));
    }

    try {
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS imagem_url VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio1 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio2 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio3 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio4 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio5 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS sorteio_por VARCHAR(50) DEFAULT 'Loteria Federal'");
    } catch(PDOException $e) {}

    $stmt = $pdo->query("SELECT id, nome, preco_numero, status, quantidade_numeros, imagem_url, premio1, premio2, premio3, premio4, premio5, sorteio_por FROM rifas ORDER BY id DESC");
    $rifas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ativas = [];
    $finalizadas = [];

    foreach($rifas as &$rifa) {
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM numeros WHERE rifa_id = ? AND status IN ('pago', 'reservado')");
        $stmt2->execute([$rifa['id']]);
        $compraCount = $stmt2->fetchColumn();
        
        $rifa['percentual'] = $rifa['quantidade_numeros'] > 0 ? floor(($compraCount / $rifa['quantidade_numeros']) * 100) : 0;
        
        $rifa['nomeCurto'] = 'Prêmio ' . $rifa['id'];
        $rifa['tag'] = '🔥 AO VIVO';
        
        if(empty($rifa['imagem_url'])) {
           $rifa['imagem_url'] = 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'; 
        } else if(!str_starts_with($rifa['imagem_url'], 'http') && str_starts_with($rifa['imagem_url'], 'uploads/')) {
           $rifa['imagem_url'] = '' . $rifa['imagem_url']; // resolve base locally to htdocs
        }

        if($rifa['status'] === 'aberta') {
            $ativas[] = $rifa;
        } else {
            $rifa['tag'] = 'FINALIZADO';
            $finalizadas[] = $rifa;
        }
    }

    // Obter Link Suporte
    $link_suporte = '';
    try {
        $stmtS = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('whatsapp_suporte', 'mensagem_suporte')");
        $confS = $stmtS->fetchAll(PDO::FETCH_KEY_PAIR);
        $wa = $confS['whatsapp_suporte'] ?? '';
        $msg = $confS['mensagem_suporte'] ?? '';
        if(!empty($wa)) {
            $link_suporte = "https://wa.me/" . preg_replace('/\D/', '', $wa);
            if(!empty($msg)) $link_suporte .= "?text=" . urlencode($msg);
        }
    } catch(PDOException $e) {}

    // Obter Dados do Popup
    $popup = null;
    try {
        $stmtP = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave LIKE 'popup_%'");
        $popupData = $stmtP->fetchAll(PDO::FETCH_KEY_PAIR);
        if(($popupData['popup_active'] ?? '0') === '1') {
            $popup = [
                'title' => $popupData['popup_title'] ?? '',
                'content' => $popupData['popup_content'] ?? '',
                'image' => $popupData['popup_image'] ?? '',
                'image_fit' => $popupData['popup_image_fit'] ?? 'cover',
                'image_position' => $popupData['popup_image_position'] ?? 'center',
                'link' => $popupData['popup_link'] ?? '',
                'button' => $popupData['popup_button'] ?? 'Entendi',
                'video' => $popupData['popup_video'] ?? ''
            ];
        }
    } catch(PDOException $e) {}

    echo json_encode([
        'success' => true,
        'ativas' => $ativas,
        'finalizadas' => array_slice($finalizadas, 0, 5),
        'link_suporte' => $link_suporte,
        'popup' => $popup
    ]);

} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
