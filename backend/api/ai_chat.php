<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config.php';

// Check if AI is enabled in config? 
// For now, we assume it's a test.

$userMsg = $_POST['message'] ?? '';
if(empty($userMsg)) die(json_encode(['error' => 'Mensagem vazia']));

// Get API Key
$stmtK = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'gemini_api_key' LIMIT 1");
$apiKey = $stmtK->fetchColumn();

if(empty($apiKey)) {
    // If no key, return a fallback message for the UI to handle or a friendly error
    die(json_encode(['error' => 'Configuração Pendente: A Chave de API do Gemini ainda não foi cadastrada no painel administrador.']));
}

// Prepare System Prompt with Context
$stmtInfo = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('assistant_name', 'assistant_attendant', 'assistant_whatsapp')");
$siteConf = $stmtInfo->fetchAll(PDO::FETCH_KEY_PAIR);

$botName = $siteConf['assistant_name'] ?? 'Assistente Virtual';
$attendant = $siteConf['assistant_attendant'] ?? 'David';
$wa = $siteConf['assistant_whatsapp'] ?? '5511999999999';

// Get Active Raffle for context
$stmtR = $pdo->query("SELECT nome, preco_numero, premio1, premio2, premio3, premio4, premio5 FROM rifas WHERE status = 'aberta' ORDER BY id DESC LIMIT 1");
$rifa = $stmtR->fetch(PDO::FETCH_ASSOC);

$rifaContext = "";
if($rifa) {
    $prizes = "1º: " . $rifa['premio1'];
    if($rifa['premio2']) $prizes .= ", 2º: " . $rifa['premio2'];
    if($rifa['premio3']) $prizes .= ", 3º: " . $rifa['premio3'];
    if($rifa['premio4']) $prizes .= ", 4º: " . $rifa['premio4'];
    if($rifa['premio5']) $prizes .= ", 5º: " . $rifa['premio5'];

    $rifaContext = "Atualmente temos a rifa '" . $rifa['nome'] . "' ativa, custando R$ " . number_format($rifa['preco_numero'], 2, ',', '.') . " cada número. Os prêmios são: $prizes.";
}

// Get Custom Answers from Admin Panel
$stmtCustom = $pdo->query("SELECT pergunta, resposta FROM assistant_messages");
$customAnswers = $stmtCustom->fetchAll(PDO::FETCH_ASSOC);

$baseConhecimento = "MANUAL DE RESPOSTAS ESPECÍFICAS:\n";
foreach($customAnswers as $ca) {
    $baseConhecimento .= "- Pergunta: " . $ca['pergunta'] . " | Resposta: " . $ca['resposta'] . "\n";
}

$systemPrompt = "Você é o $botName, um assistente virtual inteligente e amigável da plataforma de rifas online '\$UPER\$ORTE'.
Seu objetivo é ajudar os clientes de forma rápida, educada e bem-humorada.
Você deve responder em Português do Brasil (PT-BR).

REGRAS DO SITE:
1. PAGAMENTO: É feito exclusivamente via PIX com confirmação automática após a reserva.
2. SORTEIO: Baseado no resultado oficial da Loteria Federal. O sorteio só ocorre após 100% das cotas pagas.
3. SEGURANÇA: O site é 100% seguro, criptografado e as transações são bancárias reais.
4. MEUS PEDIDOS: O cliente pode ver seus números no botão 'Meus Pedidos' informando o WhatsApp.
5. ATENDENTE HUMANO: Se o cliente pedir para falar com alguém real, diga que ele pode clicar no botão de 'Falar com Atendente' ou entrar em contato pelo WhatsApp $wa. O nome do atendente responsável é $attendant.
6. CONTEXTO ATUAL: $rifaContext

$baseConhecimento

IMPORTANTE: Seja conciso, use emojis e PRIORIZE as respostas do 'MANUAL DE RESPOSTAS ESPECÍFICAS' se o assunto for mencionado. Se não souber algo, peça para o cliente falar com a equipe de suporte.";

// Correct the payload for Gemini 1.5 API
$payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => "CONTEXTO DO SISTEMA:\n" . $systemPrompt . "\n\nPERGUNTA DO CLIENTE:\n" . $userMsg]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 800,
    ]
];

$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL verification for local servers

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseData = json_decode($response, true);

if ($httpCode === 200 && isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['success' => true, 'response' => $aiResponse]);
} else {
    // Return error or fallback
    $err = $responseData['error']['message'] ?? 'Erro desconhecido na API do Google';
    echo json_encode(['error' => 'Houve um problema na comunicação com a IA.', 'debug' => $err]);
}
?>
