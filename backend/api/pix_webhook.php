<?php
// Tenta gravar log de entrada IMEDIATAMENTE
$rawPayload = file_get_contents('php://input');
$logMsg = "[" . date('Y-m-d H:i:s') . "] WEBHOOK RECEBIDO: " . $rawPayload . PHP_EOL;
file_put_contents(__DIR__ . '/webhook_debug.txt', $logMsg, FILE_APPEND);

header('Content-Type: application/json');

// SEGURANÇA: Validar Token HMAC (Opcional mas recomendado)
$tokenRecebido = $_GET['token'] ?? '';
$isMP = false;

// Tenta identificar se é Mercado Pago pelo User-Agent ou Payload
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (strpos($userAgent, 'MercadoPago') !== false) $isMP = true;

if ($tokenRecebido !== 'RIFA_SECURE_123' && !$isMP) {
    file_put_contents(__DIR__ . '/webhook_debug.txt', "ACESSO NEGADO: Token Invalido" . PHP_EOL, FILE_APPEND);
    die(json_encode(['error' => 'Acesso negado']));
}

try {
    require_once __DIR__ . '/../config.php';
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/webhook_debug.txt', "ERRO CONFIG: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    die();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode($rawPayload, true);
$txid = '';
$source = 'unknown';

if (isset($data['pix']) && is_array($data['pix'])) {
    foreach ($data['pix'] as $item) {
        if (isset($item['txid'])) { $txid = $item['txid']; break; }
    }
    $source = 'efi';
} else if (isset($data['resource']) && strpos($data['resource'], '/v2/cob/') !== false) {
    $parts = explode('/', $data['resource']);
    $txid = end($parts);
    $source = 'efi';
} else if (isset($data['type']) && $data['type'] === 'payment') {
    $txid = $data['data']['id'] ?? '';
    $source = 'mercadopago';
} else {
    $txid = $data['txid'] ?? $data['id'] ?? $data['data']['id'] ?? '';
}

file_put_contents(__DIR__ . '/webhook_debug.txt', "ORIGEM: $source | TXID: " . $txid . PHP_EOL, FILE_APPEND);

if (empty($txid)) die(json_encode(['msg' => 'OK']));

// SE FOR MERCADO PAGO, PRECISAMOS CONSULTAR SE O STATUS É 'approved'
if ($source === 'mercadopago') {
    $stmtG = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'gateway_token'");
    $mpToken = $stmtG->fetchColumn();
    
    if ($mpToken) {
        $ch = curl_init("https://api.mercadopago.com/v1/payments/$txid");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $mpToken"]);
        $resMP = curl_exec($ch);
        curl_close($ch);
        
        $mpData = json_decode($resMP, true);
        if (($mpData['status'] ?? '') !== 'approved') {
            file_put_contents(__DIR__ . '/webhook_debug.txt', "MP STATUS: " . ($mpData['status'] ?? 'null') . " - Ignorando." . PHP_EOL, FILE_APPEND);
            die(json_encode(['msg' => 'Aguardando aprovacao']));
        }
    }
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("SELECT id, status, afiliado_id, valor_total, rifa_id FROM reservas WHERE pix_txid = ?");
    $stmt->execute([$txid]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reserva && $reserva['status'] === 'pendente') {
        $pdo->prepare("UPDATE reservas SET status = 'pago' WHERE id = ?")->execute([$reserva['id']]);
        $pdo->prepare("UPDATE numeros SET status = 'pago' WHERE reserva_id = ?")->execute([$reserva['id']]);
        
        $afId = !empty($reserva['afiliado_id']) ? intval($reserva['afiliado_id']) : 0;
        if ($afId > 0) {
            $stmtC = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'comissao_padrao'");
            $pct = (float)($stmtC->fetchColumn() ?: 10);
            $comission = round(($reserva['valor_total'] * $pct) / 100, 2);
            $pdo->prepare("UPDATE afiliados SET saldo = saldo + ?, total_ganho = total_ganho + ?, vendas_pagas = vendas_pagas + 1 WHERE id = ?")
                ->execute([$comission, $comission, $afId]);
        }
        $pdo->commit();

        // --- WHATSAPP NOTIFICATION ---
        try {
            $stmtW = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'whatsapp_notify_pago'");
            if ($stmtW->fetchColumn() === '1') {
                require_once 'whatsapp_helper.php';
                
                $stmtD = $pdo->prepare("SELECT r.nome as comprador, r.whatsapp, r.rifa_id, ri.premio1, ri.premio2, ri.premio3, ri.premio4, ri.premio5, GROUP_CONCAT(n.numero) as nms 
                                        FROM reservas r 
                                        JOIN rifas ri ON r.rifa_id = ri.id 
                                        JOIN numeros n ON r.id = n.reserva_id
                                        WHERE r.id = ? GROUP BY r.id");
                $stmtD->execute([$reserva['id']]);
                $details = $stmtD->fetch(PDO::FETCH_ASSOC);

                if ($details) {
                    $prizes = "";
                    for($i=1; $i<=5; $i++) {
                        $prop = "premio" . $i;
                        if(!empty($details[$prop])) {
                            $prizes .= "\n- " . $i . "º Prêmio: " . $details[$prop];
                        }
                    }

                    $msg = "✅ *PAGAMENTO CONFIRMADO!*\n\n";
                    $msg .= "Olá *" . $details['comprador'] . "*,\n";
                    $msg .= "Seu pagamento para a rifa *#" . $details['rifa_id'] . "* foi recebido com sucesso!\n\n";
                    $msg .= "🎁 *Prêmios em jogo:*" . $prizes . "\n\n";
                    $msg .= "🎫 *Seus Números:* " . $details['nms'] . "\n\n";
                    $msg .= "Boa sorte! Acompanhe o sorteio em nosso site.";
                    
                    sendWhatsAppMessage($details['whatsapp'], $msg);
                }
            }
        } catch (Exception $eW) {
             file_put_contents(__DIR__ . '/webhook_debug.txt', "ERRO WHATSAPP: " . $eW->getMessage() . PHP_EOL, FILE_APPEND);
        }

        // --- 100% SOLD EMAIL NOTIFICATION ---
        try {
            $rifaId = $reserva['rifa_id'];
            $stmtStatus = $pdo->prepare("SELECT nome, quantidade_numeros, (SELECT COUNT(*) FROM numeros WHERE rifa_id = ? AND status = 'pago') AS pagos FROM rifas WHERE id = ?");
            $stmtStatus->execute([$rifaId, $rifaId]);
            $sR = $stmtStatus->fetch(PDO::FETCH_ASSOC);

            if ($sR && $sR['pagos'] >= $sR['quantidade_numeros']) {
                $rifaNome = $sR['nome'];
                $stmtConfMail = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave LIKE 'smtp_%'");
                $confMail = $stmtConfMail->fetchAll(PDO::FETCH_KEY_PAIR);

                $host = $confMail['smtp_host'] ?? '';
                $user_smtp = $confMail['smtp_user'] ?? '';
                $pass_smtp = $confMail['smtp_pass'] ?? '';
                $port = (int)($confMail['smtp_port'] ?? 465);
                $from_name = $confMail['smtp_from_name'] ?? 'Rifas Online';
                $from_email = $confMail['smtp_from_email'] ?? 'noreply@seusite.com';
                
                $stmtAdmEmail = $pdo->query("SELECT email FROM usuarios LIMIT 1");
                $admin_email = $stmtAdmEmail->fetchColumn();

                if (!$admin_email) {
                    $stmtConfAdmin = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'admin_email'");
                    $admin_email = $stmtConfAdmin->fetchColumn() ?: ($confMail['smtp_user'] ?? $from_email);
                }

                if (!empty($host) && !empty($user_smtp) && !empty($pass_smtp)) {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = $host;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $user_smtp;
                    $mail->Password   = $pass_smtp;
                    $mail->SMTPSecure = ($port == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $port;
                    $mail->CharSet    = 'UTF-8';
                    $mail->setFrom($from_email, $from_name);
                    $mail->addAddress($admin_email);
                    $mail->isHTML(true);
                    $mail->Subject = "RIFA 100% VENDIDA: {$rifaNome}";
                    $mail->Body    = "<h2>Parabens!</h2><p>A rifa #{$rifaId} atingiu 100% através de pagamento automático (Webhook).</p>";
                    $mail->send();
                }
            }
        } catch (Exception $eE) {
            file_put_contents(__DIR__ . '/webhook_debug.txt', "ERRO EMAIL 100%: " . $eE->getMessage() . PHP_EOL, FILE_APPEND);
        }
        // -----------------------------

        echo json_encode(['success' => true]);
    } else {
        $pdo->rollBack();
        echo json_encode(['msg' => 'Ignorado']);
    }
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}