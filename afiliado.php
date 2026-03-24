<?php
require_once 'backend/config.php';
try {
    $stmtM = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'modo_manutencao'");
    if ($stmtM->fetchColumn() === '1') {
        header('Location: manutencao.php');
        exit;
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Afiliado - $UPER$ORTE</title>
    
    <!-- Frameworks -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

    <!-- PWA -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6d28d9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="afiliado-app.png">

    <!-- TAGS DE COMPARTILHAMENTO -->
    <meta property="og:title" content="Painel de Afiliados - $UPER$ORTE">
    <meta property="og:description" content="Acesse seu painel, acompanhe suas vendas e gere seus links de divulgação.">
    <meta property="og:image" content="frontend/png/cifrao_premium.png">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .gradient-text {
            background: linear-gradient(135deg, #8e44ad, #2c3e50);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }
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
                <span id="session-timer"
                    class="hidden text-[10px] font-black text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">EXPIRA
                    EM: 05:00</span>
                <button id="btn-logout"
                    class="hidden text-xs font-bold text-red-500 uppercase tracking-widest hover:bg-red-50 px-3 py-1.5 rounded-lg transition-all">Sair</button>
            </div>
        </div>
    </header>

    <main class="max-w-xl mx-auto p-4 py-10">

        <!-- Login / Registro Layout -->
        <div id="section-auth" class="glass rounded-[2rem] p-8 shadow-2xl border border-white">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-gray-800 uppercase">Seja um Parceiro</h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Gere renda extra com segurança
                </p>
            </div>

            <form id="form-auth" class="space-y-4">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">WhatsApp (Somente
                        números)</label>
                    <input type="text" id="auth-whatsapp" placeholder="(11) 99999-9999"
                        class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                </div>

                <div id="login-fields" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Sua Senha</label>
                        <input type="password" id="auth-senha" placeholder="******"
                            class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                    </div>
                </div>

                <div id="extra-fields" class="hidden space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Nome
                            Completo</label>
                        <input type="text" id="auth-nome" placeholder="Seu nome"
                            class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Seu Melhor
                            Email</label>
                        <input type="email" id="auth-email" placeholder="email@exemplo.com"
                            class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-1 flex items-center gap-1 mb-1">
                            Chave PIX (Para receber)
                            <span onclick="showPixHelp()" class="inline-flex items-center justify-center w-3 h-3 bg-gray-200 text-gray-500 rounded-full text-[8px] cursor-help hover:bg-purple-100 hover:text-purple-600 transition-all font-bold">?</span>
                        </label>
                        <input type="text" id="auth-pix" placeholder="CPF, Email, Celular ou Aleatória"
                            class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                    </div>
                </div>

                <button type="submit" id="btn-auth-submit"
                    class="w-full bg-[#2c3e50] text-white font-black py-5 rounded-2xl shadow-xl hover:bg-gray-800 transition-all uppercase tracking-widest text-sm">Entrar
                    / Cadastrar</button>

                <div class="text-center flex flex-col gap-3">
                    <button type="button" id="btn-forgot"
                        class="text-[10px] font-black text-purple-600 uppercase tracking-widest hover:underline">Esqueci
                        minha senha</button>
                    <a href="index.html"
                        class="text-center text-[11px] text-gray-400 underline hover:text-gray-600">Voltar para a
                        Loja</a>
                </div>
            </form>
        </div>

        <!-- Token Handling Layout (Reset Password / Confirm Change) -->
        <div id="section-token" class="hidden glass rounded-[2rem] p-8 shadow-2xl border border-white text-center">
            <h2 id="token-title" class="text-xl font-black text-gray-800 uppercase mb-4">Confirmar Operação</h2>
            <div id="token-input-cont" class="hidden mb-6">
                <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Nova Senha</label>
                <input type="password" id="token-valor" placeholder="No mínimo 6 caracteres"
                    class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-semibold focus:ring-2 focus:ring-purple-500 outline-none transition-all">
            </div>
            <p id="token-desc" class="text-sm text-gray-500 mb-6 font-medium">Clique no botão abaixo para concluir.</p>
            <button id="btn-execute-token"
                class="w-full bg-[#00a650] text-white font-black py-5 rounded-2xl shadow-xl hover:bg-[#009647] transition-all uppercase tracking-widest text-sm">Confirmar
                e Salvar</button>
        </div>

        <!-- Dashboard Layout -->
        <div id="section-dash" class="hidden space-y-6">

            <!-- PWA INSTALL BANNER -->
            <div id="pwa-install-container" class="hidden bg-gradient-to-r from-gray-900 to-indigo-900 rounded-[2rem] p-8 text-white shadow-2xl relative overflow-hidden border border-white/5">
                <div class="absolute right-0 top-0 opacity-10 transform scale-150 rotate-12 -mr-8 -mt-8">
                     <img src="afiliado-app.png" class="w-40 grayscale brightness-200">
                </div>
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
                    <div>
                        <h3 class="text-xl font-black uppercase italic tracking-tight">Aplicativo do Afiliado</h3>
                        <p class="text-[11px] opacity-60 font-medium uppercase tracking-widest mt-1">Instale nosso atalho na sua tela inicial e tenha acesso instantâneo ao seu painel.</p>
                    </div>
                    <button id="btn-pwa-install" class="bg-white text-gray-900 font-black px-8 py-4 rounded-2xl text-[10px] uppercase tracking-widest shadow-xl hover:bg-gray-100 transition-all active:scale-95 whitespace-nowrap">Instalar Agora</button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col items-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Saldo Atual</p>
                    <h3 class="text-2xl font-black text-purple-600 mb-2" id="dash-saldo">R$ 0,00</h3>
                    
                    <div class="w-full bg-gray-100 h-1.5 rounded-full mb-3 overflow-hidden">
                        <div id="payout-progress" class="bg-purple-600 h-full transition-all duration-1000" style="width: 0%"></div>
                    </div>
                    
                    <button id="btn-request-payout" onclick="requestPayout()" 
                        class="w-full bg-purple-600 text-white text-[10px] font-black py-2.5 rounded-xl uppercase tracking-widest disabled:opacity-30 disabled:cursor-not-allowed transition-all hover:bg-purple-700">
                        Solicitar Saque
                    </button>
                    
                    <p class="text-[8px] text-gray-400 font-bold mt-2 uppercase text-center" id="dash-proximo-pgto">VERIFICANDO CICLO...</p>
                </div>
                
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col items-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Ganhos do Ciclo</p>
                    <h3 class="text-2xl font-black text-green-600 mb-2" id="dash-total">R$ 0,00</h3>
                    <p class="text-[9px] text-gray-400 font-bold mb-3 uppercase" id="dash-vendas">0 VENDAS PAGAS</p>
                    
                    <button onclick="showPayouts()" 
                        class="w-full bg-gray-100 text-gray-600 text-[10px] font-black py-2.5 rounded-xl uppercase tracking-widest hover:bg-gray-200 transition-all border border-gray-200">
                        Ver Extrato
                    </button>
                </div>
            </div>

            <!-- Seção Rifa Grátis -->
            <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
                
                <h3 class="text-sm font-black text-gray-800 uppercase mb-4 flex items-center gap-2 relative z-10">
                    <span class="w-5 h-5 bg-purple-100 text-purple-600 rounded flex items-center justify-center">🎁</span>
                    Meta: Rifa Grátis
                </h3>

                <div class="space-y-4 relative z-10">
                    <div class="flex justify-between items-end mb-1">
                        <div>
                            <p class="text-[9px] font-black text-gray-400 uppercase">Seu Progresso</p>
                            <h4 class="text-xs font-bold text-gray-700" id="bonus-counter-text">0 de 7 vendas para o bônus</h4>
                        </div>
                        <div class="text-right">
                            <span id="bonus-status-badge" class="text-[8px] font-black px-2 py-1 rounded-md uppercase tracking-widest bg-gray-100 text-gray-400">DISPONÍVEL EM BREVE</span>
                        </div>
                    </div>

                    <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden border border-gray-50 p-0.5">
                        <div id="bonus-progress-bar" class="bg-gradient-to-r from-purple-500 to-indigo-600 h-full rounded-full transition-all duration-1000 shadow-sm" style="width: 0%"></div>
                    </div>

                    <div id="bonus-cycle-info" class="hidden bg-blue-50 border border-blue-100 rounded-2xl p-4">
                        <p class="text-[10px] text-blue-600 font-bold uppercase tracking-tight flex items-center gap-2">
                             ⏳ Ciclo Ativo: Bônus resgatado! Reinício em: <span id="bonus-timer" class="font-black">...</span>
                        </p>
                    </div>

                    <button id="btn-redeem-bonus" onclick="openRedeemModal()" disabled 
                        class="w-full bg-[#00a650] text-white font-black py-4 rounded-2xl shadow-xl shadow-green-100 hover:bg-[#009647] transition-all uppercase tracking-widest text-xs disabled:opacity-30 disabled:grayscale disabled:cursor-not-allowed">
                        RESGATAR RIFA GRÁTIS
                    </button>
                    
                    <p id="bonus-block-info" class="hidden text-center text-[9px] font-black text-red-500 uppercase tracking-widest">⚠️ Resgates bloqueados por inatividade</p>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                <h3 class="text-sm font-black text-gray-800 uppercase mb-4 flex items-center gap-2">
                    <span
                        class="w-5 h-5 bg-purple-100 text-purple-600 rounded flex items-center justify-center">🔗</span>
                    Meus Links de Divulgação
                </h3>
                <div id="links-container" class="space-y-4"></div>
            </div>

            <div class="bg-gray-900 rounded-[2rem] p-8 text-white space-y-6">
                <div>
                    <h3 class="text-sm font-black uppercase mb-4 opacity-50">Configurações de Segurança</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-[9px] font-black uppercase tracking-widest opacity-40 ml-1 flex items-center gap-1">
                                Chave PIX
                                <span onclick="showPixHelp()" class="inline-flex items-center justify-center w-3 h-3 bg-white/20 text-white rounded-full text-[8px] cursor-help hover:bg-purple-500 transition-all font-bold">?</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text" id="dash-pix-key"
                                    class="flex-1 bg-white/10 rounded-xl p-4 text-xs font-mono outline-none border border-white/5">
                                <button onclick="requestUpdate('pix')"
                                    class="bg-purple-600 font-black px-4 rounded-xl text-[10px] uppercase hover:bg-purple-700 transition-all">Alterar</button>
                            </div>
                        </div>
                        <div>
                            <label class="text-[9px] font-black uppercase tracking-widest opacity-40 ml-1">Email</label>
                            <div class="flex gap-2">
                                <input type="email" id="dash-email"
                                    class="flex-1 bg-white/10 rounded-xl p-4 text-xs font-mono outline-none border border-white/5">
                                <button onclick="requestUpdate('email')"
                                    class="bg-purple-600 font-black px-4 rounded-xl text-[10px] uppercase hover:bg-purple-700 transition-all">Alterar</button>
                            </div>
                        </div>
                    </div>
                    <p class="text-[10px] opacity-40 mt-4 font-medium uppercase tracking-widest text-center">Pagamentos
                        realizados a cada 15 dias para saldos acima de R$ 20,00</p>
                    <p class="text-[9px] opacity-30 mt-6 font-medium uppercase tracking-widest text-center">Alterações
                        de PIX ou Email exigem confirmação via link enviado ao seu email por segurança.</p>
                </div>
            </div>

            <!-- Seção Regras -->
            <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                <h3 class="text-sm font-black text-gray-800 uppercase mb-6 flex items-center gap-2">
                    <span class="w-5 h-5 bg-orange-100 text-orange-600 rounded flex items-center justify-center">📜</span>
                    Regras de Bonificação
                </h3>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-6 h-6 bg-purple-50 text-purple-600 rounded-lg flex-shrink-0 flex items-center justify-center text-[10px] font-black">01</div>
                        <p class="text-[11px] font-bold text-gray-500 uppercase leading-relaxed">Venda 7 rifas pagas e ganhe o direito de escolher 1 número grátis na rifa ativa.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-6 h-6 bg-purple-50 text-purple-600 rounded-lg flex-shrink-0 flex items-center justify-center text-[10px] font-black">02</div>
                        <p class="text-[11px] font-bold text-gray-500 uppercase leading-relaxed">Após o resgate, inicia-se um ciclo de 30 dias. Durante esse período, as vendas não acumulam novo bônus.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-6 h-6 bg-red-50 text-red-600 rounded-lg flex-shrink-0 flex items-center justify-center text-[10px] font-black">03</div>
                        <p class="text-[11px] font-bold text-gray-500 uppercase leading-relaxed">INATIVIDADE: Venda mínimo 1 rifa a cada 2 concursos para evitar bloqueio de 15 dias. Caso fique 4 concursos sem vendas, o acesso de afiliado será desativado permanentemente.</p>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- Modal Extrato de Pagamentos -->
    <div id="modal-payouts" class="fixed inset-0 bg-black/80 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-[2rem] p-8 max-w-md w-full shadow-2xl relative border border-gray-100 max-h-[85vh] flex flex-col">
            <h2 class="text-xl font-black text-[#2c3e50] mb-6 uppercase tracking-tight italic text-center">EXTRATO DE PAGAMENTOS</h2>
            
            <div id="payouts-list" class="flex-1 overflow-y-auto space-y-3 pr-2 custom-scrollbar">
                <!-- Populado via JS -->
                <p class="text-center text-xs text-gray-400 py-10">Carregando histórico...</p>
            </div>

            <button onclick="document.getElementById('modal-payouts').classList.add('hidden')"
                class="w-full bg-gray-900 text-white font-black py-4 rounded-2xl shadow-lg uppercase text-xs tracking-widest hover:bg-black transition-all mt-6">Fechar</button>
        </div>
    </div>

    <!-- Modal Notificação Premium -->
    <div id="modal-notif"
        class="fixed inset-0 bg-black/80 z-[200] hidden flex items-center justify-center p-4 backdrop-blur-md opacity-0 transition-opacity duration-300">
        <div id="notif-box" class="bg-white rounded-[2.5rem] p-10 max-w-sm w-full text-center shadow-2xl relative border-t-8 border-purple-500 transform scale-90 transition-transform duration-300">
            <div id="notif-icon-container" class="w-20 h-20 bg-purple-50 text-purple-600 rounded-full mx-auto flex items-center justify-center mb-6 shadow-inner">
                <!-- Icon Error -->
                <svg id="icon-error" class="w-10 h-10 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <!-- Icon Success -->
                <svg id="icon-success" class="w-10 h-10 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <!-- Icon Info -->
                <svg id="icon-info" class="w-10 h-10 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h2 id="notif-title" class="text-2xl font-black text-gray-800 mb-2 uppercase tracking-tight italic"></h2>
            <p id="notif-message" class="text-[11px] font-bold text-gray-400 uppercase mb-8 leading-relaxed px-4"></p>
            <button onclick="closeNotif()"
                class="w-full bg-purple-600 text-white font-black py-5 rounded-2xl shadow-xl shadow-purple-200 uppercase text-xs tracking-widest hover:bg-purple-700 transition-all active:scale-95">ENTENDIDO</button>
        </div>
    </div>

    <!-- Modal Resgate Bônus -->
    <div id="modal-redeem" class="fixed inset-0 bg-black/80 z-[150] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-sm w-full text-center shadow-2xl relative border-t-8 border-green-500">
            <h2 class="text-2xl font-black text-gray-800 mb-6 uppercase tracking-tight italic">Resgatar Número</h2>
            
            <form id="form-redeem" onsubmit="event.preventDefault(); redeemBonus();" class="space-y-6 text-left">
                <div>
                   <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Rifa para Resgate</label>
                   <select id="redeem-rifa" name="rifa_id" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-bold outline-none"></select>
                </div>
                <div>
                   <label class="text-[10px] font-black text-gray-400 uppercase block mb-1">Escolha seu Número</label>
                   <input type="number" id="redeem-numero" name="numero" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4 text-sm font-bold outline-none" placeholder="Ex: 00">
                </div>
                
                <button type="submit" id="btn-confirm-redeem"
                    class="w-full bg-[#00a650] text-white font-black py-5 rounded-2xl shadow-xl shadow-green-100 uppercase text-xs tracking-widest hover:bg-[#009647] transition-all">CONCLUIR RESGATE</button>
                <button type="button" onclick="document.getElementById('modal-redeem').classList.add('hidden')"
                    class="w-full text-xs font-bold text-gray-400 uppercase tracking-widest py-2 hover:text-gray-600">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal Alerta Bloqueio -->
    <div id="modal-bloqueio" class="fixed inset-0 bg-black/80 z-[300] hidden flex items-center justify-center p-4 backdrop-blur-md transition-opacity duration-300">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-sm w-full text-center shadow-2xl relative border-t-8 border-red-500 transform scale-100">
            <div class="w-20 h-20 bg-red-50 text-red-600 rounded-full mx-auto flex items-center justify-center mb-6 shadow-inner">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h2 class="text-2xl font-black text-gray-800 mb-2 uppercase tracking-tight italic">BLOQUEIO ADICIONAL</h2>
            <p class="text-[11px] font-bold text-gray-400 uppercase mb-8 leading-relaxed px-4">Você ficou 2 concursos sem realizar vendas. Por isso, após o término do seu ciclo atual, será aplicado um bloqueio adicional de 15 dias.</p>
            <button onclick="confirmBloqueioNotif()"
                class="w-full bg-red-600 text-white font-black py-5 rounded-2xl shadow-xl shadow-red-200 uppercase text-xs tracking-widest hover:bg-red-700 transition-all">ENTENDIDO</button>
        </div>
    </div>

    <script>
        const API = 'backend/api/afiliado.php';

        // PWA SERVICE WORKER
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('service-worker.js').catch(() => {});
        }

        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            // Only show install banner if not already installed and on the dashboard
            if (document.getElementById('section-dash').classList.contains('hidden') === false) {
                 document.getElementById('pwa-install-container').classList.remove('hidden');
            }
        });

        document.getElementById('btn-pwa-install')?.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    document.getElementById('pwa-install-container').classList.add('hidden');
                }
                deferredPrompt = null;
            }
        });

        let currentToken = '';
        let timerInterval = null;
        let secondsLeft = 0;
        let whatsappTemplate = '';

        // Máscara para WhatsApp (11) 99999-9999
        document.getElementById('auth-whatsapp')?.addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });

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
            if (data.afiliado) {
                secondsLeft = parseInt(data.expires_in) || 300;
                startTimer();
                showDash(data);
                
                if (data.afiliado.bonus_notificado_bloqueio == 1) {
                    document.getElementById('modal-bloqueio').classList.remove('hidden');
                }
            } else {
                // Not logged or expired
                document.getElementById('section-auth').classList.remove('hidden');
                document.getElementById('section-dash').classList.add('hidden');
                document.getElementById('btn-logout').classList.add('hidden');
                document.getElementById('session-timer').classList.add('hidden');
                if (data.expired) showAlert('Por segurança, sua sessão expirou. Por favor, entre novamente.', 'Sessão expirada');
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
                    document.getElementById('session-timer').textContent = "EXPIRADO!";
                    // Logout imediato e suave
                    setTimeout(() => location.href = 'afiliado.php', 2000);
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

        async function getLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject('Seu navegador não suporta geolocalização exata.');
                } else {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
                        (err) => {
                            if (err.code === 1) reject('A geolocalização exata é obrigatória para parceiros por segurança. Por favor, autorize no seu navegador.');
                            else reject('Erro ao obter localização: ' + err.message);
                        },
                        { enableHighAccuracy: true, timeout: 5000 }
                    );
                }
            });
        }

        document.getElementById('form-auth').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-auth-submit');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="animate-spin mr-2">📍</i> Localizando...';
            btn.disabled = true;

            let coords = { lat: '', lng: '' };
            try {
                coords = await getLocation();
            } catch (err) {
                showAlert(err);
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }

            btn.innerHTML = 'Processando...';
            const fd = new FormData();
            fd.append('action', 'login_register');
            fd.append('whatsapp', document.getElementById('auth-whatsapp').value);
            fd.append('nome', document.getElementById('auth-nome').value);
            fd.append('email', document.getElementById('auth-email').value);
            fd.append('senha', document.getElementById('auth-senha').value);
            fd.append('pix_key', document.getElementById('auth-pix').value);
            fd.append('lat', coords.lat);
            fd.append('lng', coords.lng);

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();

            btn.disabled = false;
            btn.innerHTML = originalText;

            if (data.error) {
                if (data.error.includes('preencha todos os campos')) {
                    document.getElementById('extra-fields').classList.remove('hidden');
                    document.getElementById('login-fields').classList.add('hidden');
                }
                showAlert(data.error);
            } else if (data.challenge_required) {
                showAlert(data.message, 'Segurança');
                btn.innerHTML = 'Aguardando E-mail...';
                btn.disabled = true;
            } else {
                checkSession();
            }
        };

        function showDash(data) {
            document.getElementById('section-auth').classList.add('hidden');
            document.getElementById('section-dash').classList.remove('hidden');
            document.getElementById('btn-logout').classList.remove('hidden');

            whatsappTemplate = data.whatsapp_share_template || '';

            const af = data.afiliado;
            document.getElementById('dash-saldo').textContent = parseFloat(af.saldo).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            // Progress Bar for R$ 20.00
            const saldo = parseFloat(af.saldo);
            const percPayout = Math.min(100, (saldo / 20) * 100);
            document.getElementById('payout-progress').style.width = percPayout + '%';

            document.getElementById('dash-total').textContent = parseFloat(af.total_ganho).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            document.getElementById('dash-vendas').textContent = `${af.vendas_pagas} VENDAS PAGAS`;
            document.getElementById('dash-pix-key').value = af.pix_key;
            document.getElementById('dash-email').value = af.email;
            
            // Botão de Saque Logic
            const btnPay = document.getElementById('btn-request-payout');
            const ultimaData = af.data_ultimo_saque ? new Date(af.data_ultimo_saque) : null;
            const hoje = new Date();
            let diasParaProximo = 0;
            
            if (ultimaData) {
                ultimaData.setHours(0,0,0,0);
                const proximoSaque = new Date(ultimaData);
                proximoSaque.setDate(proximoSaque.getDate() + 15);
                diasParaProximo = Math.ceil((proximoSaque - hoje) / (1000 * 60 * 60 * 24));
            }

            if (saldo < 20) {
                btnPay.disabled = true;
                btnPay.textContent = 'Mínimo R$ 20';
                document.getElementById('dash-proximo-pgto').textContent = "CUMPRIR META PARA SACAR";
            } else if (diasParaProximo > 0) {
                btnPay.disabled = true;
                btnPay.textContent = 'Aguardar Ciclo';
                document.getElementById('dash-proximo-pgto').textContent = `PRÓXIMO CICLO EM ${diasParaProximo} DIAS`;
            } else {
                btnPay.disabled = false;
                btnPay.textContent = 'Solicitar Saque';
                document.getElementById('dash-proximo-pgto').textContent = "PAGAMENTO DISPONÍVEL! 🚀";
            }

            const cont = document.getElementById('links-container');
            cont.innerHTML = '';
            rifasData = {};

            data.rifas.forEach(r => {
                rifasData[r.id] = r;
                const link = `${data.site_url}/rifa.php?id=${r.id}&ref=${af.id}`;
                
                const item = `
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <p class="text-[10px] font-black text-purple-600 uppercase mb-1">${r.nome}</p>
                        <div class="flex gap-2">
                            <input type="text" readonly value="${link}" class="flex-1 bg-white border border-gray-100 rounded-xl p-3 text-[11px] font-mono outline-none text-gray-400">
                            <button onclick="copyToClipboard('${link}')" class="bg-gray-800 text-white text-[10px] font-black px-4 rounded-xl hover:bg-black transition-all uppercase tracking-widest">COPIAR</button>
                            <button onclick="shareWAById(${r.id}, '${link}')" class="bg-[#25D366] text-white p-3 rounded-xl hover:bg-[#128C7E] transition-all flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            </button>
                        </div>
                    </div>
                `;
                cont.insertAdjacentHTML('beforeend', item);
            });

            // --- Lógica de Bônus Rifa Grátis ---
            const btnRedeem = document.getElementById('btn-redeem-bonus');
            const bonusVendas = parseInt(af.bonus_vendas) || 0;
            const resgateData = af.bonus_data_resgate ? new Date(af.bonus_data_resgate) : null;
            const bloqueioAte = af.bonus_bloqueio_ate ? new Date(af.bonus_bloqueio_ate) : null;
            const agora = new Date();

            const isBlocked = bloqueioAte && bloqueioAte > agora;
            const isInCycle = resgateData && new Date(resgateData.getTime() + (30 * 24 * 60 * 60 * 1000)) > agora;

            // Update Progress
            const bonusPerc = Math.min(100, (bonusVendas / 7) * 100);
            document.getElementById('bonus-progress-bar').style.width = bonusPerc + '%';
            document.getElementById('bonus-counter-text').textContent = `${bonusVendas} de 7 vendas para o bônus`;

            const badge = document.getElementById('bonus-status-badge');
            const cycleInfo = document.getElementById('bonus-cycle-info');
            const blockInfo = document.getElementById('bonus-block-info');

            badge.className = 'text-[8px] font-black px-2 py-1 rounded-md uppercase tracking-widest ';
            cycleInfo.classList.add('hidden');
            blockInfo.classList.add('hidden');

            if (isBlocked) {
                btnRedeem.disabled = true;
                btnRedeem.textContent = 'BÔNUS BLOQUEADO';
                badge.innerText = 'BLOQUEADO';
                badge.classList.add('bg-red-100', 'text-red-500');
                blockInfo.classList.remove('hidden');
            } else if (isInCycle) {
                btnRedeem.disabled = true;
                btnRedeem.textContent = 'RESGATE REALIZADO';
                badge.innerText = 'CICLO ATIVO';
                badge.classList.add('bg-blue-100', 'text-blue-500');
                cycleInfo.classList.remove('hidden');
                
                // Timer Ciclo
                const end = new Date(resgateData.getTime() + (30 * 24 * 60 * 60 * 1000));
                const diff = Math.ceil((end - agora) / (1000 * 60 * 60 * 24));
                document.getElementById('bonus-timer').textContent = `${diff} DIAS`;
            } else if (bonusVendas >= 7) {
                btnRedeem.disabled = false;
                btnRedeem.textContent = 'RESGATAR AGORA! 🎁';
                badge.innerText = 'DISPONÍVEL';
                badge.classList.add('bg-green-100', 'text-green-500');
            } else {
                btnRedeem.disabled = true;
                btnRedeem.textContent = 'CONTINUE VENDENDO';
                badge.innerText = 'EM PROGRESSO';
                badge.classList.add('bg-gray-100', 'text-gray-400');
            }
        }

        async function openRedeemModal() {
            const select = document.getElementById('redeem-rifa');
            select.innerHTML = '';
            
            // Popula com as rifas que vieram no get_stats (rifasData)
            Object.values(rifasData).forEach(r => {
                select.innerHTML += `<option value="${r.id}">${r.nome}</option>`;
            });

            if(select.options.length === 0) {
                showAlert('Não há rifas abertas disponíveis no momento.', 'Atenção');
                return;
            }

            document.getElementById('modal-redeem').classList.remove('hidden');
        }

        async function redeemBonus() {
            const btn = document.getElementById('btn-confirm-redeem');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'RESERVANDO...';

            const fd = new FormData(document.getElementById('form-redeem'));
            fd.append('action', 'redeem_bonus');

            try {
                const res = await fetch(API, { method: 'POST', body: fd });
                const data = await res.json();
                if(data.success) {
                    document.getElementById('modal-redeem').classList.add('hidden');
                    showAlert(data.message, 'Sucesso');
                    setTimeout(() => location.reload(), 3000);
                } else {
                    showAlert(data.error);
                }
            } catch(e) {
                showAlert('Erro ao processar resgate. Tente novamente.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        async function confirmBloqueioNotif() {
            await fetch(`${API}?action=confirm_notif_bloqueio`);
            document.getElementById('modal-bloqueio').classList.add('hidden');
        }

        function shareWAById(id, link) {
            const r = rifasData[id];
            if(!r) return;
            const premios = [r.premio1 || "", r.premio2 || "", r.premio3 || "", r.premio4 || "", r.premio5 || ""];
            shareWA(link, r.nome, r.preco_numero, premios);
        }

        function shareWA(link, rifaNome, preco, premios) {
            let template = whatsappTemplate || "🎉 Participe da Rifa: {rifa_nome}\n\nConcorra agora: {link}";

            // Replace basic info
            let finalMsg = template
                .replace(/{rifa_nome}/g, rifaNome)
                .replace(/{link}/g, link)
                .replace(/{preco}/g, parseFloat(preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));

            // Replace prizes (and remove entire lines if prize is empty)
            for (let i = 1; i <= 5; i++) {
                const val = premios[i - 1];
                const placeholder = `{premio${i}}`;
                
                if (val && val.trim() !== '') {
                    finalMsg = finalMsg.replace(new RegExp(placeholder, 'g'), val.trim());
                } else {
                    // Remove line containing the placeholder
                    const regex = new RegExp(`^.*${placeholder}.*(\r?\n)?`, 'gm');
                    finalMsg = finalMsg.replace(regex, '');
                }
            }

            const msg = encodeURIComponent(finalMsg);
            window.open(`https://api.whatsapp.com/send?text=${msg}`, '_blank');
        }

        async function requestUpdate(tipo) {
            const val = document.getElementById(tipo === 'pix' ? 'dash-pix-key' : 'dash-email').value;
            const fd = new FormData();
            fd.append('action', 'request_update');
            fd.append('tipo', tipo);
            fd.append('valor', val);

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) showAlert(data.message, 'Sucesso');
            else showAlert(data.error);
        }

        let rifasData = {};
        document.getElementById('btn-logout').onclick = async () => {
            await fetch(`${API}?action=logout`);
            location.reload();
        };

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            showAlert('Link copiado para a área de transferência!');
        }

        async function requestPayout() {
            const btn = document.getElementById('btn-request-payout');
            btn.disabled = true;
            btn.textContent = 'SOLICITANDO...';
            
            const fd = new FormData();
            fd.append('action', 'request_payout');
            
            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            
            if (data.success) {
                showAlert(data.message, 'Sucesso');
                checkSession(); // Refresh dash
            } else {
                showAlert(data.error);
                btn.disabled = false;
                btn.textContent = 'Solicitar Saque';
            }
        }

        async function showPayouts() {
            const list = document.getElementById('payouts-list');
            list.innerHTML = '<p class="text-center text-xs text-gray-400 py-10">Carregando histórico...</p>';
            document.getElementById('modal-payouts').classList.remove('hidden');
            
            const res = await fetch(`${API}?action=get_payouts`);
            const data = await res.json();
            
            list.innerHTML = '';
            if (!data.success || data.payouts.length === 0) {
                list.innerHTML = '<p class="text-center text-xs text-gray-400 py-10">Você ainda não possui saques solicitados.</p>';
                return;
            }
            
            data.payouts.forEach(p => {
                const isPaid = p.status === 'pago';
                const statusColor = isPaid ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700';
                const statusIcon = isPaid ? '✓' : '⌛';
                const statusText = isPaid ? 'PAGO' : 'PENDENTE';
                
                list.insertAdjacentHTML('beforeend', `
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex justify-between items-center">
                        <div>
                            <p class="text-xs font-black text-gray-800">R$ ${parseFloat(p.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
                            <p class="text-[10px] text-gray-400 tracking-widest uppercase mt-1">${new Date(p.data_solicitacao).toLocaleDateString('pt-BR')}</p>
                        </div>
                        <div class="${statusColor} text-[9px] font-black px-3 py-1.5 rounded-lg uppercase tracking-widest flex items-center gap-1 shadow-sm">
                            ${statusIcon} ${statusText}
                        </div>
                    </div>
                `);
            });
        }

        function showPixHelp() {
            showAlert('Para chaves de telefone, use o formato internacional: +55 seguidos do DDD e o número (Ex: +5527999881122). Para CPF ou E-mail, basta digitar normalmente.', 'Instrução do PIX');
        }

        function showAlert(msg, title = 'Aviso', type = 'info') {
            const m = document.getElementById('modal-notif');
            const box = document.getElementById('notif-box');
            const iconCont = document.getElementById('notif-icon-container');
            
            // Set Title & Msg
            document.getElementById('notif-title').textContent = title;
            document.getElementById('notif-message').textContent = msg;

            // Reset Icons
            document.getElementById('icon-error').classList.add('hidden');
            document.getElementById('icon-success').classList.add('hidden');
            document.getElementById('icon-info').classList.add('hidden');

            // Theme based on type
            const lowMsg = msg.toLowerCase();
            const lowTitle = title.toLowerCase();

            if (type === 'error' || lowTitle.includes('erro') || lowMsg.includes('erro') || lowMsg.includes('limite') || lowTitle.includes('atenção') || lowMsg.includes('obrigatória')) {
                document.getElementById('icon-error').classList.remove('hidden');
                box.style.borderTopColor = '#ef4444';
                iconCont.className = "w-20 h-20 bg-red-50 text-red-500 rounded-full mx-auto flex items-center justify-center mb-6 shadow-inner";
            } else if (type === 'success' || lowTitle.includes('sucesso') || lowMsg.includes('sucesso')) {
                document.getElementById('icon-success').classList.remove('hidden');
                box.style.borderTopColor = '#10b981';
                iconCont.className = "w-20 h-20 bg-green-50 text-green-500 rounded-full mx-auto flex items-center justify-center mb-6 shadow-inner";
            } else {
                document.getElementById('icon-info').classList.remove('hidden');
                box.style.borderTopColor = '#8e44ad';
                iconCont.className = "w-20 h-20 bg-purple-50 text-purple-600 rounded-full mx-auto flex items-center justify-center mb-6 shadow-inner";
            }

            m.classList.remove('hidden');
            setTimeout(() => {
                m.classList.add('opacity-100');
                box.classList.remove('scale-90');
                box.classList.add('scale-100');
            }, 10);
        }

        function closeNotif() {
            const m = document.getElementById('modal-notif');
            const box = document.getElementById('notif-box');
            m.classList.remove('opacity-100');
            box.classList.add('scale-90');
            box.classList.remove('scale-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        }

        checkSession();
    </script>
</body>

</html>