<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'Sessão expirada.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$rifa_id = $data['rifa_id'] ?? 0;

// 1. Get Tax for this Raffle
$stmt = $pdo->prepare("SELECT * FROM rifas WHERE id = ?");
$stmt->execute([$rifa_id]);
$rifa = $stmt->fetch();

if (!$rifa) {
    echo json_encode(['success' => false, 'error' => 'Rifa não encontrada.']);
    exit;
}

$total_colecao = $rifa['total_numeros'] * $rifa['valor_numero'];
$taxa = 7.00;
if ($total_colecao > 150000) $taxa = 3967.00;
else if ($total_colecao > 100000) $taxa = 2967.00;
else if ($total_colecao > 70000) $taxa = 1967.00;
else if ($total_colecao > 50000) $taxa = 1467.00;
else if ($total_colecao > 30000) $taxa = 967.00;
else if ($total_colecao > 20000) $taxa = 467.00;
else if ($total_colecao > 10000) $taxa = 217.00;
else if ($total_colecao > 7100) $taxa = 197.00;
else if ($total_colecao > 4000) $taxa = 127.00;
else if ($total_colecao > 2000) $taxa = 77.00;
else if ($total_colecao > 1000) $taxa = 67.00;
else if ($total_colecao > 701) $taxa = 47.00;
else if ($total_colecao > 400) $taxa = 37.00;
else if ($total_colecao > 200) $taxa = 27.00;
else if ($total_colecao > 100) $taxa = 17.00;
else $taxa = 7.00;

// 2. Get Master Config
$global = $pdo->query("SELECT * FROM global_config ORDER BY id DESC LIMIT 1")->fetch();
$token = $global['mp_access_token'] ?? '';
$chave = $global['chave_pix'] ?? '';

if (empty($token) && empty($chave)) {
    echo json_encode(['success' => false, 'error' => 'Sistema de pagamento não configurado pelo administrador.']);
    exit;
}

// 3. Generate PIX
if (!empty($token)) {
    // Webhook URL dinâmica — usa o host atual (funciona com ngrok, domínio próprio, etc.)
    $protocol    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host        = $_SERVER['HTTP_HOST'] ?? '';
    $self        = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $api_dir     = dirname($self); // /clone130326/vender/backend/api
    $webhook_url = $protocol . '://' . $host . $api_dir . '/webhook_ativacao.php';

    // Via Mercado Pago
    $payment_data = [
        "transaction_amount" => (float)$taxa,
        "description"        => "Ativação de Campanha SaaS - ID " . $rifa_id,
        "external_reference" => (string)$rifa_id,
        "payment_method_id"  => "pix",
        "notification_url"   => $webhook_url,
        "payer" => [
            "email"          => "pagamento@saas.com",
            "first_name"     => "Criador",
            "last_name"      => "SaaS",
            "identification" => [
                "type"   => "CPF",
                "number" => "19119119100"
            ]
        ]
    ];

    $ch = curl_init("https://api.mercadopago.com/v1/payments");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Importante para XAMPP local
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
        'X-Idempotency-Key: ' . md5($rifa_id . microtime())
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['point_of_interaction']['transaction_data']['qr_code'])) {
        echo json_encode([
            'success'    => true,
            'payment_id' => $result['id'] ?? '',   // ID do pagamento para polling ativo
            'qr_code'    => $result['point_of_interaction']['transaction_data']['qr_code_base64'] ?? '',
            'copy_paste' => $result['point_of_interaction']['transaction_data']['qr_code']
        ]);
    } else {
        $msg = $result['message'] ?? $curl_error ?? 'Erro desconhecido';
        echo json_encode(['success' => false, 'error' => 'Erro MP (Code '.$http_code.'): ' . $msg]);
    }
} else {
    // Via Chave Manual (Static QR Mockup for now, but returning the key)
    echo json_encode([
        'success' => true,
        'qr_code' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($chave),
        'copy_paste' => $chave,
        'is_manual' => true
    ]);
}
