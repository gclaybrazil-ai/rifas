<?php
// Webhook para Ativações de Rifa (Criador -> Dono do SaaS)
require_once '../config.php';

$log_file = __DIR__ . '/webhook_log.txt';

// --- LOG COMPLETO de tudo que chega ---
$raw_input  = file_get_contents('php://input');
$data       = json_decode($raw_input, true);
$query      = $_GET ?? [];
$headers    = [];
foreach (getallheaders() ?: [] as $k => $v) {
    $headers[$k] = $v;
}

file_put_contents($log_file,
    "\n========== " . date('Y-m-d H:i:s') . " ==========\n" .
    "GET: "     . json_encode($query)   . "\n" .
    "BODY: "    . $raw_input            . "\n" .
    "HEADERS: " . json_encode($headers) . "\n",
    FILE_APPEND
);

// --- Extrair ID do pagamento (MP envia de formas diferentes) ---
$payment_id = $_GET['id']
    ?? $data['data']['id']
    ?? $data['id']
    ?? null;

$topic = $_GET['topic'] ?? $data['type'] ?? '';

file_put_contents($log_file,
    "payment_id extraído: " . $payment_id . "\n" .
    "topic/type: " . $topic . "\n",
    FILE_APPEND
);

// Só processa notificações de pagamento
if ($payment_id && in_array($topic, ['payment', 'merchant_order', ''])) {

    // 1. Buscar token MP
    $stmt   = $pdo->query("SELECT mp_access_token FROM global_config ORDER BY id DESC LIMIT 1");
    $global = $stmt->fetch();
    $token  = $global['mp_access_token'] ?? '';

    if (!empty($token)) {
        // 2. Consultar pagamento no MP
        $ch = curl_init("https://api.mercadopago.com/v1/payments/" . $payment_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        $status   = $result['status']             ?? 'N/A';
        $rifa_id  = $result['external_reference'] ?? null;

        file_put_contents($log_file,
            "MP status: $status | external_reference (rifa_id): $rifa_id\n" .
            "Resposta MP completa: " . $response . "\n",
            FILE_APPEND
        );

        // 3. Aprovar e ativar
        if ($status === 'approved' && $rifa_id) {
            $stmt = $pdo->prepare("UPDATE rifas SET status = 'ativa' WHERE id = ?");
            $stmt->execute([$rifa_id]);
            file_put_contents($log_file, "✅ Rifa $rifa_id ATIVADA com sucesso!\n", FILE_APPEND);
        }
    } else {
        file_put_contents($log_file, "❌ Token MP não encontrado em global_config\n", FILE_APPEND);
    }
} else {
    file_put_contents($log_file, "⚠️ Ignorado: payment_id=$payment_id | topic=$topic\n", FILE_APPEND);
}

// MP exige resposta 200
http_response_code(200);
echo json_encode(['status' => 'ok']);
