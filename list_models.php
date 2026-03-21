<?php
require_once 'c:/xampp/htdocs/clone130326/backend/config.php';
$stmtK = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'gemini_api_key' LIMIT 1");
$apiKey = $stmtK->fetchColumn();

$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
echo $response;
?>
