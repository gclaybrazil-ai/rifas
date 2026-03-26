<?php
require_once 'backend/config.php';

// Proteção da Página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] !== 'criador' && $_SESSION['usuario_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Rifa - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .step-active { color: #00a650; }
        .step-inactive { color: #cbd5e1; }
        .progress-bar { transition: width 0.4s ease-in-out; }
    </style>
</head>
<body class="bg-[#f8fafc] flex min-h-screen antialiased text-gray-800">

    <!-- Sidebar (Simplified Link) -->
    <aside class="w-72 bg-white border-r border-gray-100 flex flex-col hidden lg:flex">
        <div class="p-8 text-center lg:text-left">
            <a href="dashboard.php" class="text-2xl font-black italic tracking-tighter text-[#00a650]">
                $UPER<span style="color: #2c3e50;">$ORTE</span>
            </a>
            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest mt-1">SaaS Platform</p>
        </div>
        <nav class="flex-grow px-6 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a11 11 0 001 1h3m10-11l2 2m-2-2v10a11 11 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Visão Geral
            </a>
            <a href="dashboard.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest bg-[#00a650] text-white shadow-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nova Rifa
            </a>
        </nav>
        <div class="p-6 border-t border-gray-100">
            <a href="logout.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-red-400 hover:bg-red-50 transition-all">
                Sair do SaaS
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col">
        <!-- Top Bar -->
        <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 px-8 py-4 flex justify-between items-center sticky top-0 z-50">
            <h2 class="text-sm font-black text-gray-400 uppercase tracking-widest">Painel SaaS / Criar Campanha</h2>
            <a href="dashboard.php" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-800 transition-colors">Voltar para Dashboard</a>
        </header>

        <div class="p-8 max-w-4xl mx-auto w-full">
            
            <!-- Steps Progress Dashboard -->
            <div class="mb-12">
                <div class="flex justify-between items-center mb-4">
                    <div id="step-label-1" class="text-[10px] font-black uppercase tracking-widest step-active">1. Informações</div>
                    <div id="step-label-2" class="text-[10px] font-black uppercase tracking-widest step-inactive">2. Regulamento</div>
                    <div id="step-label-3" class="text-[10px] font-black uppercase tracking-widest step-inactive">3. Resultado</div>
                </div>
                <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div id="progress-bar" class="w-1/3 h-full bg-[#00a650] progress-bar"></div>
                </div>
            </div>

            <form id="raffleForm" class="bg-white rounded-[3.5rem] shadow-2xl border border-gray-100 overflow-hidden">
                <div class="p-8 lg:p-12">
                    
                    <!-- STEP 1: INFORMAÇÕES -->
                    <div id="step-1" class="space-y-8 animate-in fade-in duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Nome da Campanha</label>
                                <input type="text" name="titulo" required 
                                       class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                       placeholder="Ex: Carro do Ano">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Por onde será extraído o resultado?</label>
                                <select name="extracao_tipo" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                                    <option value="Loteria Federal">Loteria Federal</option>
                                    <option value="Sorteador.com.br">Sorteador.com.br</option>
                                    <option value="Live no Instagram">Live no Instagram</option>
                                    <option value="Live no Youtube">Live no Youtube</option>
                                    <option value="Live no TikTok">Live no TikTok</option>
                                    <option value="Outros">Outros</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Quantidade de Títulos (Números)</label>
                                <select name="total_numeros" id="total_numeros" required 
                                        class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                                    <option value="25">25 títulos - (00 à 24)</option>
                                    <option value="50">50 títulos - (00 à 49)</option>
                                    <option value="100">100 títulos - (00 à 99)</option>
                                    <option value="150">150 títulos - (000 à 149)</option>
                                    <option value="200">200 títulos - (000 à 199)</option>
                                    <option value="250">250 títulos - (000 à 249)</option>
                                    <option value="300">300 títulos - (000 à 299)</option>
                                    <option value="400">400 títulos - (000 à 399)</option>
                                    <option value="500">500 títulos - (000 à 499)</option>
                                    <option value="600">600 títulos - (000 à 599)</option>
                                    <option value="700">700 títulos - (000 à 699)</option>
                                    <option value="800">800 títulos - (000 à 799)</option>
                                    <option value="900">900 títulos - (000 à 899)</option>
                                    <option value="1000">1.000 títulos - (000 à 999)</option>
                                    <option value="1100">1.100 títulos - (0000 à 1099)</option>
                                    <option value="1500">1.500 títulos - (0000 à 1499)</option>
                                    <option value="1600">1.600 títulos - (0000 à 1599)</option>
                                    <option value="2000">2.000 títulos - (0000 à 1999)</option>
                                    <option value="2500">2.500 títulos - (0000 à 2499)</option>
                                    <option value="3000">3.000 títulos - (0000 à 2999)</option>
                                    <option value="3500">3.500 títulos - (0000 à 3499)</option>
                                    <option value="4000">4.000 títulos - (0000 à 3999)</option>
                                    <option value="4500">4.500 títulos - (0000 à 4499)</option>
                                    <option value="5000">5.000 títulos - (0000 à 4999)</option>
                                    <option value="6000">6.000 títulos - (0000 à 5999)</option>
                                    <option value="7000">7.000 títulos - (0000 à 6999)</option>
                                    <option value="8000">8.000 títulos - (0000 à 7999)</option>
                                    <option value="10000">10 mil títulos - (00000 à 09999)</option>
                                    <option value="20000">20 mil títulos - (00000 à 19999)</option>
                                    <option value="30000">30 mil títulos - (00000 à 29999)</option>
                                    <option value="50000">50 mil títulos - (00000 à 49999)</option>
                                    <option value="70000">70 mil títulos - (00000 à 69999)</option>
                                    <option value="100000">100 mil títulos - (00000 à 99999)</option>
                                    <option value="200000">200 mil títulos - (000000 à 199999)</option>
                                    <option value="300000">300 mil títulos - (000000 à 299999)</option>
                                    <option value="500000">500 mil títulos - (000000 à 499999)</option>
                                    <option value="700000">700 mil títulos - (000000 à 699999)</option>
                                    <option value="1000000">1 milhão de títulos - (000000 à 999999)</option>
                                    <option value="10000000">10 milhões de títulos - (000000 à 9999999)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Valor de cada título (R$)</label>
                                <div class="relative">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-sm font-black text-[#00a650]">R$</span>
                                    <input type="number" step="0.01" name="valor_numero" id="valor_numero" required 
                                           class="w-full bg-gray-50 border border-gray-100 rounded-2xl pl-14 pr-5 py-4 text-sm font-black focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                           placeholder="1,00">
                                </div>
                            </div>
                        </div>

                        <!-- Live Calculation Card -->
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Arrecadação Estimada</p>
                                    <p class="text-xl font-black text-[#00a650]" id="live-total">R$ 0,00</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Taxa Única de Ativação</p>
                                    <p class="text-xl font-black text-gray-800" id="live-taxa">R$ 0,00</p>
                                </div>
                            </div>
                            <div class="flex px-4">
                                <button type="button" onclick="openModal('taxaModal')" class="flex items-center gap-2 text-[9px] font-black text-orange-500 uppercase tracking-widest hover:text-orange-600 transition-colors">
                                    Ver tabela de taxa
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7-7 7"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" onclick="nextStep(2)" 
                                    class="bg-green-500 text-white font-black px-12 py-5 rounded-2xl shadow-xl hover:bg-green-600 transition-all transform hover:scale-105 uppercase tracking-widest text-[10px] flex items-center gap-3">
                                Continuar <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: REGULAMENTO -->
                    <div id="step-2" class="hidden space-y-8 animate-in slide-in-from-right duration-500">
                        <!-- Image Upload (Same style as before but refined) -->
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Adicione uma foto de destaque</label>
                            <div class="relative group">
                                <input type="file" name="imagem" accept="image/*" required id="imgInput"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                                <div id="dropZone" class="w-full h-40 bg-gray-50 border-2 border-dashed border-gray-100 rounded-[2.5rem] flex flex-col items-center justify-center gap-3 transition-all group-hover:bg-white group-hover:border-green-200">
                                    <div class="w-10 h-10 bg-white shadow-sm rounded-xl flex items-center justify-center text-green-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest" id="imgName">Adicionar Imagem</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Prazo para reserva expirar</label>
                                <select name="tempo_reserva" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                                    <option value="5">5 minutos</option>
                                    <option value="15">15 minutos</option>
                                    <option value="60">1 hora</option>
                                    <option value="1440">24 horas</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Qtd Mínima</label>
                                    <input type="number" name="min_reserva" value="1" 
                                           class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all text-center">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Qtd Máxima</label>
                                    <input type="number" name="max_reserva" value="10" 
                                           class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all text-center">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Regulamento da Campanha (Opcional)</label>
                            <textarea name="subtitulo" rows="4" 
                                      class="w-full bg-gray-50 border border-gray-100 rounded-[2rem] px-6 py-6 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                      placeholder="Descreva as regras da sua ação..."></textarea>
                        </div>

                        <div class="flex justify-between items-center">
                            <button type="button" onclick="nextStep(1)" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-800 transition-colors">Voltar</button>
                            <button type="button" onclick="nextStep(3)" 
                                    class="bg-green-500 text-white font-black px-12 py-5 rounded-2xl shadow-xl hover:bg-green-600 transition-all transform hover:scale-105 uppercase tracking-widest text-[10px] flex items-center gap-3">
                                Continuar <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: MODO/RESULTADO -->
                    <div id="step-3" class="hidden space-y-8 animate-in slide-in-from-right duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="bg-gray-50 p-8 rounded-[2.5rem] border border-gray-100">
                                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Modo de Exibição</h4>
                                <div class="grid grid-cols-2 bg-white p-1 rounded-2xl border border-gray-100">
                                    <button type="button" class="py-3 px-4 rounded-xl text-[9px] font-black uppercase tracking-widest bg-gray-50 text-gray-400">Aleatórios</button>
                                    <button type="button" class="py-3 px-4 rounded-xl text-[9px] font-black uppercase tracking-widest bg-green-500 text-white shadow-lg">Expostos</button>
                                </div>
                                <p class="text-[10px] text-gray-400 font-medium mt-6 italic">Neste modo, o cliente escolhe manualmente os números na tabela.</p>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 flex items-center justify-between hover:border-green-200 transition-all cursor-pointer group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-green-50 text-green-500 rounded-xl flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg></div>
                                        <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Adicionar Prêmios</p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-300 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                                </div>
                                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 flex items-center justify-between hover:border-green-200 transition-all cursor-pointer group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg></div>
                                        <p class="text-[10px] font-black text-gray-600 uppercase tracking-widest">Adicionar Promoção</p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center bg-gray-900 p-8 rounded-[2.5rem] shadow-2xl">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-green-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Data do Sorteio</p>
                                    <p class="text-sm font-black text-white uppercase italic">A definir (Pendente)</p>
                                </div>
                            </div>
                            <button type="button" class="text-[9px] font-black text-white/50 uppercase tracking-widest hover:text-white transition-colors">Agendar Agora</button>
                        </div>

                        <div class="flex justify-between items-center pt-6">
                            <button type="button" onclick="nextStep(2)" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-800 transition-colors">Voltar</button>
                            <button type="submit" 
                                    class="bg-gray-900 text-white font-black px-12 py-5 rounded-2xl shadow-xl hover:bg-black transition-all transform hover:scale-105 uppercase tracking-widest text-[10px] flex items-center gap-3">
                                Finalizar Campanha <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div id="responseMsg" class="mt-8 text-center text-xs font-bold transition-all h-2"></div>

                </div>
            </form>

        </div>
    </main>

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
                            <tbody class="divide-y divide-gray-50">
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

    <script>
        // Steps Logic
        function nextStep(step) {
            document.getElementById('step-1').classList.add('hidden');
            document.getElementById('step-2').classList.add('hidden');
            document.getElementById('step-3').classList.add('hidden');
            document.getElementById('step-' + step).classList.remove('hidden');
            const progress = document.getElementById('progress-bar');
            const l1 = document.getElementById('step-label-1');
            const l2 = document.getElementById('step-label-2');
            const l3 = document.getElementById('step-label-3');
            if(step === 1) { 
                progress.style.width = '33.33%'; 
                l1.classList.replace('step-inactive', 'step-active'); l2.classList.replace('step-active', 'step-inactive'); l3.classList.replace('step-active', 'step-inactive');
            } else if(step === 2) { 
                progress.style.width = '66.66%'; 
                l1.classList.replace('step-inactive', 'step-active'); l2.classList.replace('step-inactive', 'step-active'); l3.classList.replace('step-active', 'step-inactive');
            } else { 
                progress.style.width = '100%'; 
                l1.classList.replace('step-inactive', 'step-active'); l2.classList.replace('step-inactive', 'step-active'); l3.classList.replace('step-inactive', 'step-active');
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Live Calculation Logic (UPDATED FULL TABLE)
        const totalInput = document.getElementById('total_numeros');
        const valorInput = document.getElementById('valor_numero');
        const liveTotal = document.getElementById('live-total');
        const liveTaxa = document.getElementById('live-taxa');

        function updateLiveCalc() {
            const qty = parseInt(totalInput.value) || 0;
            const price = parseFloat(valorInput.value) || 0;
            const total = qty * price;
            liveTotal.innerText = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            let taxa = 0;
            if (total > 150000) taxa = 3967;
            else if (total > 100000) taxa = 2967;
            else if (total > 70000) taxa = 1967;
            else if (total > 50000) taxa = 1467;
            else if (total > 30000) taxa = 967;
            else if (total > 20000) taxa = 467;
            else if (total > 10000) taxa = 217;
            else if (total > 7100) taxa = 197;
            else if (total > 4000) taxa = 127;
            else if (total > 2000) taxa = 77;
            else if (total > 1000) taxa = 67;
            else if (total > 701) taxa = 47;
            else if (total > 400) taxa = 37;
            else if (total > 200) taxa = 27;
            else if (total > 100) taxa = 17;
            else if (total > 0) taxa = 7;
            liveTaxa.innerText = 'R$ ' + taxa.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        }
        totalInput.addEventListener('input', updateLiveCalc);
        valorInput.addEventListener('input', updateLiveCalc);
        updateLiveCalc();

        // Image preview
        const imgInput = document.getElementById('imgInput');
        const imgName = document.getElementById('imgName');
        const dropZone = document.getElementById('dropZone');
        imgInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                imgName.innerText = e.target.files[0].name;
                dropZone.classList.add('bg-green-50', 'border-green-100');
            }
        });

        // Modals
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => { 
                const content = modal.querySelector('.relative');
                content.style.transition = 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
                content.style.opacity = '1';
                content.style.transform = 'scale(1)';
            }, 10);
        }
        function closeModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        document.getElementById('raffleForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const msg = document.getElementById('responseMsg');
            const formData = new FormData(e.target);
            btn.disabled = true;
            btn.innerHTML = 'Processando...';
            try {
                const response = await fetch('backend/api/salvar_rifa.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    msg.className = 'text-center text-xs font-bold text-green-500 tracking-widest uppercase';
                    msg.innerText = 'CAMPANHA CRIADA COM SUCESSO! REDIRECIONANDO...';
                    setTimeout(() => window.location.href = 'gerenciar-rifa.php?id=' + data.id, 2000);
                } else {
                    msg.className = 'text-center text-xs font-bold text-red-500 tracking-widest uppercase';
                    msg.innerText = data.error || 'Erro ao salvar.';
                    btn.disabled = false;
                    btn.innerHTML = 'Finalizar Campanha';
                }
            } catch (error) {
                msg.className = 'text-center text-xs font-bold text-red-500 tracking-widest uppercase';
                msg.innerText = 'ERRO DE CONEXÃO.';
                btn.disabled = false;
                btn.innerHTML = 'Finalizar Campanha';
            }
        });
    </script>
</body>
</html>
