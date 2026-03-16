<?php
session_start();
require_once '../backend/config.php';

$token = $_GET['token'] ?? '';
$error = '';
$valid = false;

if ($token) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        if ($stmt->fetch()) {
            $valid = true;
        } else {
            $error = 'Link de recuperação inválido ou expirado.';
        }
    } catch (Exception $e) {
        $error = 'Erro ao validar link.';
    }
} else {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../frontend/png/cifrao.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-8 rounded-3xl shadow-lg border border-gray-100 max-w-sm w-full relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-green-400 to-[#00a650]"></div>

        <div class="text-center mb-8 mt-2">
            <h1 class="text-3xl font-black italic tracking-tighter" style="color: #00a650;">$UPER<span style="color: #2c3e50;">$ORTE</span></h1>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Redefinir Senha</p>
        </div>

        <?php if (!$valid): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 text-[11px] font-bold p-4 rounded-xl text-center mb-6">
                <?php echo $error; ?>
            </div>
            <a href="login.php" class="block w-full bg-[#2c3e50] text-white text-center font-black py-4 rounded-xl shadow uppercase text-sm hover:bg-gray-800 transition-colors">Voltar ao Login</a>
        <?php else: ?>
            <form id="reset-form" class="flex flex-col gap-4">
                <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
                <div id="msg" class="hidden p-3 rounded-xl text-[11px] font-bold text-center"></div>
                
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide ml-1">Nova Senha</label>
                    <input type="password" id="password" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-[#00a650]" required placeholder="Mínimo 6 caracteres">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide ml-1">Confirmar Senha</label>
                    <input type="password" id="confirm_password" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none focus:ring-2 focus:ring-[#00a650]" required placeholder="Repita a nova senha">
                </div>

                <button type="submit" id="btn-submit" class="w-full bg-[#00a650] text-white font-black py-4 rounded-xl shadow uppercase text-sm hover:bg-[#009647] transition-colors mt-2">
                    Alterar Senha
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
    if (document.getElementById('reset-form')) {
        document.getElementById('reset-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const p = document.getElementById('password').value;
            const cp = document.getElementById('confirm_password').value;
            const t = document.getElementById('token').value;
            const btn = document.getElementById('btn-submit');
            const msg = document.getElementById('msg');

            if (p.length < 6) {
                msg.textContent = 'A senha deve ter no mínimo 6 caracteres.';
                msg.className = 'p-3 rounded-xl text-[11px] font-bold text-center bg-red-50 text-red-600 border border-red-100';
                msg.classList.remove('hidden');
                return;
            }

            if (p !== cp) {
                msg.textContent = 'As senhas não coincidem.';
                msg.className = 'p-3 rounded-xl text-[11px] font-bold text-center bg-red-50 text-red-600 border border-red-100';
                msg.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = 'Salvando...';

            try {
                const res = await fetch('../backend/api/reset_password.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({token: t, password: p})
                });
                const data = await res.json();
                
                if (data.success) {
                    msg.textContent = 'Senha alterada com sucesso! Redirecionando...';
                    msg.className = 'p-3 rounded-xl text-[11px] font-bold text-center bg-green-50 text-green-700 border border-green-100';
                    msg.classList.remove('hidden');
                    setTimeout(() => window.location.href = 'login.php', 2000);
                } else {
                    msg.textContent = data.error;
                    msg.className = 'p-3 rounded-xl text-[11px] font-bold text-center bg-red-50 text-red-600 border border-red-100';
                    msg.classList.remove('hidden');
                    btn.disabled = false;
                    btn.innerHTML = 'Alterar Senha';
                }
            } catch (err) {
                msg.textContent = 'Erro de comunicação.';
                msg.className = 'p-3 rounded-xl text-[11px] font-bold text-center bg-red-50 text-red-600 border border-red-100';
                msg.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = 'Alterar Senha';
            }
        });
    }
    </script>
</body>
</html>
