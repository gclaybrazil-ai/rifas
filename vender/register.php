<?php
require_once 'backend/config.php';
// Se já estiver logado, vai pro dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torne-se um Criador - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .gradient-text {
            background: linear-gradient(90deg, #00a650, #2c3e50);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex flex-col antialiased">

    <!-- Navbar Minimalista -->
    <header class="p-6 bg-white/50 backdrop-blur-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-xl font-black italic tracking-tighter text-[#00a650]">
                $UPER<span style="color: #2c3e50;">$ORTE</span>
            </a>
            <a href="index.php" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-800 transition-colors">Voltar</a>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
            
            <div class="text-center mb-10">
                <h1 class="text-3xl font-black text-[#2c3e50] tracking-tighter uppercase italic leading-[0.9] mb-4">
                    Seu Império <br> <span class="gradient-text">Começa Aqui.</span>
                </h1>
                <p class="text-gray-500 font-medium text-sm">Crie sua conta no SaaS e comece a faturar.</p>
            </div>

            <!-- Card de Registro -->
            <div class="bg-white rounded-[2.5rem] shadow-2xl p-8 border border-gray-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-green-50 rounded-full blur-3xl -mr-16 -mt-16"></div>
                
                <form id="registerForm" class="space-y-4 relative z-10">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Nome Completo</label>
                        <input type="text" name="nome" required 
                               class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="Ex: João Silva">
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">WhatsApp</label>
                        <input type="tel" name="whatsapp" required 
                               class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="(00) 00000-0000">
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">E-mail</label>
                        <input type="email" name="email" required 
                               class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="exemplo@email.com">
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Senha</label>
                        <input type="password" name="senha" required 
                               class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="********">
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Confirmar Senha</label>
                        <input type="password" name="confirmar_senha" required 
                               class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="********">
                    </div>

                    <button type="submit" 
                            class="w-full bg-[#00a650] text-white font-black py-5 rounded-2xl shadow-xl hover:bg-[#009647] transition-all transform hover:-translate-y-1 uppercase tracking-widest text-[11px] mt-4">
                        Criar Minha Conta
                    </button>
                    
                    <div id="responseMsg" class="text-center text-xs font-bold transition-all h-4"></div>
                </form>
            </div>

            <p class="text-center text-gray-400 text-[10px] mt-8 font-black uppercase tracking-widest">
                Já tem uma conta? <a href="login.php" class="text-green-600 hover:text-green-700 underline">Fazer Login</a>
            </p>
        </div>
    </main>

    <footer class="p-8 text-center bg-white/30">
        <p class="text-[9px] font-black text-gray-300 uppercase tracking-[0.3em]">© 2026 $UPER$ORTE - Tecnologia para Sorteios</p>
    </footer>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const msg = document.getElementById('responseMsg');
            const formData = new FormData(e.target);

            btn.disabled = true;
            btn.innerHTML = 'Processando...';
            msg.className = 'text-center text-xs font-bold text-gray-400 tracking-widest uppercase';
            msg.innerText = 'CRIANDO SEU ACESSO...';

            try {
                const response = await fetch('backend/api/registrar_criador.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    msg.className = 'text-center text-xs font-bold text-green-500 tracking-widest uppercase';
                    msg.innerText = 'CONTA CRIADA! REDIRECIONANDO...';
                    setTimeout(() => window.location.href = 'login.php', 2000);
                } else {
                    msg.className = 'text-center text-xs font-bold text-red-500 tracking-widest uppercase';
                    msg.innerText = data.error || 'Ocorreu um erro.';
                    btn.disabled = false;
                    btn.innerHTML = 'Criar Minha Conta';
                }
            } catch (error) {
                msg.className = 'text-center text-xs font-bold text-red-500 tracking-widest uppercase';
                msg.innerText = 'ERRO DE CONEXÃO.';
                btn.disabled = false;
                btn.innerHTML = 'Criar Minha Conta';
            }
        });
    </script>
</body>
</html>
