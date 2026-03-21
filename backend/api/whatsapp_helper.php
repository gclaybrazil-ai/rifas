<?php

function sendWhatsAppMessage($to, $message) {
    global $pdo;

    // Get configs
    $stmt = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('evolution_api_url', 'evolution_api_key', 'evolution_instance')");
    $conf = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $apiUrl = trim($conf['evolution_api_url'] ?? '');
    $apiKey = trim($conf['evolution_api_key'] ?? '');
    $instance = trim($conf['evolution_instance'] ?? '');

    if (empty($apiUrl) || empty($apiKey) || empty($instance)) {
        return ['success' => false, 'error' => 'Configurações de WhatsApp incompletas'];
    }

    // Clean number (ensure starts with 55)
    $to = preg_replace('/\D/', '', $to);
    if (strlen($to) < 10) return ['success' => false, 'error' => 'Número inválido'];
    
    // Auto-add 55 if missing (standard for BR)
    if (strlen($to) <= 11 && substr($to, 0, 2) != '55') {
        $to = '55' . $to;
    }
    
    $apiUrl = rtrim($apiUrl, '/ ');
    $instance = rawurlencode($instance);

    $endpoint = $apiUrl . "/message/sendText/" . $instance;
    file_put_contents(__DIR__ . '/whatsapp_endpoint_debug.log', "[" . date('Y-m-d H:i:s') . "] Endpoint: " . $endpoint . PHP_EOL, FILE_APPEND);
    
    $payload = [
        "number" => $to,
        "text" => $message,
        "options" => [
            "delay" => 1200,
            "presence" => "composing",
            "linkPreview" => false
        ]
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $apiKey,
        'ngrok-skip-browser-warning: 1'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        $log = "[" . date('Y-m-d H:i:s') . "] ERRO WHATSAPP: HTTP $httpCode | cURL Error: $curlError | Payload: " . json_encode($payload) . " | Response: $response" . PHP_EOL;
        file_put_contents(__DIR__ . '/whatsapp_errors.log', $log, FILE_APPEND);
        return ['success' => false, 'error' => 'Erro na API Evolution: HTTP ' . $httpCode, 'raw' => $response];
    }

    return ['success' => true];
}
