<?php
/**
 * check_ativacao.php
 * Polling ativo: consulta o MP diretamente para saber se o pagamento foi aprovado.
 * Inspirado no check_payment.php do sistema raiz /clone130326 que funciona perfeitamente.
 */
header('Content-Type: application/json');
require_once '../config.php';

$log_file = __DIR__ . '/webhook_log.txt';

$rifa_id    = intval($_GET['rifa_id'] ?? 0);
$payment_id = $_GET['payment_id'] ?? '';   // ID do pagamento MP retornado ao gerar o PIX

if (!$rifa_id) {
    echo json_encode(['status' => 'error', 'msg' => 'rifa_id obrigatório']);
    exit;
}

// 1. Verificar status atual da rifa no banco
$stmt = $pdo->prepare("SELECT status FROM rifas WHERE id = ?");
$stmt->execute([$rifa_id]);
$rifa = $stmt->fetch();

if (!$rifa) {
    echo json_encode(['status' => 'error', 'msg' => 'Rifa não encontrada']);
    exit;
}

// 2. Se já está ativa, retorna imediatamente
if ($rifa['status'] === 'ativa') {
    echo json_encode(['status' => 'ativa']);
    exit;
}

// 3. Se temos o payment_id, consultar o MP ativamente (igual ao check_payment.php do sistema raiz)
if (!empty($payment_id) && is_numeric($payment_id)) {
    try {
        $global = $pdo->query("SELECT mp_access_token FROM global_config ORDER BY id DESC LIMIT 1")->fetch();
        $token  = $global['mp_access_token'] ?? '';

        if (!empty($token)) {
            $ch = curl_init("https://api.mercadopago.com/v1/payments/" . $payment_id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $resp   = curl_exec($ch);
            curl_close($ch);

            $mp_data = json_decode($resp, true);
            $mp_status = $mp_data['status'] ?? '';

            file_put_contents($log_file,
                "[" . date('Y-m-d H:i:s') . "] check_ativacao: rifa=$rifa_id payment_id=$payment_id mp_status=$mp_status\n",
                FILE_APPEND
            );

            // 4. Se aprovado, ativar a rifa
            if ($mp_status === 'approved') {
                $pdo->prepare("UPDATE rifas SET status = 'ativa' WHERE id = ?")->execute([$rifa_id]);
                file_put_contents($log_file, "✅ Rifa $rifa_id ATIVADA via polling ativo!\n", FILE_APPEND);
                echo json_encode(['status' => 'ativa']);
                exit;
            }

            echo json_encode(['status' => $rifa['status'], 'mp_status' => $mp_status]);
            exit;
        }
    } catch (Exception $e) {
        file_put_contents($log_file, "ERRO check_ativacao: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

// Fallback: retorna o status atual do banco
echo json_encode(['status' => $rifa['status']]);
