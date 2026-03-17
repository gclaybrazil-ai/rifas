<?php
header('Content-Type: text/html; charset=utf-8');

$logFile = __DIR__ . '/backend/api/webhook_debug.txt';
$webhookUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/backend/api/pix_webhook.php";

echo "<h2>Depurador de Webhook</h2>";
echo "<p><b>URL que você deve cadastrar no seu banco (Efí ou Mercado Pago):</b><br>";
echo "<code style='background:#eee;padding:5px;display:block;'>$webhookUrl</code></p>";

if (isset($_GET['test'])) {
    $testMsg = "[" . date('Y-m-d H:i:s') . "] TESTE DE ESCRITA MANUAL" . PHP_EOL;
    if (file_put_contents($logFile, $testMsg, FILE_APPEND)) {
        echo "<p style='color:green'><b>Sucesso:</b> O servidor conseguiu escrever no arquivo de log!</p>";
    } else {
        echo "<p style='color:red'><b>Erro:</b> O servidor NÃO tem permissão para escrever na pasta <code>backend/api/</code>. Verifique as permissões (CHMOD 755 ou 777).</p>";
    }
}

echo "<hr>";
echo "<h3>Conteúdo dos Logs:</h3>";

if (file_exists($logFile)) {
    echo "<pre style='background:#f4f4f4;padding:15px;border:1px solid #ccc; max-height:400px; overflow:auto;'>";
    echo htmlspecialchars(file_get_contents($logFile));
    echo "</pre>";
} else {
    echo "<p>Nenhum log encontrado ainda. Isso significa que o banco nunca 'chamou' o seu site OU a pasta está sem permissão de escrita.</p>";
    echo "<a href='?test=1' style='background:#007bff;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;'>Testar Permissão de Escrita</a>";
}

echo "<br><br><button onclick='location.reload()'>Atualizar Página</button>";
