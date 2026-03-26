<?php
require_once 'backend/config.php';
$protocol = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"));
$site_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua sorte, suas regras! Crie sua Rifa Online - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <link rel="apple-touch-icon" href="frontend/png/cifrao_premium.png">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .gradient-text {
            background: linear-gradient(90deg, #00a650, #2c3e50);
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
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }
        
        /* Custom scrollbar for modal */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #00a650;
            border-radius: 10px;
        }
    </style>
</head>

<body class="bg-[#f8fafc] text-gray-800 antialiased selection:bg-green-100 selection:text-green-900">

    <!-- Navbar -->
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-[100]">
        <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="../index.php" class="text-2xl font-black italic tracking-tighter" style="color: #00a650;">
                $UPER<span style="color: #2c3e50;">$ORTE</span>
            </a>
            <div class="flex items-center gap-6">
                <a href="login.php"
                    class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-800 transition-colors hidden sm:block">Já
                    sou Criador?</a>
                <a href="register.php"
                    class="bg-gray-900 text-white text-[10px] font-black px-6 py-3 rounded-full uppercase tracking-widest shadow-xl hover:bg-black transition-all">Começar</a>
            </div>
        </div>
    </header>

    <main class="relative overflow-hidden">

        <!-- Hero Section -->
        <section class="max-w-6xl mx-auto px-6 pt-20 pb-24 text-center relative z-10">
            <div
                class="inline-block bg-green-50 text-green-700 text-[10px] font-black px-6 py-2 rounded-full uppercase tracking-widest mb-8 border border-green-100 shadow-sm">
                Plataforma de Rifas Online (E-commerce)
            </div>

            <h1 class="text-5xl md:text-7xl font-black tracking-tighter mb-8 leading-[0.9] text-[#2c3e50]">
                Crie sua Rifa em <br>
                <span class="gradient-text">Minutos.</span>
            </h1>

            <p class="text-lg md:text-xl text-gray-500 max-w-2xl mx-auto font-medium mb-12 leading-relaxed">
                Pare de usar sistemas complicados que retêm seu dinheiro. <br>
                Nossa plataforma é direta: **Você cria sua rifa, integra seu PIX e recebe na hora.**
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="register.php" id="comecar"
                    class="w-full sm:w-auto bg-[#00a650] text-white font-black px-12 py-5 rounded-2xl shadow-2xl hover:bg-[#009647] transition-all transform hover:scale-105 uppercase tracking-widest text-xs">
                    Começar Agora
                </a>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-2 sm:mt-0 px-4">Zero taxas
                    sobre suas vendas</p>
            </div>
        </section>

        <!-- Product Preview or Features -->
        <section class="max-w-6xl mx-auto px-6 mb-32">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div
                    class="bg-white rounded-[3rem] shadow-2xl p-4 transform -rotate-2 border border-gray-100 relative overflow-hidden group">
                    <div
                        class="absolute inset-0 bg-gradient-to-tr from-green-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                    </div>
                    <img src="https://images.unsplash.com/photo-1556742044-3c52d6e88c62?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
                        class="w-full h-[400px] object-cover rounded-[2.5rem] shadow-inner" alt="Painel Administrativo">
                </div>

                <div class="space-y-8">
                    <h2
                        class="text-3xl md:text-4xl font-black text-[#2c3e50] tracking-tighter leading-tight uppercase italic">
                        Um painel completo <br> para sua sorte brilhar!
                    </h2>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 bg-green-100 text-[#00a650] rounded-xl flex items-center justify-center font-black shrink-0">
                                1</div>
                            <div>
                                <h4 class="font-black text-gray-800 uppercase text-xs tracking-widest mb-1">Configuração
                                    Express</h4>
                                <p class="text-sm text-gray-500 font-medium">Nome, prêmio, imagem e valor do número.
                                    Tudo intuitivo.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-black shrink-0">
                                2</div>
                            <div>
                                <h4 class="font-black text-gray-800 uppercase text-xs tracking-widest mb-1">Receba no
                                    seu Banco</h4>
                                <p class="text-sm text-gray-500 font-medium">Integração oficial Efí e Mercado Pago. O
                                    dinheiro vai direto pra você.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center font-black shrink-0">
                                3</div>
                            <div>
                                <h4 class="font-black text-gray-800 uppercase text-xs tracking-widest mb-1">Página
                                    Personalizada</h4>
                                <p class="text-sm text-gray-500 font-medium">Você ganha um link exclusivo da sua rifa
                                    para divulgar em suas redes.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="planos" class="bg-gray-900 py-24 sm:py-32 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-b from-[#f8fafc] to-transparent"></div>

            <div class="max-w-6xl mx-auto px-6 relative z-10">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-5xl font-black text-white tracking-tighter uppercase mb-4 italic">Quanto custa iniciar?</h2>
                    <p class="text-gray-400 font-medium max-w-xl mx-auto italic">Pague apenas o valor de ativação proporcional à sua arrecadação. Sem mensalidades, sem taxas extras.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8 items-center">

                    <!-- Basic -->
                    <div class="bg-white/5 border border-white/10 p-8 rounded-[2.5rem] backdrop-blur-xl">
                        <span class="text-[9px] font-black bg-white/10 text-white px-4 py-1.5 rounded-full uppercase tracking-widest">Small Scale</span>
                        <h3 class="text-2xl font-black text-white mt-6 mb-2 text-center uppercase">Campanha Basic</h3>
                        <div class="text-center border-t border-white/5 pt-6 mt-6">
                            <p class="text-[10px] uppercase font-black text-gray-400 tracking-widest mb-1 italic">Para quem está começando</p>
                            <span class="text-xl font-black text-green-400">Arrecadação até R$ 5k</span>
                        </div>
                        <ul class="space-y-4 my-10">
                            <li class="flex items-center gap-3 text-sm font-medium text-white/70 italic border-l-2 border-green-500 pl-4">
                                Taxa Única por Ativação
                            </li>
                            <li class="flex items-center gap-3 text-sm font-medium text-white/70 italic border-l-2 border-green-500 pl-4">
                                100% dos Recebimentos Diretos
                            </li>
                            <li class="flex items-center gap-3 text-sm font-medium text-white/70 italic border-l-2 border-green-500 pl-4">
                                Suporte via E-mail
                            </li>
                        </ul>
                        <a href="register.php" class="block w-full text-center bg-white text-gray-900 font-black py-4 rounded-xl text-[10px] uppercase tracking-widest hover:bg-gray-200 transition-all">Solicitar Ativação</a>
                    </div>

                    <!-- Pro (Featured) -->
                    <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl relative overflow-hidden transform md:scale-110 z-20">
                        <div class="absolute -top-1 -right-1 bg-[#00a650] text-white text-[9px] font-black px-8 py-3 rounded-bl-3xl uppercase tracking-widest animate-pulse">Recomendado</div>
                        <span class="text-[9px] font-black bg-green-50 text-green-700 px-4 py-1.5 rounded-full uppercase tracking-widest">Profissional</span>
                        <h3 class="text-2xl font-black text-[#2c3e50] mt-6 mb-2 text-center uppercase">Campanha Pro</h3>
                        <div class="text-center border-t border-gray-100 pt-6 mt-6">
                            <p class="text-[10px] uppercase font-black text-[#2c3e50] tracking-widest mb-1 italic">Mais popular entre criadores</p>
                            <span class="text-2xl font-black text-[#00a650]">Arrecadação até R$ 20k</span>
                        </div>
                        <ul class="space-y-4 my-10">
                            <li class="flex items-center gap-3 text-sm font-black text-gray-700 italic border-l-2 border-[#00a650] pl-4">
                                Taxa Única (Consulte)
                            </li>
                            <li class="flex items-center gap-3 text-sm font-black text-gray-700 italic border-l-2 border-[#00a650] pl-4">
                                Sistema de Afiliados Completo
                            </li>
                            <li class="flex items-center gap-3 text-sm font-black text-gray-700 italic border-l-2 border-[#00a650] pl-4">
                                Suporte Prioritário WhatsApp
                            </li>
                        </ul>
                        <a href="register.php" class="block w-full text-center bg-[#00a650] text-white font-black py-5 rounded-xl text-[10px] uppercase tracking-widest shadow-xl hover:bg-[#009247] transition-all">Começar Agora</a>
                    </div>

                    <!-- Enterprise -->
                    <div class="bg-white/5 border border-white/10 p-8 rounded-[2.5rem] backdrop-blur-xl">
                        <span class="text-[9px] font-black bg-white/10 text-white px-4 py-1.5 rounded-full uppercase tracking-widest">Expert</span>
                        <h3 class="text-2xl font-black text-white mt-6 mb-2 text-center uppercase">Expert Elite</h3>
                        <div class="text-center border-t border-white/5 pt-6 mt-6">
                            <p class="text-[10px] uppercase font-black text-gray-400 tracking-widest mb-1 italic">Para grandes arrecadações</p>
                            <span class="text-xl font-black text-green-400">Arrecadação Alta</span>
                        </div>
                        <ul class="space-y-4 my-10">
                            <li class="flex items-center gap-3 text-sm font-medium text-white/70 italic border-l-2 border-green-500 pl-4">
                                Melhores Taxas do Mercado
                            </li>
                            <li class="flex items-center gap-3 text-sm font-medium text-white/70 italic border-l-2 border-green-500 pl-4">
                                Gestão Estratégica
                            </li>
                            <li class="flex items-center gap-3 text-sm font-medium text-white/70 italic border-l-2 border-green-500 pl-4">
                                Servidor Dedicado
                            </li>
                        </ul>
                        <a href="register.php" class="block w-full text-center bg-white/10 text-white font-black py-4 rounded-xl text-[10px] uppercase tracking-widest hover:bg-white/20 transition-all border border-white/10 mb-4">Solicitar Ativação</a>
                        <p class="text-center text-[8px] font-black text-white/40 uppercase tracking-widest cursor-pointer hover:text-white" onclick="openModal('taxaModal')">Ver Tabela Completa</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal Tabela de Taxas -->
        <div id="taxaModal" class="fixed inset-0 z-[200] hidden">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeModal('taxaModal')"></div>
            <div class="relative min-h-screen flex items-center justify-center p-6 pointer-events-none">
                <div class="bg-white w-full max-w-lg rounded-[3rem] shadow-2xl pointer-events-auto relative overflow-hidden flex flex-col">
                    <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                        <h3 class="text-xl font-black text-[#2c3e50] uppercase tracking-tighter italic">Tabela de Taxas</h3>
                        <button onclick="closeModal('taxaModal')" class="text-gray-400 hover:text-gray-800 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-8 space-y-4">
                        <div class="overflow-hidden rounded-2xl border border-gray-100">
                            <table class="w-full text-left text-xs font-medium">
                                <thead class="bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-4 font-black uppercase tracking-widest text-gray-400">Arrecadação (Meta)</th>
                                        <th class="px-6 py-4 font-black uppercase tracking-widest text-gray-400">Taxa de Ativação</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-[10px]">
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 100</td><td class="px-6 py-2 font-black text-gray-800">R$ 7,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 200</td><td class="px-6 py-2 font-black text-gray-800">R$ 17,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 400</td><td class="px-6 py-2 font-black text-gray-800">R$ 27,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 701</td><td class="px-6 py-2 font-black text-gray-800">R$ 37,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 1.000</td><td class="px-6 py-2 font-black text-gray-800">R$ 47,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 2.000</td><td class="px-6 py-2 font-black text-gray-800">R$ 67,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 4.000</td><td class="px-6 py-2 font-black text-gray-800">R$ 77,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 7.100</td><td class="px-6 py-2 font-black text-gray-800">R$ 127,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 10.000</td><td class="px-6 py-2 font-black text-gray-800">R$ 197,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 20.000</td><td class="px-6 py-2 font-black text-gray-800">R$ 217,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 30.000</td><td class="px-6 py-2 font-black text-gray-800">R$ 467,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 50.000</td><td class="px-6 py-2 font-black text-gray-800">R$ 967,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 100.000</td><td class="px-6 py-2 font-black text-gray-800">R$ 1.967,00</td></tr>
                                    <tr class="hover:bg-gray-50"><td class="px-6 py-2 text-gray-600">Até R$ 150.000</td><td class="px-6 py-2 font-black text-gray-800">R$ 2.967,00</td></tr>
                                    <tr class="hover:bg-green-50/50"><td class="px-6 py-2 text-green-700 font-black">Acima de R$ 150.000</td><td class="px-6 py-2 font-black text-green-700">R$ 3.967,00</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[10px] text-gray-400 font-medium italic text-center">Taxa única paga por campanha ativa. Sem mensalidades.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final Call -->
        <section class="max-w-4xl mx-auto px-6 py-24 text-center">
            <h2
                class="text-3xl md:text-5xl font-black text-[#2c3e50] tracking-tighter uppercase italic leading-[0.9] mb-8">
                Pronto para ser o <br> próximo milionário?
            </h2>
            <p class="text-gray-500 font-medium mb-12 italic">O seu sucesso depende apenas da sua coragem. <br> Use
                nossa tecnologia e foque apenas em vender.</p>
            <div class="flex justify-center">
                <button onclick="openModal('faqModal')"
                    class="flex items-center gap-3 bg-gray-900 text-white px-10 py-5 rounded-3xl font-black uppercase text-xs tracking-widest shadow-2xl hover:bg-black transition-all transform hover:-translate-y-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Dúvidas Frequentes
                </button>
            </div>
        </section>

    </main>

    <!-- Modal FAQ -->
    <div id="faqModal" class="fixed inset-0 z-[200] hidden">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeModal('faqModal')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-6 pointer-events-none">
            <div class="bg-white w-full max-w-2xl rounded-[3rem] shadow-2xl pointer-events-auto relative overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Header -->
                <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                    <h3 class="text-xl font-black text-[#2c3e50] uppercase tracking-tighter italic">Dúvidas Frequentes</h3>
                    <button onclick="closeModal('faqModal')" class="text-gray-400 hover:text-gray-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="p-8 overflow-y-auto space-y-6 flex-grow custom-scrollbar">
                    <div class="space-y-2">
                        <h4 class="font-black text-gray-800 uppercase text-[11px] tracking-widest text-[#00a650]">O dinheiro cai na minha conta?</h4>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed">Sim! Você integra suas próprias chaves de API da Efí ou Mercado Pago. Todo o valor das vendas vai direto para o seu saldo nessas instituições.</p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-black text-gray-800 uppercase text-[11px] tracking-widest text-[#00a650]">Como funciona a ativação?</h4>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed">Nós cobramos apenas uma taxa única para ativar cada campanha. O valor depende do tamanho da sua arrecadação. Não há mensalidades.</p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-black text-gray-800 uppercase text-[11px] tracking-widest text-[#00a650]">Posso usar meu próprio domínio?</h4>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed">Nesta versão SaaS, você utiliza o link personalizado dentro do nosso portal. Caso precise de domínio próprio, entre em contato para um projeto dedicado.</p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-black text-gray-800 uppercase text-[11px] tracking-widest text-[#00a650]">É seguro para os compradores?</h4>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed">Sim, utilizamos tecnologia de ponta e criptografia. Além disso, as transações via Pix são processadas por instituições financeiras autorizadas pelo Banco Central.</p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-black text-gray-800 uppercase text-[11px] tracking-widest text-[#00a650]">Como faço para pagar a taxa?</h4>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed">Ao criar sua rifa no painel, você verá a opção de ativação. O pagamento é feito via Pix e a liberação da campanha é imediata.</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-8 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row justify-between items-center gap-4 sticky bottom-0 z-10">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ainda com dúvidas?</p>
                    <a href="https://wa.me/seu-numero" class="flex items-center gap-2 bg-[#25D366] text-white px-6 py-3 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl hover:bg-[#128C7E] transition-all">
                        Chamar no WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Animação simples
            const content = modal.querySelector('.relative');
            content.style.opacity = '0';
            content.style.transform = 'scale(0.95)';
            setTimeout(() => {
                content.style.transition = 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
                content.style.opacity = '1';
                content.style.transform = 'scale(1)';
            }, 10);
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            const content = modal.querySelector('.relative');
            content.style.opacity = '0';
            content.style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 200);
        }
    </script>

    <footer class="bg-white border-t border-gray-100 py-12">
        <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <p
                class="text-[9px] font-black text-gray-400 uppercase tracking-widest text-center md:text-left leading-relaxed">
                © 2026 $UPER$ORTE - Tecnologia para Sorteios Online. <br>
                Proibido para menores de 18 anos.
            </p>
            <div class="flex gap-4">
                <a href="../termos.php"
                    class="text-[9px] font-black text-gray-500 uppercase hover:text-gray-800">Termos</a>
                <a href="../privacidade.php"
                    class="text-[9px] font-black text-gray-500 uppercase hover:text-gray-800">Privacidade</a>
            </div>
        </div>
    </footer>

</body>

</html>
