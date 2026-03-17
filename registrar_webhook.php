<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'backend/config.php';

echo "<h2>Registrador de Webhook Efí (Solução Definitiva)</h2>";

try {
    $stmt = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('efi_client_id', 'efi_client_secret')");
    $conf = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $clientId = trim($conf['efi_client_id'] ?? '');
    $clientSecret = trim($conf['efi_client_secret'] ?? '');
    $certificate = __DIR__ . '/backend/certs/certificado_producao.p12';
    
    // URL BLINDADA (Conforme recomendação Efí)
    $webhookUrl = "https://" . $_SERVER['HTTP_HOST'] . "/backend/api/pix_webhook.php?token=RIFA_SECURE_123&ignorar=";

    if (empty($clientId) || empty($clientSecret)) die("<p style='color:red'>Erro: Configurações de API faltando no Admin.</p>");
    if (!file_exists($certificate)) die("<p style='color:red'>Erro: Certificado .p12 não encontrado.</p>");

    // 2. Token OAuth
    $ch = curl_init("https://pix.api.efipay.com.br/oauth/token");
    curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
    curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{"grant_type": "client_credentials"}');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode($clientId . ":" . $clientSecret), 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $auth_res = curl_exec($ch);
    $auth_data = json_decode($auth_res, true);
    $token = $auth_data['access_token'] ?? '';
    curl_close($ch);

    if (empty($token)) die("<p style='color:red'>Erro ao autenticar com a Efí. Verifique suas credenciais.</p>");

    echo "<div style='background:#f9f9f9; padding:15px; border:1px solid #ddd; border-radius:5px;'>
            <p><b>DICA:</b> Usando a técnica de 'Header Skip' para servidores compartilhados.</p>
          </div><br>";

    echo "<form method='POST'>
            <p>Informe sua Chave PIX (CPF, Celular, E-mail ou Chave Aleatória):</p>
            <input type='text' name='chave_pix' placeholder='Sua Chave Pix' style='padding:10px; width:300px;' required>
            <button type='submit' style='padding:10px 20px; background:#007bff; color:#fff; border:none; border-radius:5px; cursor:pointer;'>Registrar Webhook Agora</button>
          </form>";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['chave_pix'])) {
        $chave = trim($_POST['chave_pix']);
        
        // A SOLUÇÃO DEFINITIVA: O parâmetro 'x-skip-mtls-checking' deve ir no HEADER!
        $urlApi = "https://pix.api.efipay.com.br/v2/webhook/$chave";
        
        $ch = curl_init($urlApi);
        curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['webhookUrl' => $webhookUrl]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'x-skip-mtls-checking: true' // ESTE É O HEADER QUE RESOLVE O ERRO 400
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 201 || $http_code == 200 || $http_code == 204) {
            echo "<p style='color:green; font-weight:bold; font-size:1.2em;'>✓ SUCESSO ABSOLUTO! O Webhook foi registrado!</p>";
            echo "<p>A Efí aceitou o 'Skip-MTLS' via Header. Agora os pagamentos reais serão aprovados automaticamente.</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>Falhou com Erro $http_code</p>";
            echo "<pre style='background:#000; color:#fff; padding:10px;'>" . htmlspecialchars($res) . "</pre>";
        }
    }
} catch (Exception $e) { echo $e->getMessage(); }
