<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token inválido.");
}

try {
    $stmt = $pdo->prepare("SELECT id, user_type, user_id FROM login_autorizacoes WHERE token = ? AND autorizado = 0");
    $stmt->execute([$token]);
    $auth = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($auth) {
        $stmtUpdate = $pdo->prepare("UPDATE login_autorizacoes SET autorizado = 1, data_autenticacao = CURRENT_TIMESTAMP WHERE id = ?");
        $stmtUpdate->execute([$auth['id']]);

        $redirect = ($auth['user_type'] === 'admin') ? '../admin/login.php' : '../afiliado.php';

        echo "
        <div style='font-family: sans-serif; text-align: center; padding: 50px;'>
            <div style='font-size: 60px; margin-bottom: 20px;'>✅</div>
            <h1 style='color: #00a650; margin-bottom: 10px;'>Acesso Autorizado!</h1>
            <p style='color: #666;'>Este local foi liberado com sucesso. Você já pode realizar o login.</p>
            <br><br>
            <a href='{$redirect}' style='background: #2c3e50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 12px; font-weight: bold; font-size: 14px; text-transform: uppercase;'>Voltar ao Login</a>
        </div>
        ";
        
        registrarLog($auth['user_type'] === 'admin' ? 'acao_admin' : 'acao_afiliado', "Acesso via novo local CONFIRMADO pelo usuário", $auth['user_type'] === 'afiliado' ? $auth['user_id'] : null, $auth['user_type'] === 'admin' ? $auth['user_id'] : null);
    } else {
        echo "Este link já foi utilizado ou é inválido.";
    }
} catch (Exception $e) {
    echo "Erro ao processar autorização.";
}
