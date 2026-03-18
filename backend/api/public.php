<?php
header('Content-Type: application/json');
require_once '../config.php';

$action = $_GET['action'] ?? '';

if ($action === 'get_assistant_config') {
    $stmt = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('assistant_enabled', 'assistant_name', 'assistant_attendant', 'assistant_whatsapp')");
    $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    echo json_encode([
        'enabled' => $configs['assistant_enabled'] ?? '1',
        'name' => $configs['assistant_name'] ?? 'Assistente Top Sorte',
        'attendant' => $configs['assistant_attendant'] ?? 'David',
        'whatsapp' => $configs['assistant_whatsapp'] ?? '5511999999999',
        'messages' => $pdo->query("SELECT * FROM assistant_messages ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC)
    ]);
}
?>
