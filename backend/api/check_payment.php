<?php
header('Content-Type: application/json');
require_once '../config.php';

$reserva_id = $_GET['id'] ?? 0;
if(!$reserva_id) {
    die(json_encode(['error' => 'No ID provided']));
}

$stmt = $pdo->prepare("SELECT status, pix_txid FROM reservas WHERE id = ?");
$stmt->execute([$reserva_id]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$res) die(json_encode(['status' => 'error']));

$status = $res['status'];
$txid = $res['pix_txid'];

if ($status === 'pendente') {
    // ACTIVE POLLING: Validação ativa via API (Útil para localhost/Testes quando o webhook não consegue chegar)
    try {
        $stmtConf = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('gateway', 'gateway_token')");
        if($stmtConf) {
            $configs = $stmtConf->fetchAll(PDO::FETCH_KEY_PAIR);
            $gateway = $configs['gateway'] ?? '';
            $token = $configs['gateway_token'] ?? '';
            
            if($gateway === 'mercadopago' && !empty($token) && is_numeric($txid)) {
                $ch = curl_init("https://api.mercadopago.com/v1/payments/$txid");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
                $resp = curl_exec($ch);
                curl_close($ch);

                $payment_info = json_decode($resp, true);
                if(isset($payment_info['status']) && $payment_info['status'] === 'approved') {
                    $pdo->prepare("UPDATE reservas SET status = 'pago' WHERE id = ?")->execute([$reserva_id]);
                    $pdo->prepare("UPDATE numeros SET status = 'pago' WHERE reserva_id = ?")->execute([$reserva_id]);
                    $status = 'pago';
                }
            }
        }
    } catch(PDOException $e) {}
}

echo json_encode(['status' => $status]);
?>
