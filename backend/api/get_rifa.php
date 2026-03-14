<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
require_once '../config.php';

// Limpar reservas expiradas automaticamente (Baseado na configuração do admin)
$tempo_pagamento = 3; // Padrão
try {
    $stmtConf = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'tempo_pagamento'");
    if($stmtConf) {
        $val = $stmtConf->fetchColumn();
        if($val && is_numeric($val)) $tempo_pagamento = (int)$val;
    }
} catch(PDOException $e) {}

// Obter os IDs das reservas expiradas com base no tempo configurado
$stmtExpiradas = $pdo->query("SELECT id FROM reservas WHERE status='pendente' AND data_reserva < DATE_SUB(NOW(), INTERVAL $tempo_pagamento MINUTE)");
$ids = $stmtExpiradas->fetchAll(PDO::FETCH_COLUMN);

if(count($ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    // Atualizar status da reserva
    $stmt1 = $pdo->prepare("UPDATE reservas SET status='expirado' WHERE id IN ($placeholders)");
    $stmt1->execute($ids);
    // Liberar números
    $stmt2 = $pdo->prepare("UPDATE numeros SET status='disponivel', reserva_id=NULL WHERE reserva_id IN ($placeholders)");
    $stmt2->execute($ids);
}

// Obter a rifa
$rifa_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($rifa_id > 0) {
    try {
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS imagem_url VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio1 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio2 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio3 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio4 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio5 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS sorteio_por VARCHAR(50) DEFAULT 'Loteria Federal'");
    } catch(PDOException $e) {}

    $stmt = $pdo->prepare("SELECT id, nome, preco_numero, status, quantidade_numeros, imagem_url, premio1, premio2, premio3, premio4, premio5, sorteio_por FROM rifas WHERE id = ?");
    $stmt->execute([$rifa_id]);
} else {
    $stmt = $pdo->query("SELECT id, nome, preco_numero, status, quantidade_numeros, imagem_url, premio1, premio2, premio3, premio4, premio5, sorteio_por FROM rifas WHERE status = 'aberta' ORDER BY id DESC LIMIT 1");
}
$rifa = $stmt->fetch(PDO::FETCH_ASSOC);

if($rifa) {
    $rifa_id = $rifa['id'];
    if(empty($rifa['imagem_url'])) {
        $rifa['imagem_url'] = 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';
    }
}

if(!$rifa) {
    die(json_encode(['error' => 'Rifa não encontrada']));
}

// Obter os números da rifa
$stmt = $pdo->prepare("SELECT n.numero, n.status, n.reserva_id, r.nome AS comprador FROM numeros n LEFT JOIN reservas r ON n.reserva_id = r.id WHERE n.rifa_id = ? ORDER BY n.numero ASC");
$stmt->execute([$rifa_id]);
$numeros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter Link do Grupo VIP
$group_vip = '';
try {
    $stmtG = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'group_vip'");
    if($stmtG) $group_vip = $stmtG->fetchColumn() ?: '';
} catch(PDOException $e) {}

// Obter Taxas
$repassar_taxa = '0';
try {
    $stmtT = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'repassar_taxa'");
    if($stmtT) {
        $repassar_taxa = $stmtT->fetchColumn() ?: '0';
    }
} catch(PDOException $e) {}

echo json_encode([
    'rifa' => $rifa,
    'numeros' => $numeros,
    'group_vip' => $group_vip,
    'repassar_taxa' => $repassar_taxa,
    'valor_taxa' => '0.10' // Valor fixo de conveniência padrão
]);
?>
