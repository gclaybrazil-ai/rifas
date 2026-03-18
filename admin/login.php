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
    <title>Login Admin - $UPER$ORTE</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../frontend/png/cifrao.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-8 rounded-3xl shadow-lg border border-gray-100 max-w-sm w-full relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-green-400 to-[#00a650]"></div>

        <div class="text-center mb-8 mt-2">
            <h1 class="text-3xl font-black italic tracking-tighter" style="color: #00a650;">$UPER<span style="color: #2c3e50;">$ORTE</span></h1>
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
            <div class="flex flex-col gap-2 mt-2">
                <button type="button" onclick="openRecovery()" class="text-center text-[11px] text-gray-400 hover:text-[#00a650] transition-colors">Esqueceu a senha?</button>
                <a href="../index.html" class="text-center text-[11px] text-gray-400 underline hover:text-gray-600">Voltar para a Loja</a>
            </div>
        </form>
    </div>

    <!-- Recovery Modal -->
    <div id="modal-recovery" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-left shadow-2xl relative">
            <button onclick="closeRecovery()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <h2 class="text-xl font-black text-gray-800 mb-2">Recuperar Acesso</h2>
            <p class="text-xs text-gray-500 mb-6">Informe seu email cadastrado para receber uma nova senha.</p>
            <div id="recovery-msg" class="hidden mb-4 p-3 rounded-xl text-[11px] font-bold text-center"></div>
            <form id="form-recovery" class="flex flex-col gap-4">
                <div>
                   <label class="text-[10px] font-bold text-gray-400 uppercase">Email Cadastrado</label>
                   <input type="email" id="recovery-email" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none" placeholder="exemplo@email.com" required>
                </div>
                <button type="submit" id="btn-recovery" class="w-full bg-[#00a650] text-white font-bold py-4 rounded-xl shadow uppercase text-sm hover:bg-[#009647] transition-colors">Enviar Nova Senha</button>
                <p class="text-[9px] text-gray-400 text-center italic">Verifique sua caixa de spam caso não encontre o email.</p>
            </form>
        </div>
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
                } else if (data.challenge_required) {
                    errEl.textContent = data.message;
                    errEl.className = 'bg-indigo-50 border border-indigo-100 text-indigo-700 text-[11px] font-bold p-4 rounded-xl text-center leading-relaxed';
                    errEl.classList.remove('hidden');
                    btn.innerHTML = 'Aguardando Autorização';
                } else {
                    errEl.textContent = data.error;
                    errEl.className = 'bg-red-50 border border-red-100 text-red-600 text-[11px] font-bold p-3 rounded-xl text-center';
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

        const modalRec = document.getElementById('modal-recovery');
        const msgRec = document.getElementById('recovery-msg');

        function openRecovery() {
            modalRec.classList.remove('hidden');
            setTimeout(() => modalRec.classList.add('opacity-100'), 10);
        }

        function closeRecovery() {
            modalRec.classList.remove('opacity-100');
            setTimeout(() => modalRec.classList.add('hidden'), 300);
        }

        document.getElementById('form-recovery').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('recovery-email').value;
            const btn = document.getElementById('btn-recovery');
            
            btn.innerHTML = 'Processando...';
            btn.disabled = true;
            msgRec.classList.add('hidden');

            try {
                const res = await fetch('../backend/api/recovery.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({email: email})
                });
                
                const data = await res.json().catch(() => null);
                
                if(data && data.success) {
                    msgRec.textContent = data.message;
                    msgRec.className = 'mb-4 p-3 rounded-xl text-[11px] font-bold text-center bg-green-50 text-green-700 border border-green-100';
                    msgRec.classList.remove('hidden');
                    document.getElementById('recovery-email').value = '';
                } else {
                    msgRec.textContent = data ? data.error : 'Erro no servidor. Verifique a configuração.';
                    msgRec.className = 'mb-4 p-3 rounded-xl text-[11px] font-bold text-center bg-red-50 text-red-700 border border-red-100';
                    msgRec.classList.remove('hidden');
                }
            } catch(err) {
                console.error(err);
                msgRec.textContent = 'Erro ao conectar. Verifique sua internet.';
                msgRec.className = 'mb-4 p-3 rounded-xl text-[11px] font-bold text-center bg-red-50 text-red-700 border border-red-100';
                msgRec.classList.remove('hidden');
            }
 finally {
                btn.innerHTML = 'Enviar Nova Senha';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
