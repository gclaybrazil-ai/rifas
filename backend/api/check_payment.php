<?php
header('Content-Type: application/json');
require_once '../config.php';

$reserva_id = $_GET['id'] ?? 0;
if (!$reserva_id) {
    die(json_encode(['error' => 'No ID provided']));
}

$stmt = $pdo->prepare("SELECT status, pix_txid FROM reservas WHERE id = ?");
$stmt->execute([$reserva_id]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res)
    die(json_encode(['status' => 'error']));

$status = $res['status'];
$txid = $res['pix_txid'];

if ($status === 'pendente') {
    // ACTIVE POLLING: Validação ativa via API (Útil para localhost/Testes quando o webhook não consegue chegar)
    try {
        $stmtConf = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('gateway', 'gateway_token', 'efi_client_id', 'efi_client_secret')");
        if ($stmtConf) {
            $configs = $stmtConf->fetchAll(PDO::FETCH_KEY_PAIR);
            $gateway = $configs['gateway'] ?? '';
            $token = $configs['gateway_token'] ?? '';
            $clientId = trim($configs['efi_client_id'] ?? '');
            $clientSecret = trim($configs['efi_client_secret'] ?? '');
            $certificate = __DIR__ . '/../certs/certificado_producao.p12';

            if ($gateway === 'mercadopago' && !empty($token) && is_numeric($txid)) {
                $ch = curl_init("https://api.mercadopago.com/v1/payments/$txid");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
                $resp = curl_exec($ch);
                curl_close($ch);

                $payment_info = json_decode($resp, true);
                if (isset($payment_info['status']) && $payment_info['status'] === 'approved') {
                    $pdo->prepare("UPDATE reservas SET status = 'pago' WHERE id = ?")->execute([$reserva_id]);
                    $pdo->prepare("UPDATE numeros SET status = 'pago' WHERE reserva_id = ?")->execute([$reserva_id]);
                    $status = 'pago';
                }
            } else if($gateway === 'efi' && !empty($clientId) && !empty($clientSecret) && file_exists($certificate)) {
                // 1. Auth Efí
                $ch = curl_init("https://pix.api.efipay.com.br/oauth/token");
                curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
                curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, '{"grant_type": "client_credentials"}');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Basic ' . base64_encode($clientId . ":" . $clientSecret),
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                $auth_res = curl_exec($ch);
                curl_close($ch);
                $auth_data = json_decode($auth_res, true);
                $access_token = $auth_data['access_token'] ?? '';

                if($access_token && !empty($txid)) {
                    // 2. Consulta Cobrança
                    $ch = curl_init("https://pix.api.efipay.com.br/v2/cob/" . $txid);
                    curl_setopt($ch, CURLOPT_SSLCERT, $certificate);
                    curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    $cob_res = curl_exec($ch);
                    curl_close($ch);
                    $cob_data = json_decode($cob_res, true);

                    if(isset($cob_data['status']) && $cob_data['status'] === 'CONCLUIDA') {
                        $pdo->prepare("UPDATE reservas SET status = 'pago' WHERE id = ?")->execute([$reserva_id]);
                        $pdo->prepare("UPDATE numeros SET status = 'pago' WHERE reserva_id = ?")->execute([$reserva_id]);
                        $status = 'pago';
                    }
                }
            }
        }
    } catch (PDOException $e) {
    }
}

// Obter Link do Grupo VIP
$group_vip = '';
try {
    $stmtG = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'group_vip'");
    if ($stmtG)
        $group_vip = $stmtG->fetchColumn() ?: '';
} catch (PDOException $e) {
}

echo json_encode([
    'status' => $status,
    'group_vip' => $group_vip
]);
?>