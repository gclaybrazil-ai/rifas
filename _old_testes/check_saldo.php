<?php 
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require 'backend/config.php';
$stmtC = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('efi_client_id', 'efi_client_secret')");
$conf = $stmtC->fetchAll(PDO::FETCH_KEY_PAIR);
$clientId = trim($conf['efi_client_id']);
$clientSecret = trim($conf['efi_client_secret']);
$certificate = realpath(__DIR__ . '/backend/certs/certificado_producao.p12');

$ch = curl_init("https://pix.api.efipay.com.br/oauth/token");
curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{"grant_type": "client_credentials"}');
$base64 = base64_encode("$clientId:$clientSecret");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $base64", "Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$res = curl_exec($ch);
$tokenData = json_decode($res, true);
$accessToken = $tokenData['access_token'];

$ch3 = curl_init("https://pix.api.efipay.com.br/v2/gn/saldo");
curl_setopt($ch3, CURLOPT_SSLCERT, $certificate);
curl_setopt($ch3, CURLOPT_SSLCERTTYPE, "P12");
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch3, CURLOPT_SSL_VERIFYHOST, 0);
$res3 = curl_exec($ch3);
echo "SALDO EFI: " . $res3 . "\n";
?>
