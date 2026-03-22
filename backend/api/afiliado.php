<?php
header('Content-Type: application/json');
require_once '../config.php';

// session_start and PHPMailer already in config.php
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Check Maintenance
$stmtM = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'modo_manutencao'");
if ($stmtM->fetchColumn() === '1') {
    die(json_encode(['error' => 'O sistema está em manutenção. Por favor, tente novamente mais tarde.', 'maintenance' => true]));
}

if ($action === 'login_register') {
    $whatsapp = preg_replace('/\D/', '', $_POST['whatsapp'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $pix_key = trim($_POST['pix_key'] ?? '');
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;

    if (empty($whatsapp)) die(json_encode(['error' => 'WhatsApp é obrigatório.']));

    $stmt = $pdo->prepare("SELECT * FROM afiliados WHERE whatsapp = ?");
    $stmt->execute([$whatsapp]);
    $afiliado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($afiliado) {
        // Login flow
        if (empty($senha)) die(json_encode(['error' => 'Informe sua senha.']));
        if (password_verify($senha, $afiliado['senha'])) {
            $check = checkLocationChallenge('afiliado', $afiliado['id'], $afiliado['email'], $afiliado['nome'], $lat, $lng);
            if (isset($check['challenge'])) {
                die(json_encode(['challenge_required' => true, 'message' => 'Novo local detectado. Verifique seu e-mail para autorizar este acesso.']));
            }

            $_SESSION['afiliado_id'] = $afiliado['id'];
            $_SESSION['afiliado_login_time'] = time();
            registrarLog('acao_afiliado', "Afiliado logado com sucesso", $afiliado['id'], null, $lat, $lng);
            echo json_encode(['success' => true, 'message' => 'Login realizado!']);
        } else {
            registrarLog('acao_afiliado', "Tentativa de login falhou (WP: $whatsapp)", null, null, $lat, $lng);
            echo json_encode(['error' => 'Senha incorreta.']);
        }
    } else {
        // Register flow
        $valid = validatePasswordComplexity($senha);
        if ($valid !== true) {
            die(json_encode(['error' => $valid]));
        }

        if (empty($nome) || empty($pix_key) || empty($email) || empty($senha)) {
            die(json_encode(['error' => 'Para novo cadastro, preencha todos os campos.']));
        }
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO afiliados (nome, whatsapp, email, senha, pix_key) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $whatsapp, $email, $hash, $pix_key]);
            $new_id = $pdo->lastInsertId();
            $_SESSION['afiliado_id'] = $new_id;
            $_SESSION['afiliado_login_time'] = time();
            registrarLog('acao_afiliado', "Novo afiliado cadastrado", $new_id, null, $lat, $lng);
            echo json_encode(['success' => true, 'message' => 'Cadastro realizado!']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) die(json_encode(['error' => 'Email ou WhatsApp já cadastrado.']));
            die(json_encode(['error' => 'Erro ao cadastrar: ' . $e->getMessage()]));
        }
    }

} else if ($action === 'forgot_password') {
    $whatsapp = preg_replace('/\D/', '', $_POST['whatsapp'] ?? '');
    if (empty($whatsapp)) die(json_encode(['error' => 'Informe o WhatsApp.']));

    $stmt = $pdo->prepare("SELECT id, nome, email FROM afiliados WHERE whatsapp = ?");
    $stmt->execute([$whatsapp]);
    $af = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$af) die(json_encode(['error' => 'WhatsApp não encontrado.']));

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $pdo->prepare("INSERT INTO afiliado_tokens (afiliado_id, token, tipo, data_expiracao) VALUES (?, ?, 'reset_senha', ?)")
        ->execute([$af['id'], $token, $expires]);

    $link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . str_replace('backend/api/afiliado.php', 'afiliado.php', $_SERVER['PHP_SELF']) . "?token=" . $token;
    
    $subject = "Recuperação de Senha - Afiliado";
    $message = "Olá {$af['nome']},\n\nPara redefinir sua senha de acesso ao painel de afiliados, clique no link abaixo:\n\n{$link}\n\nO link expira em 1 hora.";

    if (sendMailer($af['email'], $af['nome'], $subject, $message)) {
        echo json_encode(['success' => true, 'message' => 'Link de recuperação enviado para seu email.']);
    } else {
        echo json_encode(['error' => 'Falha ao enviar email. ' . (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ? "Simulação (Local): $link" : "")]);
    }

} else if ($action === 'verify_token') {
    $token = $_GET['token'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM afiliado_tokens WHERE token = ? AND usado = 0 AND data_expiracao > NOW()");
    $stmt->execute([$token]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$t) die(json_encode(['error' => 'Link inválido ou expirado.']));
    echo json_encode(['success' => true, 'tipo' => $t['tipo']]);

} else if ($action === 'execute_token') {
    $token = $_POST['token'] ?? '';
    $valor = $_POST['valor'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM afiliado_tokens WHERE token = ? AND usado = 0 AND data_expiracao > NOW()");
    $stmt->execute([$token]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$t) die(json_encode(['error' => 'Link inválido ou expirado.']));

    $pdo->beginTransaction();
    try {
        if ($t['tipo'] === 'reset_senha') {
            $valid = validatePasswordComplexity($valor);
            if ($valid !== true) throw new Exception($valid);
            $hash = password_hash($valor, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE afiliados SET senha = ? WHERE id = ?")->execute([$hash, $t['afiliado_id']]);
        } else if ($t['tipo'] === 'update_pix') {
            $pdo->prepare("UPDATE afiliados SET pix_key = ? WHERE id = ?")->execute([$t['novo_valor'], $t['afiliado_id']]);
        } else if ($t['tipo'] === 'update_email') {
            $pdo->prepare("UPDATE afiliados SET email = ? WHERE id = ?")->execute([$t['novo_valor'], $t['afiliado_id']]);
        }

        $pdo->prepare("UPDATE afiliado_tokens SET usado = 1 WHERE id = ?")->execute([$t['id']]);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Operação realizada com sucesso!']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => $e->getMessage()]);
    }

} else if ($action === 'get_stats') {
    if (!isset($_SESSION['afiliado_id'])) die(json_encode(['error' => 'Não logado']));

    $stmt = $pdo->prepare("SELECT id, nome, whatsapp, email, pix_key, saldo, total_ganho, vendas_pagas, data_ultimo_saque FROM afiliados WHERE id = ?");
    $stmt->execute([$_SESSION['afiliado_id']]);
    $af = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmtR = $pdo->query("SELECT id, nome, preco_numero, premio1, premio2, premio3, premio4, premio5 FROM rifas WHERE status = 'aberta' ORDER BY id DESC");
    $rifas = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    $stmtConf = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'whatsapp_share_template'");
    $shareTemplate = $stmtConf->fetchColumn() ?: "🎉 Participe da Rifa: {rifa_nome}\n\nConcorra agora: {link}";

    if (!isset($_SESSION['afiliado_login_time'])) {
        $_SESSION['afiliado_login_time'] = time();
    }
    $loginTime = $_SESSION['afiliado_login_time'];
    $elapsed = time() - $loginTime;
    $expiresIn = 300 - $elapsed;

    if ($expiresIn <= 0) {
        unset($_SESSION['afiliado_id']);
        unset($_SESSION['afiliado_login_time']);
        die(json_encode(['error' => 'Sessão expirada', 'expired' => true]));
    }

    echo json_encode([
        'afiliado' => $af,
        'rifas' => $rifas,
        'whatsapp_share_template' => $shareTemplate,
        'site_url' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . str_replace('/backend/api/afiliado.php', '', $_SERVER['PHP_SELF']),
        'expires_in' => $expiresIn
    ]);

} else if ($action === 'request_update') {
    if (!isset($_SESSION['afiliado_id'])) die(json_encode(['error' => 'Não logado']));
    $tipo = $_POST['tipo'] ?? ''; // 'pix' ou 'email'
    $novo_valor = trim($_POST['valor'] ?? '');

    if (empty($novo_valor)) die(json_encode(['error' => 'Novo valor é obrigatório.']));

    $stmt = $pdo->prepare("SELECT id, nome, email FROM afiliados WHERE id = ?");
    $stmt->execute([$_SESSION['afiliado_id']]);
    $af = $stmt->fetch(PDO::FETCH_ASSOC);

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $tipo_token = ($tipo === 'pix') ? 'update_pix' : 'update_email';

    $pdo->prepare("INSERT INTO afiliado_tokens (afiliado_id, token, tipo, novo_valor, data_expiracao) VALUES (?, ?, ?, ?, ?)")
        ->execute([$af['id'], $token, $tipo_token, $novo_valor, $expires]);

    $link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . str_replace('backend/api/afiliado.php', 'afiliado.php', $_SERVER['PHP_SELF']) . "?token=" . $token;
    
    $label = ($tipo === 'pix') ? "PIX" : "Email";
    $subject = "Confirmação de Alteração - {$label}";
    $message = "Olá {$af['nome']},\n\nVocê solicitou a alteração do seu {$label} para: {$novo_valor}.\n\nPara confirmar esta alteração por segurança, clique no link abaixo:\n\n{$link}\n\nSe não foi você, ignore este email.";

    if (sendMailer($af['email'], $af['nome'], $subject, $message)) {
        echo json_encode(['success' => true, 'message' => "Um link de confirmação foi enviado para o seu email atual ({$af['email']})."]);
    } else {
        echo json_encode(['error' => 'Falha ao enviar email. ' . (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ? "Simulação (Local): $link" : "")]);
    }

} else if ($action === 'request_payout') {
    if (!isset($_SESSION['afiliado_id'])) die(json_encode(['error' => 'Não logado']));
    
    $stmt = $pdo->prepare("SELECT id, saldo, pix_key, data_ultimo_saque FROM afiliados WHERE id = ?");
    $stmt->execute([$_SESSION['afiliado_id']]);
    $af = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validação de saldo mínimo
    $minPayout = 20.00;
    if ($af['saldo'] < $minPayout) {
        die(json_encode(['error' => 'Saldo insuficiente para saque. Valor mínimo: '.number_format($minPayout, 2, ',', '.')]));
    }

    // Validação de intervalo de 15 dias (Ciclo de Pagamento)
    if ($af['data_ultimo_saque']) {
        $ultimaData = new DateTime($af['data_ultimo_saque']);
        $hoje = new DateTime();
        $diff = $ultimaData->diff($hoje)->days;
        if ($diff < 15) {
            $prox = $ultimaData->modify('+15 days')->format('d/m/Y');
            die(json_encode(['error' => "Próximo saque disponível apenas em: $prox (Ciclo de 15 dias)"]));
        }
    }

    try {
        $pdo->beginTransaction();
        
        // Registrar Saque
        $stmtS = $pdo->prepare("INSERT INTO saques (afiliado_id, valor, chave_pix, status) VALUES (?, ?, ?, 'pendente')");
        $stmtS->execute([$af['id'], $af['saldo'], $af['pix_key']]);

        // Zerar saldo e ganhos do ciclo, e atualizar data do último saque
        $stmtU = $pdo->prepare("UPDATE afiliados SET saldo = 0, total_ganho = 0, data_ultimo_saque = NOW() WHERE id = ?");
        $stmtU->execute([$af['id']]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Solicitação de saque enviada com sucesso!']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Erro ao processar saque: ' . $e->getMessage()]);
    }

} else if ($action === 'get_payouts') {
    if (!isset($_SESSION['afiliado_id'])) die(json_encode(['error' => 'Não logado']));
    
    $stmt = $pdo->prepare("SELECT * FROM saques WHERE afiliado_id = ? ORDER BY data_solicitacao DESC");
    $stmt->execute([$_SESSION['afiliado_id']]);
    $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($payouts);

} else if ($action === 'logout') {
    unset($_SESSION['afiliado_id']);
    echo json_encode(['success' => true]);
}
