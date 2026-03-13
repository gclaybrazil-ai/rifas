<?php
header('Content-Type: application/json');
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

$rifa_id = 1; // Fixo para este protótipo
$nome = $data['nome'] ?? '';
$whatsapp = $data['whatsapp'] ?? '';
$numerosSelecionados = $data['numeros'] ?? [];

if(empty($nome) || empty($whatsapp) || empty($numerosSelecionados)) {
    die(json_encode(['error' => 'Por favor, preencha todos os dados.']));
}

if(count($numerosSelecionados) > 20) {
    die(json_encode(['error' => 'Máximo de 20 números por reserva.']));
}

$pdo->beginTransaction();

try {
    // Verificar se os números estão disponíveis travando a linha (FOR UPDATE)
    $placeholders = implode(',', array_fill(0, count($numerosSelecionados), '?'));
    $sql = "SELECT numero, status FROM numeros WHERE rifa_id = ? AND numero IN ($placeholders) FOR UPDATE";
    $stmt = $pdo->prepare($sql);
    $params = array_merge([$rifa_id], $numerosSelecionados);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($rows) !== count($numerosSelecionados)) {
        throw new Exception("Algum número informado não existe.");
    }

    foreach($rows as $r) {
        if($r['status'] !== 'disponivel') {
            throw new Exception("O número {$r['numero']} já foi reservado ou pago por outro usuário.");
        }
    }

    // Calcular valor
    $stmt = $pdo->prepare("SELECT preco_numero FROM rifas WHERE id = ?");
    $stmt->execute([$rifa_id]);
    $preco = $stmt->fetchColumn();
    $total = count($numerosSelecionados) * $preco;

    // Gerar txid PIX e payload Copia e Cola (Simulação. Na vida real: MercadoPago API)
    $txid = uniqid('PIX_');
    // QR Code dummy (imagem 1x1 transparente para teste visual)
    $pix_qrcode = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkCgMAAQQAATCwnmAAAAAASUVORK5CYII="; 
    // Copia e Cola Dummy
    $pix_copiacola = "00020101021226840014br.gov.bcb.pix2562pix.bcb.gov.br/api/v2/$txid";

    // Inserir reserva
    $stmt = $pdo->prepare("INSERT INTO reservas (rifa_id, nome, whatsapp, valor_total, data_reserva, status, pix_txid, pix_qrcode, pix_copiacola) VALUES (?, ?, ?, ?, NOW(), 'pendente', ?, ?, ?)");
    $stmt->execute([$rifa_id, $nome, $whatsapp, $total, $txid, $pix_qrcode, $pix_copiacola]);
    $reserva_id = $pdo->lastInsertId();

    // Atualizar números
    $updateStmt = $pdo->prepare("UPDATE numeros SET status = 'reservado', reserva_id = ? WHERE rifa_id = ? AND numero = ? AND status = 'disponivel'");
    foreach($numerosSelecionados as $num) {
        $updateStmt->execute([$reserva_id, $rifa_id, $num]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'reserva_id' => $reserva_id, 
        'pix_qrcode' => $pix_qrcode, 
        'pix_copiacola' => $pix_copiacola, 
        'total' => $total, 
        'txid' => $txid,
        'expire_in' => 5 * 60 // 5 minutos
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => $e->getMessage()]);
}
?>
