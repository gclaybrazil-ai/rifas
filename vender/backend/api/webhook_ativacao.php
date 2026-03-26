<?php
// Webhook para Ativações de Rifa (Criador -> Dono do SaaS)
require_once '../config.php';

// Receber o corpo da requisição (Notificação do Mercado Pago)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Se for notificado sobre um ID de pagamento específico
$id = $_GET['id'] ?? $data['data']['id'] ?? null;

if ($id) {
    // 1. Fetch Master Config (Token)
    $stmt = $pdo->query("SELECT mp_access_token FROM global_config ORDER BY id DESC LIMIT 1");
    $global = $stmt->fetch();
    $token = $global['mp_access_token'] ?? '';

    if (!empty($token)) {
        // 2. Consultar o pagamento no Mercado Pago
        $ch = curl_init("https://api.mercadopago.com/v1/payments/" . $id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local env
        
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        curl_close($ch);

        $status = $result['status'] ?? '';
        $rifa_id = $result['external_reference'] ?? null;

        // 3. Se aprovado, ativar a rifa
        if ($status === 'approved' && $rifa_id) {
            $stmt = $pdo->prepare("UPDATE rifas SET status = 'ativa' WHERE id = ?");
            $stmt->execute([$rifa_id]);
            
            // Log de sucesso (OPCIONAL)
            file_put_contents('webhook_log.txt', "Rifa $rifa_id ativada via Webhook em " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        }
    }
}

// O Mercado Pago exige resposta 200 ou 201
http_response_code(200);
echo json_encode(['status' => 'ok']);
