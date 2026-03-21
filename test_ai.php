<?php
header('Content-Type: application/json');
require_once 'c:/xampp/htdocs/clone130326/backend/config.php';

// Get API Key
$stmtK = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'gemini_api_key' LIMIT 1");
$apiKey = $stmtK->fetchColumn();

if(empty($apiKey)) {
    die(json_encode(['error' => 'API Key vazia no banco.']));
}

$userMsg = "Oi, como funciona o sorteio?";
$payload = [
    "contents" => [["parts" => [["text" => $userMsg]]]]
];

$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$err = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
    'key' => substr($apiKey, 0, 5) . '...',
    'http' => $httpCode,
    'curl_err' => $err,
    'res' => json_decode($response, true)
]);
?>
