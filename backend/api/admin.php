<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    die(json_encode(['error' => 'Não autorizado']));
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'stats';

if ($action === 'stats') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    if ($page < 1)
        $page = 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $statusFilter = $_GET['status'] ?? '';

    $stmtStats = $pdo->query("SELECT status, COUNT(*) as qtd FROM numeros GROUP BY status");
    $stats = $stmtStats->fetchAll(PDO::FETCH_KEY_PAIR);

    $stmtConfig = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'tempo_pagamento'");
    $tempo_pagamento = (int)($stmtConfig->fetchColumn() ?: 3);

    $stmtFat = $pdo->query("SELECT SUM(valor_total) FROM reservas WHERE status = 'pago'");
    $faturamento = $stmtFat->fetchColumn() ?: 0;
    
    $stmtTaxa = $pdo->query("SELECT SUM(valor_taxa) FROM reservas WHERE status = 'pago'");
    $total_repassado = $stmtTaxa->fetchColumn() ?: 0;

    $where = "";
    $paramsCount = [];
    $paramsData = [];

    if (!empty($statusFilter)) {
        $where = " WHERE r.status = ? ";
        $paramsCount[] = $statusFilter;
        $paramsData[] = $statusFilter;
    }

    // Total de reservas para paginação
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM reservas r $where");
    $stmtCount->execute($paramsCount);
    $totalCount = $stmtCount->fetchColumn();
    $totalPages = ceil($totalCount / $limit);

    $sql = "SELECT r.*, rf.nome as rifa_nome FROM reservas r 
            LEFT JOIN rifas rf ON r.rifa_id = rf.id 
            $where 
            ORDER BY r.data_reserva DESC 
            LIMIT ? OFFSET ?";

    $stmt = $pdo->prepare($sql);

    $idx = 1;
    foreach ($paramsData as $p) {
        $stmt->bindValue($idx++, $p, PDO::PARAM_STR);
    }
    $stmt->bindValue($idx++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($idx++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar datas para ISO para o JS não se perder
    foreach($reservas as &$r) {
        $r['data_reserva_iso'] = date('c', strtotime($r['data_reserva']));
    }

    // Get maintenance and email settings
    $stmtM = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('modo_manutencao', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_name', 'smtp_from_email')");
    $configs = $stmtM ? $stmtM->fetchAll(PDO::FETCH_KEY_PAIR) : [];
    
    $stmtUser = $pdo->query("SELECT email, username FROM usuarios WHERE id = 1");
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'stats' => $stats,
        'faturamento' => $faturamento,
        'total_repassado' => $total_repassado,
        'reservas' => $reservas,
        'total_pages' => (int) $totalPages,
        'current_page' => (int) $page,
        'tempo_pagamento' => $tempo_pagamento,
        'maintenance' => $configs['modo_manutencao'] ?? '0',
        'email_config' => $configs,
        'admin_email' => $userData['email'] ?? '',
        'admin_user' => $userData['username'] ?? '',
        'server_time' => date('c')
    ]);
} else if ($action === 'billing_report') {
    $start = $_GET['start'] ?? '';
    $end = $_GET['end'] ?? '';
    $period = $_GET['period'] ?? ''; // '30', '7', 'today'

    $where = " WHERE status = 'pago' ";
    $params = [];

    if ($period === 'today') {
        $where .= " AND DATE(data_reserva) = CURDATE() ";
    } else if ($period === '7') {
        $where .= " AND data_reserva >= DATE_SUB(NOW(), INTERVAL 7 DAY) ";
    } else if ($period === '30') {
        $where .= " AND data_reserva >= DATE_SUB(NOW(), INTERVAL 30 DAY) ";
    } else if (!empty($start) && !empty($end)) {
        $where .= " AND data_reserva BETWEEN ? AND ? ";
        $params = [$start . ' 00:00:00', $end . ' 23:59:59'];
    }

    $stmt = $pdo->prepare("SELECT SUM(valor_total) as total, COUNT(*) as qtd FROM reservas $where");
    $stmt->execute($params);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'total' => (float) ($data['total'] ?: 0),
        'count' => (int) $data['qtd']
    ]);
} else if ($action === 'mark_paid') {
    $id = intval($_POST['id'] ?? 0);
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT id, status, afiliado_id, valor_total, rifa_id FROM reservas WHERE id = ?");
        $stmt->execute([$id]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reserva && $reserva['status'] === 'pendente') {
            // Marca como pago
            $pdo->prepare("UPDATE reservas SET status = 'pago' WHERE id = ?")->execute([$id]);
            $pdo->prepare("UPDATE numeros SET status = 'pago' WHERE reserva_id = ?")->execute([$id]);
            
            // Lógica de Comissão de Afiliado
            if (!empty($reserva['afiliado_id'])) {
                $afId = intval($reserva['afiliado_id']);
                $valorTotal = (float)$reserva['valor_total'];
                
                // Busca % de comissão
                $stmtC = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'comissao_padrao'");
                $comissionPct = (float)($stmtC->fetchColumn() ?: 10.00);
                $comissao = round(($valorTotal * $comissionPct) / 100, 2);
                
                // Atualiza saldo do afiliado
                $pdo->prepare("UPDATE afiliados SET saldo = saldo + ?, total_ganho = total_ganho + ?, vendas_pagas = vendas_pagas + 1 WHERE id = ?")
                    ->execute([$comissao, $comissao, $afId]);
            }

            $pdo->commit();

            // Webhook common logic: 100% sold check
            $rifaId = intval($reserva['rifa_id']);
            $stmtCountTotal = $pdo->prepare("SELECT COUNT(*) FROM numeros WHERE rifa_id = ?");
            $stmtCountTotal->execute([$rifaId]);
            $totalNums = (int)$stmtCountTotal->fetchColumn();

            $stmtCountPaid = $pdo->prepare("SELECT COUNT(*) FROM numeros WHERE rifa_id = ? AND status = 'pago'");
            $stmtCountPaid->execute([$rifaId]);
            $totalPaid = (int)$stmtCountPaid->fetchColumn();

            if ($totalPaid >= $totalNums && $totalNums > 0) {
                // Trigger 100% email notification (Same logic as webhook)
                require_once '../libs/PHPMailer/PHPMailer.php';
                require_once '../libs/PHPMailer/SMTP.php';
                require_once '../libs/PHPMailer/Exception.php';

                $stmtRifa = $pdo->prepare("SELECT nome FROM rifas WHERE id = ?");
                $stmtRifa->execute([$rifaId]);
                $rifaNome = $stmtRifa->fetchColumn();

                $stmtConfMail = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave LIKE 'smtp_%'");
                $confMail = $stmtConfMail->fetchAll(PDO::FETCH_KEY_PAIR);

                $host = $confMail['smtp_host'] ?? '';
                $user_smtp = $confMail['smtp_user'] ?? '';
                $pass_smtp = $confMail['smtp_pass'] ?? '';
                $port = (int)($confMail['smtp_port'] ?? 465);
                $from_name = $confMail['smtp_from_name'] ?? 'Rifas Online';
                $from_email = $confMail['smtp_from_email'] ?? 'noreply@seusite.com';
                $admin_email = $confMail['smtp_user'] ?? $from_email;

                if (!empty($host) && !empty($user_smtp) && !empty($pass_smtp)) {
                    try {
                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host       = $host;
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $user_smtp;
                        $mail->Password   = $pass_smtp;
                        $mail->SMTPSecure = ($port == 465) ? 'ssl' : 'tls';
                        $mail->Port       = $port;
                        $mail->CharSet    = 'UTF-8';
                        $mail->setFrom($from_email, $from_name);
                        $mail->addAddress($admin_email);
                        $mail->isHTML(true);
                        $mail->Subject = "RIFA 100% VENDIDA: {$rifaNome}";
                        $mail->Body    = "<h2>Parabens!</h2><p>A rifa #{$rifaId} atingiu 100%.</p>";
                        $mail->send();
                    } catch (Exception $e) {}
                }
            }

            echo json_encode(['success' => true]);
        } else {
            $pdo->rollBack();
            echo json_encode(['error' => 'Reserva nao encontrada ou ja paga.']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
    }
} else if ($action === 'draw_multiple') {
    $rifa_id = intval($_POST['rifa_id'] ?? 0);
    $qtd = intval($_POST['qtd'] ?? 1);
    if ($qtd < 1)
        $qtd = 1;
    if ($qtd > 5)
        $qtd = 5;

    $stmtCheck = $pdo->prepare("SELECT quantidade_numeros, (SELECT COUNT(*) FROM numeros n WHERE n.rifa_id = r.id AND n.status = 'pago') AS pagos FROM rifas r WHERE r.id = ?");
    $stmtCheck->execute([$rifa_id]);
    $rifaStatus = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($rifaStatus['pagos'] < $rifaStatus['quantidade_numeros']) {
        die(json_encode(['error' => 'A rifa só pode ser finalizada e sorteada após ter 100% dos números vendidos e pagos.']));
    }

    $manual_numbers = trim($_POST['manual'] ?? '');

    if (!empty($manual_numbers)) {
        // Manual Draw
        $pad_len = strlen((string) ($rifaStatus['quantidade_numeros'] - 1));
        $nums = explode(',', $manual_numbers);
        $cleanNums = [];
        foreach ($nums as $n) {
            $val = trim($n);
            if ($val !== '') {
                // Força o formato exato que está no banco (preenchido com zeros dependendo da qtd)
                if (is_numeric($val)) {
                    $val = str_pad((int) $val, $pad_len, '0', STR_PAD_LEFT);
                }
                $cleanNums[] = $val;
            }
        }

        if (count($cleanNums) === 0) {
            die(json_encode(['error' => 'Nenhum número válido informado.']));
        }

        $placeholders = str_repeat('?,', count($cleanNums) - 1) . '?';
        // MySQL FIELD() allows sorting exactly in the array order
        $sql = "SELECT n.numero, r.nome, r.whatsapp FROM numeros n JOIN reservas r ON n.reserva_id = r.id 
                WHERE n.rifa_id = ? AND n.status = 'pago' AND n.numero IN ($placeholders) 
                ORDER BY FIELD(n.numero, $placeholders)";

        $stmt = $pdo->prepare($sql);
        $params = array_merge([$rifa_id], $cleanNums, $cleanNums);
        $stmt->execute($params);
        $winners = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if all requested numbers were found and paid
        if (count($winners) !== count($cleanNums)) {
            die(json_encode(['error' => 'Um ou mais números informados não existem ou não estão pagos (ou foram informados incorretamente). Verifique os pagamentos.']));
        }

    } else {
        // Auto Draw
        $stmt = $pdo->prepare("SELECT n.numero, r.nome, r.whatsapp FROM numeros n JOIN reservas r ON n.reserva_id = r.id WHERE n.rifa_id = ? AND n.status = 'pago' ORDER BY RAND() LIMIT ?");
        $stmt->bindValue(1, $rifa_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $qtd, PDO::PARAM_INT);
        $stmt->execute();
        $winners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Finaliza a rifa
    $pdo->prepare("UPDATE rifas SET status = 'fechada' WHERE id = ?")->execute([$rifa_id]);

    // Save winners persistently
    $pdo->exec("CREATE TABLE IF NOT EXISTS ganhadores (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        rifa_id INT NOT NULL, 
        numero VARCHAR(10) NOT NULL, 
        nome VARCHAR(255) NOT NULL, 
        whatsapp VARCHAR(20) NOT NULL, 
        premio_ordem INT NOT NULL, 
        data_sorteio DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Clear old winners for this rifa just in case it's a re-draw (edge case)
    $pdo->prepare("DELETE FROM ganhadores WHERE rifa_id = ?")->execute([$rifa_id]);

    $stmtWin = $pdo->prepare("INSERT INTO ganhadores (rifa_id, numero, nome, whatsapp, premio_ordem) VALUES (?, ?, ?, ?, ?)");
    foreach ($winners as $index => $w) {
        $ordem = $index + 1;
        $stmtWin->execute([$rifa_id, $w['numero'], $w['nome'], $w['whatsapp'], $ordem]);
    }

    // Pegar prêmios para o feedback visual
    $stmtRifa = $pdo->prepare("SELECT premio1, premio2, premio3, premio4, premio5 FROM rifas WHERE id = ?");
    $stmtRifa->execute([$rifa_id]);
    $prizes = $stmtRifa->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'winners' => $winners, 'prizes' => $prizes]);
} else if ($action === 'save_integration') {
    $gateway = $_POST['gateway'] ?? '';
    $token = trim($_POST['token'] ?? '');
    $efi_client_id = trim($_POST['efi_client_id'] ?? '');
    $efi_client_secret = trim($_POST['efi_client_secret'] ?? '');

    $tempo_pagamento = $_POST['tempo_pagamento'] ?? '3';
    $group_vip = $_POST['group_vip'] ?? '';
    $whatsapp_suporte = $_POST['whatsapp_suporte'] ?? '';
    $mensagem_suporte = $_POST['mensagem_suporte'] ?? '';
    $repassar_taxa = $_POST['repassar_taxa'] ?? '0';
    $valor_taxa = $_POST['valor_taxa'] ?? '0.00';
    $whatsapp_share_template = $_POST['whatsapp_share_template'] ?? '';

    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes (chave VARCHAR(50) PRIMARY KEY, valor TEXT)");
    
    // Process Certificate Upload
    if(isset($_FILES['efi_cert_file']) && $_FILES['efi_cert_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../certs/';
        if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $filename = $_FILES['efi_cert_file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if($ext === 'p12') {
            $newName = 'certificado_producao.p12';
            move_uploaded_file($_FILES['efi_cert_file']['tmp_name'], $uploadDir . $newName);
            
            $stmtCert = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('efi_cert_name', ?) ON DUPLICATE KEY UPDATE valor = ?");
            $stmtCert->execute([$filename, $filename]);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('gateway', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt->execute([$gateway, $gateway]);

    $stmt2 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('gateway_token', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt2->execute([$token, $token]);
    
    $stmtE1 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('efi_client_id', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmtE1->execute([$efi_client_id, $efi_client_id]);

    $stmtE2 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('efi_client_secret', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmtE2->execute([$efi_client_secret, $efi_client_secret]);

    $stmt3 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('tempo_pagamento', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt3->execute([$tempo_pagamento, $tempo_pagamento]);

    $stmt4 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('group_vip', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt4->execute([$group_vip, $group_vip]);

    $stmt5 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('whatsapp_suporte', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt5->execute([$whatsapp_suporte, $whatsapp_suporte]);

    $stmt6 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('mensagem_suporte', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt6->execute([$mensagem_suporte, $mensagem_suporte]);

    $stmt7 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('repassar_taxa', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt7->execute([$repassar_taxa, $repassar_taxa]);

    $stmt8 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('valor_taxa', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt8->execute([$valor_taxa, $valor_taxa]);

    $stmt9 = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('whatsapp_share_template', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt9->execute([$whatsapp_share_template, $whatsapp_share_template]);

    echo json_encode(['success' => true]);
} else if ($action === 'get_integration') {
    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracoes (chave VARCHAR(50) PRIMARY KEY, valor TEXT)");
    $stmt = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('gateway', 'gateway_token', 'tempo_pagamento', 'group_vip', 'whatsapp_suporte', 'mensagem_suporte', 'efi_client_id', 'efi_client_secret', 'efi_cert_name', 'repassar_taxa', 'valor_taxa', 'whatsapp_share_template')");
    $conf = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    echo json_encode($conf ?: []);
} else if ($action === 'create_rifa') {
    $nome = $_POST['nome'] ?? 'Rifa Nova';
    $preco = $_POST['preco'] ?? 10.00;

    $imagem = $_POST['imagem'] ?? '';
    if (isset($_FILES['imagem_file']) && $_FILES['imagem_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['imagem_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('banner_') . '.' . $ext;
        if (move_uploaded_file($_FILES['imagem_file']['tmp_name'], $uploadDir . $filename)) {
            $imagem = 'uploads/' . $filename;
        }
    }

    $qtd = intval($_POST['qtd'] ?? 100);
    $qtd = max(10, min(10000, $qtd));
    $sorteio = $_POST['sorteio'] ?? 'Loteria Federal';
    $p1 = $_POST['p1'] ?? '';
    $p2 = $_POST['p2'] ?? '';
    $p3 = $_POST['p3'] ?? '';
    $p4 = $_POST['p4'] ?? '';
    $p5 = $_POST['p5'] ?? '';

    $stmtCheck = $pdo->query("SELECT COUNT(*) FROM rifas WHERE status = 'aberta'");
    if ($stmtCheck->fetchColumn() >= 10) {
        die(json_encode(['error' => 'Limite atingido! Você só pode ter até 10 rifas ativas simultaneamente.']));
    }

    try {
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS imagem_url VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio1 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio2 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio3 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio4 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS premio5 VARCHAR(255) DEFAULT ''");
        $pdo->exec("ALTER TABLE rifas ADD COLUMN IF NOT EXISTS sorteio_por VARCHAR(50) DEFAULT 'Loteria Federal'");
    } catch (PDOException $e) {
    }

    $pdo->beginTransaction();
    try {

        $stmt = $pdo->prepare("INSERT INTO rifas (nome, preco_numero, status, quantidade_numeros, imagem_url, premio1, premio2, premio3, premio4, premio5, sorteio_por) VALUES (?, ?, 'aberta', ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $preco, $qtd, $imagem, $p1, $p2, $p3, $p4, $p5, $sorteio]);
        $rifa_id = $pdo->lastInsertId();

        $insert_stmt = $pdo->prepare("INSERT INTO numeros (rifa_id, numero) VALUES (?, ?)");

        $pad_len = strlen((string) ($qtd - 1));

        // Disable unique checks temporarily to speed up mass insertion for 10.000 loop
        $pdo->exec("SET unique_checks=0;");
        $pdo->exec("SET foreign_key_checks=0;");

        // Chunk insertion for speed
        for ($i = 0; $i < $qtd; $i++) {
            $num = str_pad($i, $pad_len, '0', STR_PAD_LEFT);
            $insert_stmt->execute([$rifa_id, $num]);
        }

        $pdo->exec("SET unique_checks=1;");
        $pdo->exec("SET foreign_key_checks=1;");

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
    }
} else if ($action === 'edit_rifa') {
    $id = intval($_POST['id'] ?? 0);
    $nome = $_POST['nome'] ?? 'Rifa Nova';
    $preco = $_POST['preco'] ?? 10.00;

    $imagem = $_POST['imagem'] ?? '';
    if (isset($_FILES['imagem_file']) && $_FILES['imagem_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['imagem_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('banner_') . '.' . $ext;
        if (move_uploaded_file($_FILES['imagem_file']['tmp_name'], $uploadDir . $filename)) {
            $imagem = 'uploads/' . $filename; // override com upload
        }
    }

    $sorteio = $_POST['sorteio'] ?? 'Loteria Federal';
    $p1 = $_POST['p1'] ?? '';
    $p2 = $_POST['p2'] ?? '';
    $p3 = $_POST['p3'] ?? '';
    $p4 = $_POST['p4'] ?? '';
    $p5 = $_POST['p5'] ?? '';

    if ($imagem !== '') {
        $stmt = $pdo->prepare("UPDATE rifas SET nome=?, preco_numero=?, imagem_url=?, premio1=?, premio2=?, premio3=?, premio4=?, premio5=?, sorteio_por=? WHERE id=?");
        $stmt->execute([$nome, $preco, $imagem, $p1, $p2, $p3, $p4, $p5, $sorteio, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE rifas SET nome=?, preco_numero=?, premio1=?, premio2=?, premio3=?, premio4=?, premio5=?, sorteio_por=? WHERE id=?");
        $stmt->execute([$nome, $preco, $p1, $p2, $p3, $p4, $p5, $sorteio, $id]);
    }

    echo json_encode(['success' => true]);
} else if ($action === 'get_rifas_list') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    if ($page < 1) $page = 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    $statusFilter = $_GET['status'] ?? '';

    $where = "";
    $params = [];
    if (!empty($statusFilter)) {
        $where = " WHERE status = ? ";
        $params[] = $statusFilter;
    }

    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM rifas $where");
    $stmtCount->execute($params);
    $totalCount = $stmtCount->fetchColumn();
    $totalPages = ceil($totalCount / $limit);

    $sql = "SELECT r.id, r.nome, r.preco_numero, r.status, r.quantidade_numeros, 
            r.imagem_url, r.sorteio_por, r.premio1, r.premio2, r.premio3, r.premio4, r.premio5,
            (SELECT COUNT(*) FROM numeros n WHERE n.rifa_id = r.id AND n.status = 'pago') AS pagos 
            FROM rifas r $where ORDER BY r.id DESC LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $i = 1;
    foreach($params as $p) {
        $stmt->bindValue($i++, $p);
    }
    $stmt->bindValue($i++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($i++, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    echo json_encode([
        'rifas' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'total_pages' => (int)$totalPages,
        'current_page' => (int)$page
    ]);
} else if ($action === 'get_winners') {
    $rifa_id = intval($_GET['rifa_id'] ?? $_POST['rifa_id'] ?? 0);
    $pdo->exec("CREATE TABLE IF NOT EXISTS ganhadores (id INT AUTO_INCREMENT PRIMARY KEY, rifa_id INT NOT NULL, numero VARCHAR(10) NOT NULL, nome VARCHAR(255) NOT NULL, whatsapp VARCHAR(20) NOT NULL, premio_ordem INT NOT NULL, data_sorteio DATETIME DEFAULT CURRENT_TIMESTAMP)");

    $stmtRifa = $pdo->prepare("SELECT premio1, premio2, premio3, premio4, premio5 FROM rifas WHERE id = ?");
    $stmtRifa->execute([$rifa_id]);
    $prizes = $stmtRifa->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT numero, nome, whatsapp, premio_ordem FROM ganhadores WHERE rifa_id = ? ORDER BY premio_ordem ASC");
    $stmt->execute([$rifa_id]);
    echo json_encode(['winners' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'prizes' => $prizes]);
} else if ($action === 'delete_rifa') {
    $id = intval($_POST['id'] ?? 0);
    $pdo->prepare("DELETE FROM reservas WHERE rifa_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM numeros WHERE rifa_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM rifas WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true]);
} else if ($action === 'set_rifa_status') {
    $id = intval($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'fechada';

    // Optional: Only 1 active
    if ($status === 'aberta') {
        $pdo->exec("UPDATE rifas SET status = 'fechada'");
    }

    $pdo->prepare("UPDATE rifas SET status = ? WHERE id = ?")->execute([$status, $id]);
    echo json_encode(['success' => true]);
} else if ($action === 'save_publicacao') {
    $id = intval($_POST['id'] ?? 0);
    $nome = $_POST['nome'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $desc = $_POST['desc'] ?? '';

    $imagem = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('winner_') . '.' . $ext;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
            $imagem = 'uploads/' . $filename;
        }
    }

    if ($id > 0) {
        if ($imagem !== '') {
            $stmt = $pdo->prepare("UPDATE publicacoes_ganhadores SET nome_ganhador=?, numero_premiado=?, premio_descricao=?, imagem_url=? WHERE id=?");
            $stmt->execute([$nome, $numero, $desc, $imagem, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE publicacoes_ganhadores SET nome_ganhador=?, numero_premiado=?, premio_descricao=? WHERE id=?");
            $stmt->execute([$nome, $numero, $desc, $id]);
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO publicacoes_ganhadores (nome_ganhador, numero_premiado, premio_descricao, imagem_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $numero, $desc, $imagem]);
    }

    echo json_encode(['success' => true]);
} else if ($action === 'get_publicacoes_admin') {
    $stmt = $pdo->query("SELECT * FROM publicacoes_ganhadores ORDER BY data_publicacao DESC");
    echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} else if ($action === 'delete_publicacao') {
    $id = intval($_POST['id'] ?? 0);
    $pdo->prepare("DELETE FROM publicacoes_ganhadores WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true]);
} else if ($action === 'set_maintenance') {
    $status = $_POST['status'] ?? '0';
    $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES ('modo_manutencao', ?) ON DUPLICATE KEY UPDATE valor = ?");
    $stmt->execute([$status, $status]);
    echo json_encode(['success' => true]);
} else if ($action === 'update_access') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if(empty($user) || empty($email)) {
        die(json_encode(['error' => 'Usuário e email são obrigatórios']));
    }
    
    try {
        if(!empty($pass)) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET username = ?, password = ?, email = ? WHERE id = 1");
            $stmt->execute([$user, $hash, $email]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET username = ?, email = ? WHERE id = 1");
            $stmt->execute([$user, $email]);
        }
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        echo json_encode(['error' => 'Erro ao atualizar: ' . $e->getMessage()]);
    }
} else if ($action === 'save_smtp') {
    $host = $_POST['smtp_host'] ?? '';
    $port = $_POST['smtp_port'] ?? '';
    $user = $_POST['smtp_user'] ?? '';
    $pass = $_POST['smtp_pass'] ?? '';
    $from_name = $_POST['smtp_from_name'] ?? '';
    $from_email = $_POST['smtp_from_email'] ?? '';

    $params = [
        'smtp_host' => $host,
        'smtp_port' => $port,
        'smtp_user' => $user,
        'smtp_pass' => $pass,
        'smtp_from_name' => $from_name,
        'smtp_from_email' => $from_email
    ];

    foreach ($params as $key => $val) {
        $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->execute([$key, $val, $val]);
    }
    echo json_encode(['success' => true]);
}
?>