<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Afiliado - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="frontend/png/cifrao.png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .gradient-text { background: linear-gradient(135deg, #8e44ad, #2c3e50); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-100 p-4 sticky top-0 z-40">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <img src="frontend/png/cifrao.png" class="w-8 h-8 animate-float" alt="Logo">
                <h1 class="text-xl font-black gradient-text">PROGRAMA DE AFILIADOS</h1>
            </div>
            <div class="flex items-center gap-4">
                <span id="session-timer" class="hidden text-[10px] font-black text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">EXPIRA EM: 20:00</span>
                <button id="btn-logout" class="hidden text-xs font-bold text-red-500 uppercase tracking-widest hover:bg-red-50 px-3 py-1.5 rounded-lg transition-all">Sair</button>
            </div>
        </div>
    </header>

    <main class="max-w-xl mx-auto p-4 py-10">

        <!-- Login / Registro Layout -->
        <div id="section-auth" class="glass rounded-[2rem] p-8 shadow-2xl border border-white">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-gray-800 uppercase">Seja um Parceiro</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Gere renda extra com segurança</p>
            </div>

            <form id="form-auth" class="space-y-4">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">WhatsApp (Somente números)</label>
                    <input type="text" id="auth-whatsapp" placeholder="Ex: 11999999999" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                </div>

                <div id="login-fields" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Sua Senha</label>
                        <input type="password" id="auth-senha" placeholder="******" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                    </div>
                </div>

                <div id="extra-fields" class="hidden space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Nome Completo</label>
                        <input type="text" id="auth-nome" placeholder="Seu nome" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Seu Melhor Email</label>
                        <input type="email" id="auth-email" placeholder="email@exemplo.com" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Chave PIX (Para receber)</label>
                        <input type="text" id="auth-pix" placeholder="CPF, Email, Celular ou Aleatória" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                    </div>
                </div>

                <button type="submit" id="btn-auth-submit" class="w-full bg-[#2c3e50] text-white font-black py-5 rounded-2xl shadow-xl hover:bg-gray-800 transition-all uppercase tracking-widest text-sm">Entrar / Cadastrar</button>
                
                <div class="text-center">
                    <button type="button" id="btn-forgot" class="text-[10px] font-black text-purple-600 uppercase tracking-widest hover:underline">Esqueci minha senha</button>
                </div>
            </form>
        </div>

        <!-- Token Handling Layout (Reset Password / Confirm Change) -->
        <div id="section-token" class="hidden glass rounded-[2rem] p-8 shadow-2xl border border-white text-center">
            <h2 id="token-title" class="text-xl font-black text-gray-800 uppercase mb-4">Confirmar Operação</h2>
            <div id="token-input-cont" class="hidden mb-6">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Nova Senha</label>
                <input type="password" id="token-valor" placeholder="No mínimo 6 caracteres" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
            </div>
            <p id="token-desc" class="text-sm text-gray-500 mb-6 font-medium">Clique no botão abaixo para concluir.</p>
            <button id="btn-execute-token" class="w-full bg-[#00a650] text-white font-black py-5 rounded-2xl shadow-xl hover:bg-[#009647] transition-all uppercase tracking-widest text-sm">Confirmar e Salvar</button>
        </div>

        <!-- Dashboard Layout -->
        <div id="section-dash" class="hidden space-y-6">
            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 text-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Saldo Atual</p>
                    <h3 class="text-2xl font-black text-purple-600" id="dash-saldo">R$ 0,00</h3>
                    <p class="text-[9px] text-gray-400 font-bold mt-2">SAQUE MÍN: R$ 20,00</p>
                </div>
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 text-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Total Ganho</p>
                    <h3 class="text-2xl font-black text-green-600" id="dash-total">R$ 0,00</h3>
                    <p class="text-[9px] text-gray-400 font-bold mt-2" id="dash-vendas">0 VENDAS PAGAS</p>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                <h3 class="text-sm font-black text-gray-800 uppercase mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 bg-purple-100 text-purple-600 rounded flex items-center justify-center">🔗</span>
                    Meus Links de Divulgação
                </h3>
                <div id="links-container" class="space-y-4"></div>
            </div>

            <div class="bg-gray-900 rounded-[2rem] p-8 text-white space-y-6">
                <div>
                    <h3 class="text-sm font-black uppercase mb-4 opacity-50">Configurações de Segurança</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-[9px] font-black uppercase tracking-widest opacity-40 ml-1">Chave PIX</label>
                            <div class="flex gap-2">
                                <input type="text" id="dash-pix-key" class="flex-1 bg-white/10 rounded-xl p-4 text-xs font-mono outline-none border border-white/5">
                                <button onclick="requestUpdate('pix')" class="bg-purple-600 font-black px-4 rounded-xl text-[10px] uppercase hover:bg-purple-700 transition-all">Alterar</button>
                            </div>
                        </div>
                        <div>
                            <label class="text-[9px] font-black uppercase tracking-widest opacity-40 ml-1">Email</label>
                            <div class="flex gap-2">
                                <input type="email" id="dash-email" class="flex-1 bg-white/10 rounded-xl p-4 text-xs font-mono outline-none border border-white/5">
                                <button onclick="requestUpdate('email')" class="bg-purple-600 font-black px-4 rounded-xl text-[10px] uppercase hover:bg-purple-700 transition-all">Alterar</button>
                            </div>
                        </div>
                    </div>
                    <p class="text-[9px] opacity-30 mt-6 font-medium uppercase tracking-widest text-center">Alterações de PIX ou Email exigem confirmação via link enviado ao seu email por segurança.</p>
                </div>
            </div>

        </div>

    </main>

    <!-- Modal Notificação -->
    <div id="modal-notif" class="fixed inset-0 bg-black/80 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl relative">
            <h2 id="notif-title" class="text-xl font-black text-gray-800 mb-2 uppercase">Atenção</h2>
            <p id="notif-message" class="text-sm text-gray-500 mb-6 font-medium">Informação aqui.</p>
            <button onclick="document.getElementById('modal-notif').classList.add('hidden')" class="w-full bg-purple-600 text-white font-black py-4 rounded-xl shadow-lg uppercase text-sm hover:bg-purple-700 transition-all">Entendido</button>
        </div>
    </div>

    <script>
        const API = 'backend/api/afiliado.php';
        let currentToken = '';
        let timerInterval = null;
        let secondsLeft = 0;

        async function checkSession() {
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');
            if (token) {
                currentToken = token;
                handleToken(token);
                return;
            }

            const res = await fetch(`${API}?action=get_stats`);
            const data = await res.json();
            if(data.afiliado) {
                secondsLeft = data.expires_in || 1200;
                startTimer();
                showDash(data);
            }
        }

        function startTimer() {
            if (timerInterval) clearInterval(timerInterval);
            const display = document.getElementById('session-timer');
            display.classList.remove('hidden');

            timerInterval = setInterval(() => {
                secondsLeft--;
                if (secondsLeft <= 0) {
                    clearInterval(timerInterval);
                    location.reload();
                    return;
                }

                const mins = Math.floor(secondsLeft / 60);
                const secs = secondsLeft % 60;
                display.textContent = `EXPIRA EM: ${mins}:${secs.toString().padStart(2, '0')}`;
            }, 1000);
        }

        async function handleToken(token) {
            const res = await fetch(`${API}?action=verify_token&token=${token}`);
            const data = await res.json();
            if (data.error) {
                showAlert(data.error);
                document.getElementById('section-auth').classList.remove('hidden');
            } else {
                document.getElementById('section-auth').classList.add('hidden');
                document.getElementById('section-token').classList.remove('hidden');
                
                if (data.tipo === 'reset_senha') {
                    document.getElementById('token-title').textContent = 'Redefinir Senha';
                    document.getElementById('token-input-cont').classList.remove('hidden');
                    document.getElementById('token-desc').textContent = 'Digite sua nova senha abaixo para recuperar o acesso.';
                } else if (data.tipo === 'update_pix') {
                    document.getElementById('token-title').textContent = 'Confirmar Nova Chave PIX';
                    document.getElementById('token-desc').textContent = 'Confirmamos que você solicitou a troca da chave PIX. Clique abaixo para ativar.';
                } else if (data.tipo === 'update_email') {
                    document.getElementById('token-title').textContent = 'Confirmar Novo Email';
                    document.getElementById('token-desc').textContent = 'Confirmamos que você solicitou a troca de email. Clique abaixo para ativar.';
                }
            }
        }

        document.getElementById('btn-execute-token').onclick = async () => {
            const valor = document.getElementById('token-valor').value;
            const fd = new FormData();
            fd.append('action', 'execute_token');
            fd.append('token', currentToken);
            fd.append('valor', valor);

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                showAlert(data.message, 'Sucesso');
                setTimeout(() => location.href = 'afiliado.php', 2000);
            } else {
                showAlert(data.error);
            }
        };

        document.getElementById('btn-forgot').onclick = async () => {
            const wa = document.getElementById('auth-whatsapp').value;
            if (!wa) return showAlert('Informe seu WhatsApp para recuperar a senha.');

            const fd = new FormData();
            fd.append('action', 'forgot_password');
            fd.append('whatsapp', wa);
            
            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) showAlert(data.message, 'Sucesso');
            else showAlert(data.error);
        };

        document.getElementById('form-auth').onsubmit = async (e) => {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action', 'login_register');
            fd.append('whatsapp', document.getElementById('auth-whatsapp').value);
            fd.append('nome', document.getElementById('auth-nome').value);
            fd.append('email', document.getElementById('auth-email').value);
            fd.append('senha', document.getElementById('auth-senha').value);
            fd.append('pix_key', document.getElementById('auth-pix').value);

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();

            if(data.error) {
                if(data.error.includes('preencha todos os campos')) {
                    document.getElementById('extra-fields').classList.remove('hidden');
                    document.getElementById('login-fields').classList.add('hidden');
                }
                showAlert(data.error);
            } else {
                checkSession();
            }
        };

        function showDash(data) {
            document.getElementById('section-auth').classList.add('hidden');
            document.getElementById('section-dash').classList.remove('hidden');
            document.getElementById('btn-logout').classList.remove('hidden');

            const af = data.afiliado;
            document.getElementById('dash-saldo').textContent = parseFloat(af.saldo).toLocaleString('pt-BR', {style: 'currency', currency:'BRL'});
            document.getElementById('dash-total').textContent = parseFloat(af.total_ganho).toLocaleString('pt-BR', {style: 'currency', currency:'BRL'});
            document.getElementById('dash-vendas').textContent = `${af.vendas_pagas} VENDAS PAGAS`;
            document.getElementById('dash-pix-key').value = af.pix_key;
            document.getElementById('dash-email').value = af.email;

            const cont = document.getElementById('links-container');
            cont.innerHTML = '';
            
            data.rifas.forEach(r => {
                const link = `${data.site_url}/rifa.html?id=${r.id}&ref=${af.id}`;
                const item = `
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <p class="text-[10px] font-black text-purple-600 uppercase mb-1">${r.nome}</p>
                        <div class="flex gap-2">
                            <input type="text" readonly value="${link}" class="flex-1 bg-white border border-gray-200 rounded-lg p-2 text-xs font-mono outline-none">
                            <button onclick="copyToClipboard('${link}')" class="bg-gray-800 text-white text-[10px] font-black px-4 rounded-lg uppercase tracking-widest hover:bg-black transition-all">Copiar</button>
                        </div>
                    </div>
                `;
                cont.insertAdjacentHTML('beforeend', item);
            });
        }

        async function requestUpdate(tipo) {
            const val = document.getElementById(tipo === 'pix' ? 'dash-pix-key' : 'dash-email').value;
            const fd = new FormData();
            fd.append('action', 'request_update');
            fd.append('tipo', tipo);
            fd.append('valor', val);

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            if(data.success) showAlert(data.message, 'Sucesso');
            else showAlert(data.error);
        }

        document.getElementById('btn-logout').onclick = async () => {
            await fetch(`${API}?action=logout`);
            location.reload();
        };

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            showAlert('Link copiado para a área de transferência!');
        }

        function showAlert(msg, title='Atenção') {
            document.getElementById('notif-title').textContent = title;
            document.getElementById('notif-message').textContent = msg;
            document.getElementById('modal-notif').classList.remove('hidden');
        }

        checkSession();
    </script>
</body>
</html>
