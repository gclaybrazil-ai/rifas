<?php
session_start();
if(isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Top Sorte</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-8 rounded-3xl shadow-lg border border-gray-100 max-w-sm w-full relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-green-400 to-[#00a650]"></div>

        <div class="text-center mb-8 mt-2">
            <h1 class="text-3xl font-black italic tracking-tighter" style="color: #00a650;">TOP<span style="color: #2c3e50;">SORTE</span></h1>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Acesso Restrito</p>
        </div>

        <form id="login-form" class="flex flex-col gap-4">
            <div id="error-msg" class="hidden bg-red-50 border border-red-100 text-red-600 text-[11px] font-bold p-3 rounded-xl text-center"></div>
            
            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide ml-1">Usuário</label>
                <input type="text" id="username" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-[#00a650] focus:border-[#00a650] outline-none transition-all" required autofocus>
            </div>
            
            <div class="flex flex-col gap-1 mb-2">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide ml-1">Senha</label>
                <input type="password" id="password" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-[#00a650] focus:border-[#00a650] outline-none transition-all" required>
            </div>

            <button type="submit" id="btn-submit" class="w-full bg-[#2c3e50] text-white font-black py-4 rounded-xl shadow uppercase tracking-wide text-sm hover:bg-gray-800 transition-colors">
                Entrar no Painel
            </button>
            <a href="../index.html" class="text-center text-[11px] text-gray-400 underline mt-2 hover:text-gray-600">Voltar para a Loja</a>
        </form>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const u = document.getElementById('username').value;
            const p = document.getElementById('password').value;
            const btn = document.getElementById('btn-submit');
            const errEl = document.getElementById('error-msg');
            
            btn.innerHTML = 'Validando...';
            btn.disabled = true;

            try {
                const res = await fetch('../backend/api/login.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username: u, password: p})
                });
                const data = await res.json();
                
                if(data.success) {
                    window.location.href = 'index.php';
                } else {
                    errEl.textContent = data.error;
                    errEl.classList.remove('hidden');
                    btn.innerHTML = 'Entrar no Painel';
                    btn.disabled = false;
                }
            } catch(err) {
                errEl.textContent = 'Erro de comunicação com o servidor.';
                errEl.classList.remove('hidden');
                btn.innerHTML = 'Entrar no Painel';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
