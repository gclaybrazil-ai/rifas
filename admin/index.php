<?php
session_start();
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - $UPER$ORTE</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../frontend/png/cifrao.png">
    <style>
        /* Custom Scrollbar for Desktop */
        @media (min-width: 768px) {
            .scrollbar-thin::-webkit-scrollbar {
                width: 6px;
            }
            .scrollbar-thin::-webkit-scrollbar-track {
                background: transparent;
                margin: 10px 0; /* Keeps track away from the very top/bottom rounded edges */
            }
            .scrollbar-thin::-webkit-scrollbar-thumb {
                background: #d1d5db; /* gray-300 */
                border-radius: 10px;
            }
            .scrollbar-thin::-webkit-scrollbar-thumb:hover {
                background: #9ca3af; /* gray-400 */
            }
            #modal-integrations > div {
                scrollbar-gutter: stable; /* Preveit layout shift but keep it clean */
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans p-6">

    <div
        class="max-w-4xl mx-auto mb-6 bg-white p-6 rounded-lg shadow border border-gray-100 flex flex-col md:flex-row justify-between items-center text-center md:text-left gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#8e44ad]">Painel Administrativo</h1>
            <p class="text-sm text-gray-500">Gestão de Rifas</p>
        </div>        <div class="w-full md:w-auto flex flex-wrap gap-2 justify-center md:justify-end items-center">
            <!-- Cronômetro e Sair (Lado de Fora) -->
            <span id="session-timer" class="hidden text-[11px] font-black text-[#2c3e50] bg-gray-100 px-3 py-2 rounded-lg border border-gray-200 min-w-[120px] text-center">EXPIRA EM: 20:00</span>
            
            <!-- Menu Dropdown -->
            <div class="relative">
                <button id="btn-menu" class="bg-[#8e44ad] text-white font-black px-4 py-2 rounded-xl shadow-lg hover:bg-[#7d3c98] text-xs flex items-center gap-2 uppercase tracking-widest transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    Menu
                </button>
                <div id="dropdown-menu" class="hidden absolute left-0 md:right-0 md:left-auto mt-2 w-64 bg-white border border-gray-100 rounded-[1.5rem] shadow-2xl z-[100] p-4 flex-col gap-4">
                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 px-1">Atalhos Rápidos</div>
                    <button id="btn-affiliates" class="w-full bg-orange-600 text-white font-bold px-3 py-2.5 rounded-xl shadow-sm hover:bg-orange-700 text-xs text-left flex items-center gap-2">
                        <span class="w-6 h-6 bg-white/20 rounded flex items-center justify-center text-sm">👥</span> COMISSÕES (AFILIADOS)
                    </button>
                    <button id="btn-new-rifa" class="w-full bg-[#00a650] text-white font-bold px-3 py-2.5 rounded-xl shadow-sm hover:bg-[#009647] text-xs text-left flex items-center gap-2">
                        <span class="w-6 h-6 bg-white/20 rounded flex items-center justify-center text-lg">+</span> Criar Nova Rifa
                    </button>
                    <a href="rifas.php" class="w-full bg-blue-500 text-white font-bold px-3 py-2.5 rounded-xl shadow-sm hover:bg-blue-600 text-xs flex items-center gap-2">
                        <span class="w-6 h-6 bg-white/20 rounded flex items-center justify-center text-sm">🎫</span> Gerenciar Rifas
                    </a>
                    <button id="btn-billing" class="w-full bg-purple-600 text-white font-bold px-3 py-2.5 rounded-xl shadow-sm hover:bg-purple-700 text-xs text-left flex items-center gap-2">
                        <span class="w-6 h-6 bg-white/20 rounded flex items-center justify-center text-sm">💰</span> Financeiro
                    </button>
                    <a href="ganhadores.php" class="w-full bg-yellow-400 text-[#2c3e50] font-bold px-3 py-2.5 rounded-xl shadow-sm hover:bg-yellow-500 text-xs flex items-center gap-2">
                        <span class="w-6 h-6 bg-black/5 rounded flex items-center justify-center text-sm">🏆</span> Ganhadores
                    </a>
                    <a href="../index.html" class="w-full bg-gray-100 text-gray-700 font-bold px-3 py-2.5 rounded-xl shadow-sm hover:bg-gray-200 text-xs flex items-center gap-2">
                        <span class="w-6 h-6 bg-white rounded flex items-center justify-center text-sm">🏠</span> Voltar para a Loja
                    </a>

                    <div class="h-px bg-gray-100 my-2"></div>
                    <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1 px-1">Configurações Avançadas</div>
                    
                    <button id="btn-integrations" type="button" class="w-full bg-indigo-50 text-indigo-700 font-bold px-3 py-2.5 rounded-xl hover:bg-indigo-100 text-xs text-left flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Integrações e Gateways
                    </button>
                    <button id="btn-security" type="button" class="w-full bg-red-50 text-red-600 font-bold px-3 py-2.5 rounded-xl hover:bg-red-100 text-xs text-left flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Acesso do Administrador
                    </button>
                    <button id="btn-open-security-monitor" type="button" class="w-full bg-gray-800 text-white font-bold px-3 py-2.5 rounded-xl hover:bg-black text-xs text-left flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Segurança e Logs de Ações
                    </button>
                    <button id="btn-open-smtp" type="button" class="w-full bg-blue-50 text-blue-600 font-bold px-3 py-2.5 rounded-xl hover:bg-blue-100 text-xs text-left flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Servidor de E-mail (SMTP)
                    </button>
                    <div class="flex items-center justify-between gap-2 bg-gray-50 px-3 py-2 rounded-xl border border-gray-100">
                        <span class="text-[10px] font-black text-gray-500 uppercase">Assistente IA</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="assistant-toggle" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-500"></div>
                        </label>
                    </div>
                    <button id="btn-open-assistant" type="button" class="w-full bg-purple-50 text-purple-600 font-bold px-3 py-2.5 rounded-xl hover:bg-purple-100 text-xs text-left flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        Mensagem e Config. IA
                    </button>
                    <button id="btn-open-popup" type="button" class="w-full bg-orange-50 text-orange-600 font-bold px-3 py-2.5 rounded-xl hover:bg-orange-100 text-xs text-left flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                        Config. Popup de Entrada
                    </button>
                    <div class="flex items-center justify-between gap-2 bg-gray-50 px-3 py-2 rounded-xl border border-gray-100">
                        <span class="text-[10px] font-black text-gray-500 uppercase">Modo de Manutenção</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="maintenance-toggle" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-500"></div>
                        </label>
                    </div>
                </div>
            </div>

            <a href="../backend/api/logout.php" class="bg-[#2c3e50] text-white font-black px-4 py-2 rounded-lg shadow hover:bg-black text-[11px] uppercase tracking-widest transition-all">Sair</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-5 gap-4 mb-6" id="stats-grid">
        <div class="bg-white p-4 rounded-xl shadow-sm text-center border-l-4 border-green-500 overflow-hidden">
            <h3 class="text-gray-500 text-[10px] font-bold uppercase mb-1 tracking-wider text-center">Livres</h3>
            <p class="text-2xl font-black text-[#2c3e50] truncate" id="stat-livre">0</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm text-center border-l-4 border-yellow-500 overflow-hidden">
            <h3 class="text-gray-500 text-[10px] font-bold uppercase mb-1 tracking-wider text-center">Reservados</h3>
            <p class="text-2xl font-black text-[#2c3e50] truncate" id="stat-reservado">0</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm text-center border-l-4 border-purple-500 overflow-hidden">
            <h3 class="text-gray-500 text-[10px] font-bold uppercase mb-1 tracking-wider text-center">Pagos</h3>
            <p class="text-2xl font-black text-[#9b59b6] truncate" id="stat-pago">0</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm text-center border-l-4 border-blue-500 overflow-hidden">
            <h3 class="text-gray-500 text-[10px] font-bold uppercase mb-1 tracking-wider text-center">Faturamento</h3>
            <p class="text-2xl font-black text-blue-600 truncate" id="stat-faturamento">R$ 0,00</p>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm text-center border-l-4 border-red-500 overflow-hidden">
            <h3 class="text-gray-500 text-[10px] font-bold uppercase mb-1 tracking-wider text-center">Taxas (1.19%)</h3>
            <p class="text-2xl font-black text-red-600 truncate" id="stat-taxas">R$ 0,00</p>
        </div>
    </div>

    <!-- Tabela Reservas -->
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-bold text-gray-700 uppercase tracking-wide">Últimas Reservas</h2>

            <div class="flex flex-wrap gap-2">
                <button onclick="setStatusFilter('')" id="tab-all"
                    class="status-tab px-3 py-1 rounded-full text-[10px] font-bold uppercase transition-all bg-gray-200 text-gray-700">Todos</button>
                <button onclick="setStatusFilter('pago')" id="tab-pago"
                    class="status-tab px-3 py-1 rounded-full text-[10px] font-bold uppercase transition-all bg-white border border-gray-200 text-gray-500">Pagos</button>
                <button onclick="setStatusFilter('pendente')" id="tab-pendente"
                    class="status-tab px-3 py-1 rounded-full text-[10px] font-bold uppercase transition-all bg-white border border-gray-200 text-gray-500">Pendentes</button>
                <button onclick="setStatusFilter('expirado')" id="tab-expirado"
                    class="status-tab px-3 py-1 rounded-full text-[10px] font-bold uppercase transition-all bg-white border border-gray-200 text-gray-500">Expirados</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase font-bold text-xs">
                        <th class="p-4 border-b">ID / Nome da RF</th>
                        <th class="p-4 border-b">Comprador</th>
                        <th class="p-4 border-b">WhatsApp</th>
                        <th class="p-4 border-b">Valor</th>
                        <th class="p-4 border-b">Status</th>
                        <th class="p-4 border-b text-right">Ação</th>
                    </tr>
                </thead>
                <tbody id="table-reservas">
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div id="pagination-reservas"
            class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-center items-center gap-2">
            <!-- Buttons injected here -->
        </div>
    </div>

    <!-- Modal Integracoes -->
    <div id="modal-integrations"
        class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-start justify-center p-4 md:items-center backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-md w-full text-left shadow-2xl relative max-h-[90vh] overflow-y-auto scrollbar-thin !overflow-x-hidden">
            <button id="btn-close-integrations"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-800">Configurações Gerais</h2>
                    <p class="text-xs text-gray-500">Acessos, PIX e Segurança</p>
                </div>
            </div>

            <form id="form-integrations" class="flex flex-col gap-4">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Escolha
                        o Gateway</label>
                    <select id="gateway-provider"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="mercadopago">Mercado Pago</option>
                        <option value="efi">Efí Bank (Gerencianet)</option>
                    </select>
                </div>
                
                <div id="fee-repassar-container" class="bg-gray-50 p-4 rounded-lg flex items-start gap-3 border border-gray-100">
                    <div class="flex items-center h-5">
                        <input id="repassar_taxa" name="repassar_taxa" type="checkbox" value="1"
                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                    </div>
                    <div class="text-sm">
                        <label for="repassar_taxa" class="font-bold text-gray-700 cursor-pointer">REPASSAR TAXA PARA O COLABORADOR</label>
                        <p class="text-gray-500 text-[10px] leading-tight mt-1">
                            Se optar por não repassar, uma taxa mínima ainda será cobrada caso o valor da reserva não seja maior do que a taxa cobrada pelo método de pagamento.
                        </p>
                    </div>
                </div>
                <div id="fields-mercadopago">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Token / Access Key (MP)</label>
                    <input type="password" id="gateway-token"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                        placeholder="APP_USR-...">
                </div>

                <!-- Efí Fields -->
                <div id="fields-efi" class="hidden flex flex-col gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Client ID (Efí)</label>
                        <input type="text" id="efi-client-id"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                            placeholder="Client_Id_...">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Client Secret (Efí)</label>
                        <input type="password" id="efi-client-secret"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                            placeholder="Client_Secret_...">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Certificado .p12 (Upload)</label>
                        <input type="file" id="efi-cert-file" accept=".p12"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <p id="cert-status" class="text-[9px] text-gray-400 mt-1 ml-1"></p>
                    </div>
                </div>

                <!-- WhatsApp Notifications (Evolution API) -->
                <div class="h-px bg-gray-100 my-4"></div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-green-100 rounded-lg text-green-600">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.412-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.309 1.656zm6.29-4.143c1.589.943 3.143 1.416 4.703 1.417 5.432.001 9.853-4.42 9.856-9.853.002-2.633-1.025-5.109-2.892-6.977-1.867-1.868-4.341-2.896-6.976-2.898-5.432 0-9.854 4.421-9.857 9.855-.001 1.737.457 3.432 1.326 4.906l-.527 1.922 2.019-.53zm10.744-7.404c-.232-.117-1.371-.677-1.583-.754-.212-.077-.366-.117-.52.117-.154.234-.597.754-.732.909-.136.155-.271.174-.503.057-.232-.117-.98-.362-1.868-1.152-.69-.615-1.156-1.376-1.291-1.61-.136-.234-.015-.361.102-.477.105-.104.232-.271.348-.407.116-.136.155-.234.232-.39s.039-.291-.019-.407c-.058-.117-.52-1.255-.712-1.714-.187-.449-.377-.388-.52-.395-.135-.007-.29-.008-.444-.008-.154 0-.405.058-.617.291-.212.234-.81.792-.81 1.932 0 1.14.83 2.242.946 2.399.116.156 1.632 2.492 3.954 3.493.552.238.983.38 1.32.487.554.174 1.057.149 1.456.09.444-.066 1.371-.56 1.563-1.103.193-.544.193-1.01.136-1.103-.058-.095-.212-.154-.445-.271z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-gray-800 tracking-tighter uppercase italic">Notificações WhatsApp</h2>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest leading-tight">API Evolution (Confirm. Compra & Ganhadores)</p>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 flex items-center gap-2">
                            Evolution API URL
                            <button type="button" id="btn-help-evolution" class="w-4 h-4 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-[9px] font-bold hover:bg-green-200 transition-colors shadow-sm" title="Como configurar a Evolution API?">?</button>
                        </label>
                        <input type="url" id="evolution_api_url" placeholder="https://api.seusite.com" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">API Key (Chave Global/Instance)</label>
                        <input type="password" id="evolution_api_key" placeholder="Sua Chave API" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Nome da Instância</label>
                        <input type="text" id="evolution_instance" placeholder="MinhaRifa" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <!-- Botão de Teste -->
                    <div class="bg-green-50 p-4 rounded-xl border border-green-100 mt-2">
                        <label class="text-[10px] font-bold text-green-700 uppercase mb-2 block">Número p/ Teste (WhatsApp)</label>
                        <div class="flex gap-2">
                            <input type="text" id="test-whatsapp-number" placeholder="5511999999999" class="flex-1 bg-white border border-green-200 rounded-lg p-2 text-xs outline-none">
                            <button type="button" id="btn-test-whatsapp" class="bg-green-600 text-white font-bold px-4 py-2 rounded-lg text-[10px] hover:bg-green-700 shadow-sm uppercase transition-all whitespace-nowrap">
                                Testar Agora
                            </button>
                        </div>
                        <p class="text-[9px] text-green-600/70 mt-1 leading-tight">Configurações devem ser salvas antes de testar.</p>
                    </div>
                </div>

                <div class="h-px bg-gray-100 my-4"></div>

                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Tempo
                        Padrão P/ Pagamento (Minutos)</label>
                    <input type="number" id="tempo-pagamento"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                        placeholder="3" min="1" max="5" value="3">
                    <p class="text-[10px] text-gray-400 mt-1 ml-1">Para garantir máxima urgência de conversão, use no
                        máximo 5 min.</p>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 flex items-center gap-2">
                        Exigência de Senha (Usuários)
                        <button type="button" id="btn-help-password" class="w-4 h-4 bg-gray-100 text-gray-500 rounded-full flex items-center justify-center text-[10px] font-bold hover:bg-indigo-100 hover:text-indigo-600 transition-colors shadow-sm" title="💡 Ajuda sobre senhas">?</button>
                    </label>
                    <select id="password-complexity" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="1">Alfanumérica (Mín 8 dígitos)</option>
                        <option value="2">Alfanumérica + Caracteres Especiais (Mín 8 dígitos)</option>
                    </select>
                    <p class="text-[9px] text-gray-400 mt-1 ml-1 leading-tight">Define o nível de segurança exigido para todos os usuários e afiliados.</p>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Link
                        Grupo VIP (WhatsApp)</label>
                    <input type="url" id="group-vip"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                        placeholder="https://chat.whatsapp.com/...">
                </div>
                <div>
                    <label
                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">WhatsApp
                        Suporte (Somente Números)</label>
                    <input type="text" id="whatsapp-suporte"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                        placeholder="5511999999999">
                </div>
                <div>
                    <label
                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Mensagem
                        Padrão (WhatsApp Suporte)</label>
                    <textarea id="mensagem-suporte"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none h-20"
                        placeholder="Olá, $uper$orte! Preciso de ajuda."></textarea>
                </div>
                <div class="border-t border-gray-100 pt-4 mt-2">
                    <label class="text-[10px] font-black text-purple-600 uppercase tracking-widest ml-1 mb-1 inline-flex items-center gap-2">
                        Template Compartilhamento (Afiliados)
                        <button type="button" id="btn-help-template" class="w-4 h-4 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-[10px] font-bold hover:bg-purple-200 transition-colors shadow-sm" title="Clique para ver como usar">?</button>
                    </label>
                    <textarea id="whatsapp-share-template"
                        class="w-full bg-purple-50 border border-purple-100 rounded-xl p-3 text-sm focus:ring-2 focus:ring-purple-500 outline-none h-32"
                        placeholder="🚨 {rifa_nome} 🚨&#10;&#10;🎟 Apenas {preco} por número!&#10;&#10;🎁 Prêmios:&#10;🥇 1º: {premio1}&#10;🥈 2º: {premio2}&#10;&#10;👇 Participe:&#10;{link}"></textarea>
                    <p class="text-[9px] text-gray-400 mt-1 ml-1 leading-tight">Use <b>{rifa_nome}</b>, <b>{link}</b> e <b>{preco}</b> como espaços reservados.</p>
                </div>
                <button type="submit" id="btn-save-integrations"
                    class="w-full bg-indigo-600 text-white font-bold py-4 rounded-xl shadow uppercase text-sm mt-4 mb-4 hover:bg-indigo-700 transition-colors">
                    Salvar Configurações
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Ajuda Senha -->
    <div id="modal-password-help" class="fixed inset-0 bg-black bg-opacity-80 z-[70] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-sm w-full text-left shadow-2xl relative">
            <button id="btn-close-password-help" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-800">Regras de Senha</h2>
                    <p class="text-xs text-gray-500">Mínimo 8 caracteres</p>
                </div>
            </div>
            <div class="space-y-4">
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <h3 class="text-xs font-bold text-gray-700 uppercase mb-2">1. Alfanumérica</h3>
                    <p class="text-xs text-gray-500 leading-relaxed mb-2">Exige a combinação de letras e números para garantir que a senha não seja apenas sequencial.</p>
                    <div class="bg-white p-2 rounded border border-gray-200 text-xs font-mono text-indigo-600">Ex: sorteio2024</div>
                </div>
                <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <h3 class="text-xs font-bold text-indigo-700 uppercase mb-2">2. + Caracteres Especiais</h3>
                    <p class="text-xs text-indigo-600/70 leading-relaxed mb-2">Segurança Máxima. Exige ao menos uma <b>letra maiúscula</b>, uma <b>minúscula</b>, um <b>número</b> e um <b>caractere especial</b> (ex: @, #, $).</p>
                    <div class="bg-white p-2 rounded border border-indigo-200 text-xs font-mono text-indigo-700 font-bold">Ex: Sorte@2024!</div>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-password-help').classList.add('hidden')" class="w-full bg-gray-800 text-white font-bold py-3 rounded-xl mt-6 hover:bg-black transition-colors uppercase text-xs tracking-widest">Entendi</button>
        </div>
    </div>

    <!-- Modal Nova Rifa -->
    <div id="modal-new-rifa"
        class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl relative max-h-[95vh] overflow-y-auto">
            <button id="btn-close-new"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 class="text-xl font-black text-[#2c3e50] mb-4 uppercase">Criar Nova Rifa</h2>

            <form id="form-new-rifa" class="flex flex-col gap-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Nome da Rifa</label>
                        <input type="text" id="new-nome" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#00a650] outline-none"
                            placeholder="Ex: Sorteio do PIX">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Preço do Número (R$)</label>
                        <input type="number" step="0.01" id="new-preco" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#00a650] outline-none"
                            placeholder="Ex: 0.10">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Imagem Banner (URL ou Arquivo Próprio)</label>
                    <div class="flex gap-2">
                        <input type="url" id="new-imagem"
                            class="w-1/2 bg-gray-50 border border-gray-200 rounded-lg p-2 text-[10px] md:text-xs focus:ring-2 focus:ring-[#00a650] outline-none"
                            placeholder="Ou cole o Link https://...">
                        <input type="file" id="new-imagem-file" accept="image/*"
                            class="w-1/2 bg-gray-50 border border-gray-200 rounded-lg p-1.5 text-[10px] md:text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-[#00a650] file:text-white file:font-bold hover:file:bg-[#009647]">
                    </div>
                    <!-- Global Image Preview -->
                    <div id="preview-new-rifa" class="hidden mt-2 h-20 w-full rounded-lg overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center relative group">
                        <img id="img-new-rifa" class="h-full w-full object-cover">
                        <button type="button" onclick="clearImagePreview('new-imagem-file', 'new-imagem', 'img-new-rifa', 'preview-new-rifa')" class="absolute top-1 right-1 bg-red-500 text-white p-1.5 rounded-full shadow-md hover:bg-red-600 z-10" title="Limpar">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Quantidade de Números</label>
                        <input type="number" id="new-qtd" min="10" max="10000" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#00a650] outline-none"
                            placeholder="Mín: 10, Máx: 10000">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Sorteado Por</label>
                        <select id="new-sorteio"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#00a650] outline-none">
                            <option value="Loteria Federal">Loteria Federal</option>
                            <option value="Jogo do Bicho">Jogo do Bicho</option>
                            <option value="Sorteador.com.br">Sorteador.com.br</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-3 mt-1">
                    <p class="text-[10px] font-black text-[#8e44ad] uppercase mb-2">Prêmios Específicos (Opcional)</p>
                    <div class="flex flex-col gap-2">
                        <input type="text" id="new-premio1"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-[#8e44ad] outline-none"
                            placeholder="1º Prêmio (Ex: iPhone 16)">
                        <input type="text" id="new-premio2"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-[#8e44ad] outline-none"
                            placeholder="2º Prêmio">
                        <input type="text" id="new-premio3"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-[#8e44ad] outline-none"
                            placeholder="3º Prêmio">
                        <input type="text" id="new-premio4"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-[#8e44ad] outline-none"
                            placeholder="4º Prêmio">
                        <input type="text" id="new-premio5"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-[#8e44ad] outline-none"
                            placeholder="5º Prêmio">
                    </div>
                </div>

                <button type="submit" id="btn-submit-new"
                    class="w-full bg-[#00a650] text-white font-bold py-3 mt-3 rounded-xl hover:bg-[#009647] uppercase text-sm shadow">Criar
                    e Ativar Rifa</button>
            </form>
        </div>
    </div>

    <!-- Modal Afiliados -->
    <div id="modal-affiliates" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-4xl w-full shadow-2xl relative max-h-[90vh] flex flex-col">
            <button onclick="closeModal('modal-affiliates')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-pink-100 rounded-lg text-pink-600"><span class="text-2xl">👥</span></div>
                <div>
                    <h2 class="text-xl font-black text-gray-800">Gestão de Afiliados</h2>
                    <p class="text-xs text-gray-500">Saldo e Comissões</p>
                </div>
            </div>

            <div class="overflow-y-auto flex-1 scrollbar-thin">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-gray-50 text-gray-400 uppercase font-black tracking-widest sticky top-0 z-10">
                        <tr>
                            <th class="p-3 border-b">Afiliado</th>
                            <th class="p-3 border-b">Vendas</th>
                            <th class="p-3 border-b">Saldo Atual</th>
                            <th class="p-3 border-b">Total Pago</th>
                            <th class="p-3 border-b text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="table-affiliates" class="text-gray-700">
                        <!-- Content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes de Vendas do Afiliado -->
    <div id="modal-af-sales" class="fixed inset-0 bg-black bg-opacity-90 z-[60] hidden flex items-center justify-center p-4 backdrop-blur-md">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-3xl w-full shadow-2xl relative max-h-[85vh] flex flex-col">
            <button onclick="closeModal('modal-af-sales')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="mb-4">
                <h2 id="af-sales-title" class="text-xl font-black text-gray-800 uppercase">Vendas Detalhadas</h2>
                <p class="text-xs text-gray-400">Somente vendas pagas atribuídas a este afiliado</p>
            </div>
            <div class="overflow-y-auto flex-1 scrollbar-thin rounded-xl border border-gray-100">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-gray-50 text-gray-400 font-bold uppercase sticky top-0 z-10">
                        <tr>
                            <th class="p-3 border-b">Data</th>
                            <th class="p-3 border-b">Comprador</th>
                            <th class="p-3 border-b">Rifa</th>
                            <th class="p-3 border-b">Números</th>
                            <th class="p-3 border-b text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody id="table-af-sales-body">
                        <!-- Content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Afiliados -->
    <div id="modal-affiliates" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-4xl w-full shadow-2xl relative max-h-[90vh] flex flex-col">
            <button onclick="closeModal('modal-affiliates')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-pink-100 rounded-lg text-pink-600"><span class="text-2xl">👥</span></div>
                <div>
                    <h2 class="text-xl font-black text-gray-800">Gestão de Afiliados</h2>
                    <p class="text-xs text-gray-500">Saldo e Comissões</p>
                </div>
                <button onclick="openPendingPayouts()" class="ml-auto bg-indigo-600 text-white font-black px-4 py-2 rounded-xl text-[10px] hover:bg-indigo-700 shadow-sm flex items-center gap-2">
                    <span>📋</span> SAQUES PENDENTES
                </button>
            </div>

            <div class="overflow-y-auto flex-1 scrollbar-thin">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-gray-50 text-gray-400 uppercase font-black tracking-widest sticky top-0 z-10">
                        <tr>
                            <th class="p-3 border-b">Afiliado</th>
                            <th class="p-3 border-b">Vendas</th>
                            <th class="p-3 border-b">Saldo Atual</th>
                            <th class="p-3 border-b">Total Pago</th>
                            <th class="p-3 border-b text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="table-affiliates" class="text-gray-700">
                        <!-- Content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes de Vendas do Afiliado -->
    <div id="modal-af-sales" class="fixed inset-0 bg-black bg-opacity-90 z-[60] hidden flex items-center justify-center p-4 backdrop-blur-md">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-3xl w-full shadow-2xl relative max-h-[85vh] flex flex-col">
            <button onclick="closeModal('modal-af-sales')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="mb-4">
                <h2 id="af-sales-title" class="text-xl font-black text-gray-800 uppercase">Vendas Detalhadas</h2>
                <p class="text-xs text-gray-400">Somente vendas pagas atribuídas a este afiliado</p>
            </div>
            <div class="overflow-y-auto flex-1 scrollbar-thin rounded-xl border border-gray-100">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-gray-50 text-gray-400 font-bold uppercase sticky top-0 z-10">
                        <tr>
                            <th class="p-3 border-b">Data</th>
                            <th class="p-3 border-b">Comprador</th>
                            <th class="p-3 border-b">Rifa</th>
                            <th class="p-3 border-b">Números</th>
                            <th class="p-3 border-b text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody id="table-af-sales-body">
                        <!-- Content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Saques Pendentes -->
    <div id="modal-pending-payouts" class="fixed inset-0 bg-black bg-opacity-90 z-[70] hidden flex items-center justify-center p-4 backdrop-blur-md">
        <div class="bg-white rounded-3xl p-6 md:p-8 max-w-2xl w-full shadow-2xl relative max-h-[85vh] flex flex-col border-4 border-indigo-100">
            <button onclick="closeModal('modal-pending-payouts')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="mb-6">
                <h2 class="text-xl font-black text-gray-800 uppercase flex items-center gap-2">
                    <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">📋</span>
                    Pagamentos a Realizar
                </h2>
                <p class="text-xs text-gray-400">Pegue a chave Pix e após pagar, confirme no botão verde para atualizar o sistema.</p>
            </div>
            <div class="overflow-y-auto flex-1 scrollbar-thin">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-gray-50 text-gray-400 font-bold uppercase sticky top-0 z-10">
                        <tr>
                            <th class="p-3 border-b">Afiliado / Chave Pix</th>
                            <th class="p-3 border-b">Valor</th>
                            <th class="p-3 border-b text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody id="table-pending-payouts" class="text-gray-700">
                        <!-- Content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Faturamento (FIN) -->
    <div id="modal-billing"
        class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-7 max-w-md w-full shadow-2xl relative">
            <button id="btn-close-billing" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 class="text-xl font-black text-[#2c3e50] mb-6 uppercase flex items-center gap-2">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
                Relatório de Faturamento
            </h2>

            <div class="grid grid-cols-2 gap-2 mb-6">
                <button onclick="fetchBilling('today')"
                    class="bg-gray-100 hover:bg-gray-200 p-3 rounded-xl text-xs font-bold text-gray-700 transition-all">Hoje</button>
                <button onclick="fetchBilling('7')"
                    class="bg-gray-100 hover:bg-gray-200 p-3 rounded-xl text-xs font-bold text-gray-700 transition-all">Últimos
                    7 Dias</button>
                <button onclick="fetchBilling('30')"
                    class="bg-gray-100 hover:bg-gray-200 p-3 rounded-xl text-xs font-bold text-gray-700 transition-all">Últimos
                    30 Dias</button>
                <button onclick="toggleCustomRange()"
                    class="bg-gray-800 text-white p-3 rounded-xl text-xs font-bold transition-all">Personalizado</button>
            </div>

            <div id="custom-range"
                class="hidden flex flex-col gap-3 mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Início</label>
                        <input type="date" id="bill-start"
                            class="w-full bg-white border border-gray-200 rounded-lg p-2 text-xs outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Fim</label>
                        <input type="date" id="bill-end"
                            class="w-full bg-white border border-gray-200 rounded-lg p-2 text-xs outline-none">
                    </div>
                </div>
                <button onclick="fetchBilling('custom')"
                    class="w-full bg-[#00a650] text-white font-bold py-2 rounded-lg text-xs hover:bg-[#009647]">Gerar
                    Relatório</button>
            </div>

            <div id="billing-result"
                class="hidden bg-blue-50 p-5 rounded-[2rem] border border-blue-100 text-center flex flex-col gap-1">
                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest" id="bill-label">
                    Faturamento Total</p>
                <h3 class="text-3xl font-black text-blue-700" id="bill-total">R$ 0,00</h3>
                <p class="text-[10px] font-bold text-blue-400" id="bill-count">0 Reservas Pagas</p>
            </div>
        </div>
    </div>

    <!-- Modal Seguranca/Acesso -->
    <div id="modal-security"
        class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-left shadow-2xl relative">
            <button id="btn-close-security" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-red-100 rounded-lg text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-800">Acesso Admin</h2>
                    <p class="text-xs text-gray-500">Alterar Usuário e Senha</p>
                </div>
            </div>
            <form id="form-security" class="flex flex-col gap-4">
                <div>
                   <label class="text-[10px] font-bold text-gray-400 uppercase">Novo Usuário</label>
                   <input type="text" id="new-admin-user" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none" placeholder="admin" required>
                </div>
                <div>
                   <label class="text-[10px] font-bold text-gray-400 uppercase">Email de Recuperação</label>
                   <input type="email" id="new-admin-email" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none" placeholder="admin@email.com" required>
                </div>
                <div>
                   <label class="text-[10px] font-bold text-gray-400 uppercase">Nova Senha</label>
                   <input type="password" id="new-admin-pass" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none" placeholder="Deixe em branco para não alterar">
                </div>
                <button type="submit" id="btn-save-security" class="w-full bg-red-600 text-white font-bold py-4 rounded-xl shadow uppercase text-sm mt-2 hover:bg-red-700 transition-colors">Atualizar Acesso</button>
                <button type="button" id="btn-test-location-admin" class="w-full bg-gray-100 text-gray-600 font-bold py-3 rounded-xl shadow-sm uppercase text-[10px] mt-2 border border-gray-200 hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
                    <span>📍 Testar Meu Acesso (GPS)</span>
                </button>
                <div id="location-test-cont-admin" class="hidden mt-2 p-3 bg-blue-50 rounded-xl text-[10px] text-blue-700 border border-blue-100 leading-relaxed">
                    <div id="location-test-result-admin"></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Configurações de Email -->
    <div id="modal-smtp" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full text-left shadow-2xl relative">
            <button id="btn-close-smtp" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black text-gray-800">Parâmetros de Email</h2>
                        <button type="button" id="btn-help-smtp" class="w-5 h-5 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center text-[10px] font-bold hover:bg-gray-300 transition-colors" title="Como configurar?">?</button>
                    </div>
                    <p class="text-xs text-gray-500">Configuração para Recuperação de Senha</p>
                </div>
            </div>
            <form id="form-smtp" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                   <label class="text-[10px] font-bold text-gray-400 uppercase">Servidor SMTP</label>
                   <input type="text" id="smtp-host" name="smtp_host" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none" placeholder="smtp.exemplo.com">
                </div>
                <div>
                   <label class="text-[10px] font-bold text-gray-400 uppercase">Porta SMTP</label>
                   <input type="text" id="smtp-port" name="smtp_port" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none" placeholder="587">
                </div>
                <div>
                   <label class="text-[10px] font-bold text-gray-400 uppercase">Usuário SMTP</label>
                   <input type="text" id="smtp-user" name="smtp_user" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none" placeholder="login@email.com">
                </div>
                <div>
                   <label class="text-[10px] font-bold text-gray-400 uppercase">Senha SMTP</label>
                   <input type="password" id="smtp-pass" name="smtp_pass" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none" placeholder="******">
                </div>
                <div>
                   <label class="text-[10px] font-bold text-gray-400 uppercase">Nome do Remetente</label>
                   <input type="text" id="smtp-from-name" name="smtp_from_name" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none" placeholder="Admin Sorte">
                </div>
                <div>
                   <label class="text-[10px] font-bold text-gray-400 uppercase">Email do Remetente</label>
                   <input type="email" id="smtp-from-email" name="smtp_from_email" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm outline-none" placeholder="noreply@site.com">
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" id="btn-save-smtp" class="flex-1 bg-[#00a650] text-white font-bold py-4 rounded-xl shadow uppercase text-sm mt-2 hover:bg-[#009647] transition-colors">Salvar Configuração</button>
                    <button type="button" id="btn-test-email" class="bg-blue-500 text-white font-bold py-4 px-6 rounded-xl shadow uppercase text-sm mt-2 hover:bg-blue-600 transition-colors flex items-center gap-2">
                        <span>Testar Envio</span>
                        <i id="icon-test-email">📧</i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ajuda Evolution -->
    <div id="modal-help-evolution" class="fixed inset-0 bg-black bg-opacity-80 z-[60] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-lg w-full text-left shadow-2xl relative max-h-[90vh] overflow-y-auto scrollbar-thin">
            <button onclick="closeHelpEvolution()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <h2 class="text-2xl font-black text-gray-800 mb-6 uppercase tracking-tighter">Guia de Integração Evolution API</h2>
            
            <div class="space-y-6">
                <div class="border-b pb-4">
                    <h3 class="font-bold text-green-600 mb-2 flex items-center gap-2">
                        <span class="w-6 h-6 bg-green-100 rounded flex items-center justify-center text-[10px]">1</span>
                        Preparar o Servidor
                    </h3>
                    <p class="text-[11px] text-gray-600 leading-relaxed">Você precisa ter a Evolution API instalada (via Docker ou serviço contratado). Certifique-se de criar uma <b>Instância</b> e ler o QR Code com o WhatsApp que fará os envios.</p>
                </div>

                <div class="border-b pb-4">
                    <h3 class="font-bold text-green-600 mb-2 flex items-center gap-2">
                        <span class="w-6 h-6 bg-green-100 rounded flex items-center justify-center text-[10px]">2</span>
                        Pegar Credenciais
                    </h3>
                    <ul class="text-[11px] text-gray-600 space-y-2 ml-4 list-disc">
                        <li><b>API URL:</b> O endereço do seu servidor (ex: <code>https://api.seusite.com</code>).</li>
                        <li><b>API Key:</b> Sua chave de acesso global ou da instância.</li>
                        <li><b>Nome da Instância:</b> O nome exato que você deu na Evolution.</li>
                    </ul>
                </div>

                <div class="border-b pb-4">
                    <h3 class="font-bold text-green-600 mb-2 flex items-center gap-2">
                        <span class="w-6 h-6 bg-green-100 rounded flex items-center justify-center text-[10px]">3</span>
                        Testar Conexão
                    </h3>
                    <p class="text-[11px] text-gray-600 leading-relaxed">Após salvar os dados no painel, use o campo <b>"Número p/ Teste"</b> para enviar uma mensagem real ao seu celular e verificar se está tudo ok.</p>
                </div>

                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 text-blue-700">
                    <h4 class="font-bold text-xs uppercase mb-1">O que é automatizado?</h4>
                    <ul class="text-[10px] space-y-1 list-disc ml-4">
                        <li>Envio de PIX Copia e Cola na hora da reserva.</li>
                        <li>Confirmação de recebimento após o pagamento.</li>
                        <li>Notificação automática para o ganhador do sorteio.</li>
                    </ul>
                </div>
            </div>
            <button onclick="closeHelpEvolution()" class="w-full bg-gray-800 text-white font-bold py-3 rounded-xl mt-6 hover:bg-black transition-colors uppercase text-xs tracking-widest">Entendi</button>
        </div>
    </div>

    <!-- Modal Ajuda SMTP -->
    <div id="modal-help-smtp" class="fixed inset-0 bg-black bg-opacity-80 z-[60] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-lg w-full text-left shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button id="btn-close-help-smtp" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <h2 class="text-2xl font-black text-gray-800 mb-6">Como Configurar o Email?</h2>
            
            <div class="space-y-6">
                <div class="p-4 bg-orange-50 border-l-4 border-orange-400 text-orange-800 text-xs">
                    <strong>Atenção:</strong> Escolha <strong>UM</strong> servidor abaixo para usar os dados no seu formulário.
                </div>

                <div class="border-b pb-4">
                    <h3 class="font-bold text-indigo-600 mb-2 flex items-center gap-2">
                        <span class="w-6 h-6 bg-indigo-100 rounded flex items-center justify-center text-[10px]">1</span>
                        Hostinger (Recomendado se usar Titan)
                    </h3>
                    <ul class="text-[11px] text-gray-600 space-y-1 ml-8 list-disc">
                        <li><strong>Servidor:</strong> smtp.titan.email (ou o que estiver no seu guia Titan)</li>
                        <li><strong>Porta:</strong> 465 (ou 587 se usar TLS)</li>
                        <li><strong>Usuário:</strong> Seu email completo (ex: contato@seusite.com)</li>
                        <li><strong>Senha:</strong> A mesma senha do seu email</li>
                    </ul>
                </div>

                <div class="border-b pb-4">
                    <h3 class="font-bold text-blue-600 mb-2 flex items-center gap-2">
                        <span class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center text-[10px]">2</span>
                        Outlook / Hotmail
                    </h3>
                    <ul class="text-[11px] text-gray-600 space-y-1 ml-8 list-disc">
                        <li><strong>Servidor:</strong> smtp-mail.outlook.com</li>
                        <li><strong>Porta:</strong> 587</li>
                        <li><strong>Usuário:</strong> Seu e-mail completo</li>
                        <li><strong>Senha:</strong> Senha do e-mail (ou Senha de App se tiver 2 fatores)</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-bold text-red-600 mb-2 flex items-center gap-2">
                        <span class="w-6 h-6 bg-red-100 rounded flex items-center justify-center text-[10px]">3</span>
                        Gmail
                    </h3>
                    <ul class="text-[11px] text-gray-600 space-y-1 ml-8 list-disc">
                        <li><strong>Servidor:</strong> smtp.gmail.com</li>
                        <li><strong>Porta:</strong> 465</li>
                        <li><strong>Usuário:</strong> Seu Gmail</li>
                        <li><strong>Senha:</strong> Você precisa gerar uma <strong>"Senha de App"</strong> nas configurações de segurança da sua conta Google.</li>
                    </ul>
                </div>

                <div class="p-4 bg-gray-50 rounded-xl text-[10px] text-gray-500">
                    <p><strong>Dica:</strong> Se usar <strong>Hostinger Webmail</strong> comum, o servidor geralmente é <code>smtp.hostinger.com</code> na porta <code>465</code>.</p>
                </div>
            </div>
            
            <button id="btn-entendi-smtp" class="w-full bg-[#2c3e50] text-white font-black py-3 rounded-xl shadow uppercase text-xs mt-6 hover:bg-gray-800 transition-colors">Entendi e Voltar</button>
        </div>
    </div>

    <!-- Modal Config Popup -->
    <div id="modal-popup-config" class="fixed inset-0 bg-[#2c3e50]/80 backdrop-blur-sm z-[150] hidden opacity-0 transition-opacity duration-300 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm flex items-center gap-2">
                    <span class="w-8 h-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center italic">📣</span>
                    Popup de Entrada (Início)
                </h3>
                <button id="btn-close-popup-config" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="form-popup-config" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-2xl border border-orange-100">
                    <span class="text-xs font-black text-orange-700 uppercase">Status do Popup</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="popup-active" name="popup_active" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                    </label>
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase ml-1 mb-1 block">Título do Alerta</label>
                    <input type="text" id="popup-title" name="popup_title" placeholder="Ex: Grande Promoção!" class="w-full bg-gray-50 border border-gray-100 p-4 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase ml-1 mb-1 block">Conteúdo (Texto ou HTML)</label>
                    <textarea id="popup-content" name="popup_content" rows="4" placeholder="Descreva aqui o que será exibido no popup..." class="w-full bg-gray-50 border border-gray-100 p-4 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all"></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-1 mb-1 block">Texto do Botão</label>
                        <input type="text" id="popup-button" name="popup_button" placeholder="Entendi" class="w-full bg-gray-50 border border-gray-100 p-4 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-1 mb-1 block">Link (Opcional)</label>
                        <input type="text" id="popup-link" name="popup_link" placeholder="https://..." class="w-full bg-gray-50 border border-gray-100 p-4 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase ml-1 mb-1 block">Banner Promocional (Opcional)</label>
                    <div class="flex flex-col gap-2">
                        <input type="file" id="popup-image-file" name="popup_image_file" accept="image/*" class="text-xs font-bold text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 transition-all">
                        <div id="popup-image-preview" class="hidden h-32 w-full rounded-2xl border-2 border-dashed border-gray-100 bg-gray-50 overflow-hidden flex items-center justify-center relative group">
                            <img src="" id="popup-img-tag" class="h-full w-full object-cover">
                            <button type="button" onclick="clearImagePreview('popup-image-file', '', 'popup-img-tag', 'popup-image-preview', 'current-popup-image')" class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full shadow-lg hover:bg-red-600 z-10" title="Remover Imagem">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                        <input type="hidden" id="current-popup-image" name="current_popup_image">
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1 ml-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase block">Vídeo Promocional (Embed/URL - Opcional)</label>
                        <button type="button" onclick="document.getElementById('modal-help-video').classList.remove('hidden'); document.getElementById('modal-help-video').classList.add('flex')" class="w-4 h-4 bg-blue-100 text-blue-600 border border-blue-200 rounded-full flex items-center justify-center text-[8px] font-black shadow-sm hover:bg-blue-200 transition-colors">?</button>
                    </div>
                    <input type="text" id="popup-video" name="popup_video" placeholder="https://youtube.com/embed/..." class="w-full bg-gray-50 border border-gray-100 p-4 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none transition-all">
                </div>
            </form>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex gap-2">
                <button type="submit" form="form-popup-config" id="btn-save-popup" class="flex-1 bg-orange-500 text-white font-black py-4 rounded-2xl shadow-lg hover:bg-orange-600 transition-all uppercase tracking-widest text-xs">Salvar Configurações</button>
            </div>
        </div>
    </div>

    <!-- Modal Assistente -->
    <div id="modal-assistant" class="fixed inset-0 bg-black bg-opacity-80 z-[110] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl relative max-h-[90vh] overflow-y-auto scrollbar-thin">
            <button id="btn-close-assistant" class="absolute top-6 right-6 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <h2 class="text-2xl font-black text-[#8e44ad] mb-2 uppercase tracking-tight italic">CONFIGURAR ASSISTENTE</h2>
            <p class="text-xs text-gray-500 mb-8 font-medium">Personalize a identidade e o contato do seu bot de atendimento.</p>
            
            <form id="form-assistant" class="space-y-6">
                <div class="space-y-4">
                    <div class="bg-purple-50 p-4 rounded-2xl border border-purple-100">
                        <h3 class="font-bold text-purple-700 text-[11px] uppercase mb-4 tracking-widest">Identidade Visual</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Nome do Robô</label>
                                <input type="text" name="assistant_name" id="assistant_name" value="Assistente Top Sorte" class="w-full bg-white border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-purple-500 outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Nome do Atendente</label>
                                <input type="text" name="assistant_attendant" id="assistant_attendant" value="David" class="w-full bg-white border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-purple-500 outline-none shadow-sm">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Mensagem de Boas-Vindas</label>
                            <textarea name="assistant_welcome_message" id="assistant_welcome_message" rows="3" class="w-full bg-white border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-purple-500 outline-none shadow-sm resize-none" placeholder="Olá! 👋 Sou o assistente..."></textarea>
                             <p class="text-[9px] text-purple-400 mt-1 ml-1 font-bold italic">Dica: Use &lt;br&gt; para pular linha.</p>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-2xl border border-green-100">
                        <h3 class="font-bold text-green-700 text-[11px] uppercase mb-4 tracking-widest">Contato de Suporte</h3>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">WhatsApp Atendente (Cód + DDD + Num)</label>
                            <input type="text" name="assistant_whatsapp" id="assistant_whatsapp" value="5511999999999" placeholder="5511999999999" class="w-full bg-white border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-green-500 outline-none shadow-sm">
                            <p class="text-[10px] text-green-600/70 mt-2 ml-1 font-bold">Importante: Use apenas números, incluindo o código do país (55).</p>
                        </div>
                    </div>

                    <!-- Gemini AI Section -->
                    <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                        <h3 class="font-bold text-blue-700 text-[11px] uppercase mb-4 tracking-widest">Inteligência Artificial (Google Gemini)</h3>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-1 block mb-1">Crie sua Chave Gemini API</label>
                            <div class="flex gap-2">
                                <input type="password" name="gemini_api_key" id="gemini_api_key" class="w-full bg-white border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm" placeholder="Cole sua chave aqui...">
                                <button type="button" onclick="toggleVisibility('gemini_api_key')" class="bg-white border border-gray-200 px-3 rounded-xl hover:bg-gray-50">
                                    <svg class="w-4 h-4 text-gray-400 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </button>
                            </div>
                            <p class="text-[9px] text-blue-500 mt-2 ml-1 font-bold italic">
                                💡 Chave Grátis (IA Conversacional): 
                                <a href="https://aistudio.google.com/app/apikey" target="_blank" class="underline decoration-dotted">Clique aqui para gerar sua chave</a>
                            </p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-700 text-[11px] uppercase tracking-widest">Respostas Automáticas</h3>
                            <button type="button" id="btn-add-assistant-msg" class="bg-purple-600 text-white text-[10px] font-black px-3 py-1 rounded-lg uppercase shadow-sm hover:bg-purple-700 transition-colors">Nova Resposta</button>
                        </div>
                        <div id="assistant-messages-list" class="space-y-3">
                            <!-- Injected JS messages -->
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" id="btn-save-assistant" class="flex-1 bg-purple-600 text-white font-black py-4 rounded-2xl shadow-lg hover:bg-purple-700 transition-colors uppercase text-xs tracking-widest">Salvar Dados do Robô</button>
                    <button type="button" id="btn-close-assistant-footer" class="bg-gray-100 text-gray-500 font-black py-4 px-6 rounded-2xl shadow hover:bg-gray-200 transition-colors uppercase text-xs tracking-widest">Sair</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Mensagem Assistente -->
    <div id="modal-assistant-msg" class="fixed inset-0 bg-black bg-opacity-90 z-[120] hidden flex items-center justify-center p-4 backdrop-blur-md transition-opacity duration-300">
        <div class="bg-white rounded-[2rem] p-8 max-w-sm w-full shadow-2xl relative">
            <h2 id="msg-modal-title" class="text-xl font-black text-[#8e44ad] mb-6 uppercase italic tracking-tighter">EDITAR RESPOSTA</h2>
            <form id="form-assistant-msg" class="space-y-4 text-left">
                <input type="hidden" name="msg_id" id="msg_id">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase ml-1">Pergunta do Botão</label>
                    <input type="text" name="msg_pergunta" id="msg_pergunta" required class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase ml-1">Resposta do Chat</label>
                    <textarea name="msg_resposta" id="msg_resposta" required rows="4" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-purple-500 outline-none resize-none"></textarea>
                    <p class="text-[9px] text-gray-400 mt-1">Dica: Use <code>&lt;br&gt;</code> para pular linha.</p>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 bg-purple-600 text-white font-black py-4 rounded-2xl shadow-lg hover:bg-purple-700 uppercase text-[10px] tracking-widest">Salvar Resposta</button>
                    <button type="button" id="btn-close-msg-modal" class="bg-gray-100 text-gray-500 font-black py-4 px-4 rounded-2xl uppercase text-[10px] tracking-widest">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ajuda Vídeo Popup -->
    <div id="modal-help-video" class="fixed inset-0 bg-[#2c3e50]/80 backdrop-blur-sm z-[200] hidden items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] p-8 max-w-sm w-full shadow-2xl relative animate-in fade-in zoom-in duration-300">
            <h2 class="text-xl font-black text-gray-800 mb-4 flex items-center gap-2 uppercase tracking-tighter italic">
                <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center not-italic">?</span>
                Como pegar o link?
            </h2>
            <div class="space-y-4 text-xs font-medium text-gray-500 leading-relaxed">
                <p>Para exibir um vídeo no popup, você precisa do link de <strong>incorporação (embed)</strong>:</p>
                <ol class="list-decimal ml-4 space-y-2">
                    <li>No YouTube, clique no botão <strong>Compartilhar</strong>.</li>
                    <li>Escolha a opção <strong>Incorporar (Embed)</strong>.</li>
                    <li>Copie apenas o link que está dentro das aspas do <code>src="..."</code>.</li>
                </ol>
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 font-mono text-[10px] break-all">
                    Exemplo correto:<br>
                    <span class="text-blue-600">https://www.youtube.com/embed/XXXXX</span>
                </div>
            </div>
            <button onclick="document.getElementById('modal-help-video').classList.add('hidden')" class="w-full bg-[#2c3e50] text-white font-black py-4 rounded-2xl shadow-lg mt-6 uppercase text-[10px] tracking-widest hover:bg-black transition-all">Entendi</button>
        </div>
    </div>

    <!-- Modal Ajuda Template -->
    <div id="modal-help-template" class="fixed inset-0 bg-black bg-opacity-80 z-[60] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-lg w-full text-left shadow-2xl relative">
            <button onclick="closeHelpTemplate()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <h2 class="text-2xl font-black text-gray-800 mb-6">Como usar o Template?</h2>
            
            <div class="space-y-6">
                <div class="p-4 bg-purple-50 border-l-4 border-purple-400 text-purple-800 text-[11px] leading-relaxed">
                    Personalize a mensagem que os afiliados enviam. Use as <b>"Tags"</b> abaixo para preencher os dados reais automaticamente:
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="flex items-center gap-2">
                        <code class="bg-gray-100 px-1.5 py-0.5 rounded text-purple-600 font-bold text-[10px]">{rifa_nome}</code>
                        <span class="text-[9px] text-gray-500">Nome da Rifa</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-gray-100 px-1.5 py-0.5 rounded text-purple-600 font-bold text-[10px]">{link}</code>
                        <span class="text-[9px] text-gray-500">Link Afiliado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-gray-100 px-1.5 py-0.5 rounded text-purple-600 font-bold text-[10px]">{preco}</code>
                        <span class="text-[9px] text-gray-500">Preço</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-gray-100 px-1.5 py-0.5 rounded text-purple-600 font-bold text-[10px]">{premio1}</code>
                        <span class="text-[9px] text-gray-500">1º Prêmio</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-gray-100 px-1.5 py-0.5 rounded text-purple-600 font-bold text-[10px]">{premio2}</code>
                        <span class="text-[9px] text-gray-500">2º Prêmio</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-gray-100 px-1.5 py-0.5 rounded text-purple-600 font-bold text-[10px]">{premio3}..5</code>
                        <span class="text-[9px] text-gray-500">Demais Prêmios</span>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase mb-2">Exemplo Prático:</p>
                    <div class="bg-gray-50 p-4 rounded-xl space-y-3">
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase mb-1">Se você preencher assim:</p>
                            <p class="text-[9px] bg-white p-2 rounded border border-gray-200 font-mono italic">🚨 *RIFA TOP* 🚨&#10;Apenas {preco}!&#10;1º: {premio1}&#10;2º: {premio2}&#10;{link}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-green-600 uppercase mb-1">No WhatsApp aparecerá:</p>
                            <p class="text-[9px] bg-white p-2 rounded border border-green-100 font-medium whitespace-pre-wrap">🚨 <b>RIFA TOP</b> 🚨&#10;Apenas R$ 0,10!&#10;1º: iPhone 16&#10;2º: R$ 500 no PIX&#10;seusite.com/rifa.php?id=1&ref=12</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 rounded-xl text-[10px] text-gray-500">
                    <p><b>Dica:</b> No WhatsApp, use <code>*texto em negrito*</code> e emojis para chamar atenção!</p>
                </div>
            </div>
            
            <button onclick="closeHelpTemplate()" class="w-full bg-[#2c3e50] text-white font-black py-4 rounded-xl shadow uppercase text-sm mt-6 hover:bg-gray-800 transition-colors">Entendi</button>
        </div>
    </div>


    <!-- Modal Segurança & Logs -->
    <div id="modal-security-monitor" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 md:p-8 max-w-4xl w-full text-left shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button onclick="closeSecurityMonitor()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-red-100 rounded-lg text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-800 uppercase tracking-tighter">Monitor de Segurança</h2>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Logs de Atividade e Acessos Real-time</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-center">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase mb-3">Quem está Online</h3>
                    <div id="security-online-list" class="space-y-2">
                        <!-- Injected JS -->
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 md:col-span-2">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase mb-3">Páginas mais Acessadas</h3>
                    <div id="security-top-pages" class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[10px] font-bold text-gray-600">
                        <!-- Injected JS -->
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase">Últimas Atividades</h3>
                    <div class="flex flex-wrap gap-2 items-center">
                        <select id="filter-log-cat" class="bg-gray-50 border border-gray-200 rounded-lg p-2 text-[10px] font-bold text-gray-600 outline-none">
                            <option value="">TODAS CATEGORIAS</option>
                            <option value="acao_admin">ADMINISTRATIVO</option>
                            <option value="acao_afiliado">AFILIADOS</option>
                            <option value="acesso_site">VISITANTES</option>
                        </select>
                        <input type="text" id="filter-log-ip" placeholder="PESQUISAR IP..." class="bg-gray-50 border border-gray-200 rounded-lg p-2 text-[10px] font-bold text-gray-600 outline-none w-32 uppercase">
                        <button onclick="fetchSecurityStats()" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-[10px] font-black uppercase hover:bg-indigo-700 transition-colors">Filtrar</button>
                    </div>
                </div>
                <div class="overflow-x-auto overflow-y-auto max-h-[300px] border border-gray-50 rounded-xl">
                    <table class="w-full text-left text-[11px]">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase">
                                <th class="pb-3 pr-4">Data/Hora</th>
                                <th class="pb-3 pr-4">IP / Local</th>
                                <th class="pb-3 pr-4">Categoria</th>
                                <th class="pb-3">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="security-logs-tbody" class="divide-y divide-gray-50">
                            <!-- Injected JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Notificação -->
    <div id="modal-notif" class="fixed inset-0 bg-black bg-opacity-80 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-[2rem] p-8 max-w-sm w-full text-center shadow-2xl relative border border-gray-100">
            <h2 id="notif-title" class="text-2xl font-black text-[#2c3e50] mb-4 uppercase tracking-tight italic">$UPER$ORTE</h2>
            <p id="notif-message" class="text-sm text-gray-500 mb-8 font-medium leading-relaxed">Informação aqui.</p>
            <button id="btn-close-notif" class="w-full bg-[#8e44ad] text-white font-black py-4 rounded-2xl shadow-lg uppercase text-xs tracking-widest hover:bg-[#7d3c98] transition-all">Entendido</button>
        </div>
    </div>

    <!-- Modal Alerta de Segurança (Invasão/Erro) -->
    <div id="modal-security-alert" class="fixed inset-0 bg-black bg-opacity-90 z-[110] hidden flex items-center justify-center p-4 backdrop-blur-md transition-opacity duration-500">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-sm w-full text-center shadow-[0_0_50px_rgba(239,68,68,0.4)] relative border-4 border-red-500 animate-pulse">
            <div class="mx-auto w-24 h-24 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-6 ring-8 ring-red-50 shadow-inner">
                <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <h2 class="text-2xl font-black text-red-700 mb-2 uppercase italic tracking-tighter">ALERTA DE SEGURANÇA!</h2>
            <p id="security-alert-msg" class="text-sm text-gray-600 mb-8 font-bold leading-relaxed">Detectamos múltiplas tentativas de acesso inválidas ao sistema administrativo recentemente.</p>
            <div class="flex flex-col gap-3">
                <button onclick="openSecurityMonitorAndCloseAlert()" class="w-full bg-red-600 text-white font-black py-4 rounded-2xl shadow-lg uppercase text-xs hover:bg-red-700 transition-all transform hover:scale-105">Ver Logs de Atividade</button>
                <button onclick="closeSecurityAlert()" class="w-full bg-gray-100 text-gray-400 font-bold py-3 rounded-2xl uppercase text-[10px] hover:bg-gray-200 transition-colors">Ignorar Aviso</button>
            </div>
        </div>
    </div>

    <script>
        const API = '../backend/api/admin.php';

        function showNotification(title, message, type = 'success', callback = null) {
            const modal = document.getElementById('modal-notif');
            document.getElementById('notif-title').textContent = title === 'Erro' || title === 'Atenção' ? 'ATENÇÃO' : title;
            document.getElementById('notif-message').textContent = message;
            
            const btnClose = document.getElementById('btn-close-notif');

            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.add('opacity-100'), 10);

            btnClose.onclick = () => {
                modal.classList.remove('opacity-100');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    if(callback) callback();
                }, 300);
            };
        }

        let currentPage = 1;
        let currentStatus = '';
        let serverTimeOffset = 0;
        let tempoPagamento = 3;
        let countdowns = [];
        let reservationTimerInterval = null;
        let secondsLeft = 0;
        let sessionTimerInterval = null;

        async function fetchStats(page = 1) {
            currentPage = page;
            try {
                const ts = new Date().getTime();
                const res = await fetch(`${API}?action=stats&page=${page}&status=${currentStatus}&_=${ts}`);
                const data = await res.json();

                if (data.error && data.expired) {
                    showNotification('Sessão expirada', 'Sua sessão administrativa expirou por segurança. Por favor, entre novamente.', 'error', () => {
                        window.location.href = 'login.php';
                    });
                    return;
                }
                if (data.error) {
                    window.location.href = 'login.php';
                    return;
                }

                // Server Time Sync
                const sTime = new Date(data.server_time).getTime();
                const cTime = new Date().getTime();
                serverTimeOffset = sTime - cTime;
                console.log("Sync Admin:", { server: data.server_time, client: new Date().toISOString(), offset: serverTimeOffset });
                tempoPagamento = data.tempo_pagamento;
                countdowns = [];

                document.getElementById('stat-livre').textContent = data.stats['disponivel'] || 0;
                document.getElementById('stat-reservado').textContent = data.stats['reservado'] || 0;
                document.getElementById('stat-pago').textContent = data.stats['pago'] || 0;
                document.getElementById('stat-faturamento').textContent = parseFloat(data.faturamento).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                document.getElementById('stat-taxas').textContent = parseFloat(data.total_repassado).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                if(data.expires_in) {
                    secondsLeft = parseInt(data.expires_in);
                    startTimer();
                }

                if (document.getElementById('maintenance-toggle')) {
                    document.getElementById('maintenance-toggle').checked = (data.maintenance === '1');
                }

                if (document.getElementById('assistant-toggle')) {
                    document.getElementById('assistant-toggle').checked = (data.assistant.enabled === '1');
                }

                // Populate Assistant (only if modal is closed)
                const modalAssistant = document.getElementById('modal-assistant');
                if(modalAssistant && modalAssistant.classList.contains('hidden') && data.assistant) {
                    if(document.getElementById('assistant_name')) document.getElementById('assistant_name').value = data.assistant.name || '';
                    if(document.getElementById('assistant_attendant')) document.getElementById('assistant_attendant').value = data.assistant.attendant || '';
                    if(document.getElementById('assistant_whatsapp')) document.getElementById('assistant_whatsapp').value = data.assistant.whatsapp || '';
                    if(document.getElementById('assistant_welcome_message')) document.getElementById('assistant_welcome_message').value = data.assistant.welcome_message || '';
                    if(document.getElementById('gemini_api_key')) document.getElementById('gemini_api_key').value = data.assistant.gemini_api_key || '';
                    
                    const msgList = document.getElementById('assistant-messages-list');
                    if(msgList) {
                        msgList.innerHTML = '';
                        (data.assistant.messages || []).forEach(m => {
                            msgList.insertAdjacentHTML('beforeend', `
                                <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-gray-100 shadow-sm group">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[10px] font-black text-purple-600 uppercase truncate">${m.pergunta}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" onclick='editAssistantMsg(${JSON.stringify(m).replace(/'/g, "&apos;")})' class="text-gray-400 hover:text-blue-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                                        <button type="button" onclick="deleteAssistantMsg(${m.id})" class="text-gray-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                    </div>
                                </div>
                            `);
                        });
                    }
                }

                // Populate Acesso (only if modal is closed)
                const modalSecurity = document.getElementById('modal-security');
                if(modalSecurity && modalSecurity.classList.contains('hidden')) {
                    if(document.getElementById('new-admin-user')) document.getElementById('new-admin-user').value = data.admin_user || '';
                    if(document.getElementById('new-admin-email')) document.getElementById('new-admin-email').value = data.admin_email || '';
                }

                // Populate SMTP (only if modal is closed)
                const modalSMTP = document.getElementById('modal-smtp');
                if(modalSMTP && modalSMTP.classList.contains('hidden') && data.email_config) {
                    if(document.getElementById('smtp-host')) document.getElementById('smtp-host').value = data.email_config.smtp_host || '';
                    if(document.getElementById('smtp-port')) document.getElementById('smtp-port').value = data.email_config.smtp_port || '';
                    if(document.getElementById('smtp-user')) document.getElementById('smtp-user').value = data.email_config.smtp_user || '';
                    if(document.getElementById('smtp-pass')) document.getElementById('smtp-pass').value = data.email_config.smtp_pass || '';
                    if(document.getElementById('smtp-from-name')) document.getElementById('smtp-from-name').value = data.email_config.smtp_from_name || '';
                    if(document.getElementById('smtp-from-email')) document.getElementById('smtp-from-email').value = data.email_config.smtp_from_email || '';
                }

                const tbody = document.getElementById('table-reservas');
                tbody.innerHTML = '';

                if (data.reservas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-gray-400 font-medium">Nenhuma reserva encontrada.</td></tr>';
                    return;
                }

                data.reservas.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b hover:bg-gray-50';

                    let bgStatus = 'bg-gray-100 text-gray-600';
                    let timerHtml = '';

                    if (r.status === 'pago') bgStatus = 'bg-purple-100 text-purple-700';
                    else if (r.status === 'pendente') {
                        bgStatus = 'bg-yellow-100 text-yellow-700';
                        // Calc expiry
                        const dataReserva = new Date(r.data_reserva_iso).getTime();
                        const expiry = dataReserva + (tempoPagamento * 60 * 1000);
                        const idTimer = `timer-${r.id}`;
                        timerHtml = `<div id="${idTimer}" class="text-[9px] font-black text-red-500 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="timer-display">--:--</span>
                        </div>`;
                        countdowns.push({ id: idTimer, expiry: expiry });
                    }

                    let btn = r.status === 'pendente'
                        ? `<button onclick="markPaid(${r.id})" class="text-xs bg-green-500 text-white px-3 py-1 rounded shadow hover:bg-green-600 focus:outline-none transition-colors">Marcar Pago</button>`
                        : `<span class="text-xs text-gray-400">—</span>`;

                    tr.innerHTML = `
                        <td class="p-4 align-top">
                            <div class="font-bold text-gray-800">#${r.id}</div>
                            <div class="text-xs mt-1 truncate max-w-[150px] shadow-sm bg-blue-50 text-blue-700 border border-blue-100 px-2 py-1 rounded inline-block font-bold" title="${r.rifa_nome || 'N/A'}">#${r.rifa_id}: ${r.rifa_nome || 'N/A'}</div>
                        </td>
                        <td class="p-4 align-top text-gray-700 font-semibold text-sm">
                            <span class="bg-gray-100 border border-gray-200 px-2 py-1 rounded inline-block truncate max-w-[150px]" title="${r.nome}">${r.nome}</span>
                        </td>
                        <td class="p-4 font-mono text-xs text-[#00a650] align-top whitespace-nowrap">${r.whatsapp}</td>
                        <td class="p-4 text-sm font-bold text-gray-700 align-top">${parseFloat(r.valor_total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
                        <td class="p-4 align-top">
                            <span class="px-2 py-1 rounded text-[10px] uppercase font-bold tracking-wider ${bgStatus}">${r.status}</span>
                            ${timerHtml}
                        </td>
                        <td class="p-4 text-right align-top">${btn}</td>
                    `;
                    tbody.appendChild(tr);
                });

                renderPagination(data.total_pages, data.current_page);
                startGlobalTimer();
                
                // Alert for recent failed attempts
                checkSecurityAlerts(data.failed_recent || 0);

            } catch (e) {
                console.error(e);
            }
        }

        function startGlobalTimer() {
            if (reservationTimerInterval) clearInterval(reservationTimerInterval);
            updateCountdowns();
            reservationTimerInterval = setInterval(updateCountdowns, 1000);
        }

        function startTimer() {
            if (sessionTimerInterval) clearInterval(sessionTimerInterval);
            const display = document.getElementById('session-timer');
            if(!display) return;
            display.classList.remove('hidden');

            sessionTimerInterval = setInterval(() => {
                secondsLeft--;
                if (secondsLeft <= 0) {
                    clearInterval(sessionTimerInterval);
                    display.textContent = "EXPIRADO!";
                    showNotification('Sessão expirada', 'Sua sessão administrativa expirou por segurança. Fazendo logout...', 'error', () => {
                        window.location.href = 'login.php';
                    });
                    return;
                }

                const mins = Math.floor(secondsLeft / 60);
                const secs = secondsLeft % 60;
                display.textContent = `EXPIRA EM: ${mins}:${secs.toString().padStart(2, '0')}`;
            }, 1000);
        }

        function updateCountdowns() {
            const now = new Date().getTime() + serverTimeOffset;

            countdowns.forEach(c => {
                const el = document.getElementById(c.id);
                if (!el) return;

                const diff = c.expiry - now;
                const display = el.querySelector('.timer-display');

                if (diff <= 30000) { // Menos de 30 segundos ou já expirado
                    display.textContent = 'EXPIRANDO...';
                    el.classList.add('animate-bounce');
                } else {
                    const min = Math.floor(diff / 60000);
                    const sec = Math.floor((diff % 60000) / 1000);
                    display.textContent = `${String(min).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
                    el.classList.remove('animate-bounce');
                }
            });
        }

        function renderPagination(totalPages, current) {
            const container = document.getElementById('pagination-reservas');
            container.innerHTML = '';

            if (totalPages <= 1) return;

            // Simple Pagination: Previous, Page Numbers, Next
            if (current > 1) {
                const btnPrev = document.createElement('button');
                btnPrev.className = 'px-3 py-1 bg-white border border-gray-300 rounded text-xs font-bold text-gray-600 hover:bg-gray-100';
                btnPrev.textContent = 'Anterior';
                btnPrev.onclick = () => fetchStats(current - 1);
                container.appendChild(btnPrev);
            }

            // Show max 5 pages around current
            let start = Math.max(1, current - 2);
            let end = Math.min(totalPages, start + 4);
            if (end === totalPages) start = Math.max(1, end - 4);

            for (let i = start; i <= end; i++) {
                const btn = document.createElement('button');
                btn.className = `px-3 py-1 rounded text-xs font-bold ${i === current ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-100'}`;
                btn.textContent = i;
                btn.onclick = () => fetchStats(i);
                container.appendChild(btn);
            }

            if (current < totalPages) {
                const btnNext = document.createElement('button');
                btnNext.className = 'px-3 py-1 bg-white border border-gray-300 rounded text-xs font-bold text-gray-600 hover:bg-gray-100';
                btnNext.textContent = 'Próximo';
                btnNext.onclick = () => fetchStats(current + 1);
                container.appendChild(btnNext);
            }
        }

        async function markPaid(id) {
            if (!confirm('Marcar esta reserva como PAGA manualmente?')) return;
            const fd = new URLSearchParams();
            fd.append('action', 'mark_paid');
            fd.append('id', id);

            await fetch(API, { method: 'POST', body: fd });
            fetchStats(currentPage);
        }

        function setStatusFilter(status) {
            currentStatus = status;
            currentPage = 1;

            // UI Update
            document.querySelectorAll('.status-tab').forEach(tab => {
                tab.classList.remove('bg-gray-200', 'text-gray-700');
                tab.classList.add('bg-white', 'border', 'border-gray-200', 'text-gray-500');
            });

            const activeId = status === '' ? 'tab-all' : `tab-${status}`;
            const activeTab = document.getElementById(activeId);
            activeTab.classList.remove('bg-white', 'border', 'border-gray-200', 'text-gray-500');
            activeTab.classList.add('bg-gray-200', 'text-gray-700');

            fetchStats();
        }


        // Affiliates Logic
        document.getElementById('btn-affiliates').addEventListener('click', () => {
            const m = document.getElementById('modal-affiliates');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
            fetchAffiliates();
        });

        async function fetchAffiliates() {
            const res = await fetch(`${API}?action=get_affiliates`);
            const data = await res.json();
            const tbody = document.getElementById('table-affiliates');
            tbody.innerHTML = '';

            data.affiliates.forEach(af => {
                const tr = document.createElement('tr');
                tr.className = 'border-b hover:bg-gray-50';
                
                let btnAttr = af.can_payout ? '' : 'disabled';
                let btnClass = af.can_payout 
                    ? 'bg-green-500 text-white hover:bg-green-600' 
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed';
                
                let cycleMsg = af.can_payout 
                    ? '<span class="text-[10px] text-green-600 font-bold">✓ Ciclo Completo</span>' 
                    : `<span class="text-[10px] text-red-400">Faltam ${af.days_remaining} dias</span>`;

                tr.innerHTML = `
                    <td class="p-3">
                        <div class="font-bold">${af.nome}</div>
                        <div class="text-[10px] text-gray-400">${af.whatsapp}</div>
                    </td>
                    <td class="p-3 font-bold">${af.vendas_pagas}</td>
                    <td class="p-3 font-bold text-[#8e44ad]">R$ ${parseFloat(af.saldo).toFixed(2).replace('.', ',')}</td>
                    <td class="p-3 text-gray-400">R$ ${parseFloat(af.total_ganho).toFixed(2).replace('.', ',')}</td>
                    <td class="p-3 space-y-1 text-center">
                        <div class="flex flex-col gap-1 items-center">
                            <button onclick="viewAffiliateSales(${af.id}, '${af.nome}')" class="text-[10px] bg-blue-50 text-blue-600 px-2 py-1 rounded-lg border border-blue-100 hover:bg-blue-100 font-bold w-32">Ver Detalhes</button>
                            ${cycleMsg}
                            <button onclick="payoutAffiliate(${af.id}, '${af.nome}', ${af.saldo})" ${btnAttr} class="text-[10px] font-black px-2 py-2 rounded-lg w-32 transition-all ${btnClass}">
                                PAGAR AGORA
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        async function viewAffiliateSales(id, nome) {
            document.getElementById('af-sales-title').innerText = `Vendas de ${nome}`;
            const res = await fetch(`${API}?action=get_affiliate_sales&id=${id}`);
            const data = await res.json();
            
            const tbody = document.getElementById('table-af-sales-body');
            tbody.innerHTML = '';

            data.sales.forEach(s => {
                const tr = document.createElement('tr');
                tr.className = 'border-b hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="p-3 text-gray-400 text-[10px]">${new Date(s.data_reserva).toLocaleDateString()}</td>
                    <td class="p-3 font-bold">${s.comprador}</td>
                    <td class="p-3 text-gray-500">${s.rifa_nome}</td>
                    <td class="p-3 font-mono text-[9px] text-indigo-600 leading-tight">${s.numeros}</td>
                    <td class="p-3 text-right font-bold">R$ ${parseFloat(s.valor_total).toFixed(2).replace('.', ',')}</td>
                `;
                tbody.appendChild(tr);
            });

            const m = document.getElementById('modal-af-sales');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
        }

        async function payoutAffiliate(id, nome, saldo) {
            if (!confirm(`Confirmar fechamento de ciclo e pagamento de R$ ${parseFloat(saldo).toFixed(2).replace('.', ',')} para ${nome} via PIX?\n\nO saldo do afiliado será zerado e registrado um saque pendente.`)) return;
            
            const fd = new URLSearchParams();
            fd.append('action', 'payout_affiliate');
            fd.append('id', id);

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            
            if(data.success) {
                showNotification('Ciclo Fechado!', data.message, 'success');
                fetchAffiliates();
                fetchStats();
            } else {
                showNotification('Erro', data.error, 'error');
            }
        }

        async function openPendingPayouts() {
            const res = await fetch(`${API}?action=get_pending_payouts`);
            const data = await res.json();
            const tbody = document.getElementById('table-pending-payouts');
            tbody.innerHTML = '';

            if (data.payouts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="p-10 text-center text-gray-400 font-bold">Nenhum saque pendente no momento! 🎉</td></tr>';
            }

            data.payouts.forEach(p => {
                const tr = document.createElement('tr');
                tr.className = 'border-b hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="p-3">
                        <div class="font-bold text-indigo-700 uppercase">${p.afiliado_nome}</div>
                        <div class="bg-gray-50 p-2 rounded-lg mt-1 flex items-center justify-between group">
                            <code class="text-[11px] font-black">${p.chave_pix}</code>
                            <button onclick="navigator.clipboard.writeText('${p.chave_pix}'); showNotification('Copiado!', 'Chave Pix copiada', 'success')" class="text-[9px] bg-white border px-1.5 py-1 rounded shadow-sm opacity-0 group-hover:opacity-100 transition-opacity uppercase font-bold">Copiar</button>
                        </div>
                        <div class="text-[9px] text-gray-400 mt-1">Solicitado em: ${new Date(p.data_solicitacao).toLocaleString()}</div>
                    </td>
                    <td class="p-3">
                        <div class="text-lg font-black text-green-600">R$ ${parseFloat(p.valor).toFixed(2).replace('.', ',')}</div>
                    </td>
                    <td class="p-3 text-center">
                        <button onclick="confirmPayoutPaid(${p.id}, ${p.valor})" class="bg-[#00a650] text-white font-black px-4 py-3 rounded-2xl text-[10px] hover:bg-[#009647] shadow transition-all uppercase">
                            Marcar como Pago
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            const m = document.getElementById('modal-pending-payouts');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
        }

        async function confirmPayoutPaid(id, valor) {
            if (!confirm(`Você confirma que já realizou o PIX no valor de R$ ${parseFloat(valor).toFixed(2).replace('.', ',')} para este afiliado?\n\nEsta ação não pode ser desfeita.`)) return;
            
            const fd = new URLSearchParams();
            fd.append('action', 'confirm_payout');
            fd.append('id', id);

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            
            if(data.success) {
                showNotification('Sucesso!', data.message, 'success');
                openPendingPayouts(); // Refresh list
                fetchAffiliates();    // Refresh totals
            } else {
                showNotification('Erro', data.error, 'error');
            }
        }

        // Helper to close specific modal
        window.closeModal = (id) => {
            const m = document.getElementById(id);
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        };

        // Billing Logic
        document.getElementById('btn-billing').addEventListener('click', () => {
            const m = document.getElementById('modal-billing');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
        });

        document.getElementById('btn-close-billing').addEventListener('click', () => {
            const m = document.getElementById('modal-billing');
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        });

        // Security Modal
        document.getElementById('btn-security').addEventListener('click', () => {
            const m = document.getElementById('modal-security');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
        });

        document.getElementById('btn-close-security').addEventListener('click', () => {
            const m = document.getElementById('modal-security');
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        });

        document.getElementById('form-security').addEventListener('submit', async (e) => {
            e.preventDefault();
            const user = document.getElementById('new-admin-user').value;
            const email = document.getElementById('new-admin-email').value;
            const pass = document.getElementById('new-admin-pass').value;
            const btn = document.getElementById('btn-save-security');
            btn.innerHTML = 'Salvando...';

            const fd = new URLSearchParams();
            fd.append('action', 'update_access');
            fd.append('user', user);
            fd.append('email', email);
            fd.append('pass', pass);

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            
            if(data.success) {
                if(pass.trim() !== "") {
                   showNotification('Sucesso!', 'Acesso atualizado! Você será deslogado para entrar com a nova senha.', 'success', () => {
                       window.location.href = '../backend/api/logout.php';
                   });
                } else {
                   showNotification('Sucesso!', 'Acesso atualizado com sucesso.', 'success');
                   btn.innerHTML = 'Atualizar Acesso';
                   fetchStats(currentPage);
                }
            } else {
                showNotification('Erro', data.error, 'error');
                btn.innerHTML = 'Atualizar Acesso';
            }
        });

        // SMTP Modal
        document.getElementById('btn-open-smtp').addEventListener('click', () => {
            const m = document.getElementById('modal-smtp');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
        });

        document.getElementById('btn-close-smtp').addEventListener('click', () => {
            const m = document.getElementById('modal-smtp');
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        });

        document.getElementById('form-smtp').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-save-smtp');
            btn.innerHTML = 'Salvando...';

            const fd = new URLSearchParams(new FormData(e.target));
            fd.append('action', 'save_smtp');

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            
            if(data.success) {
                showNotification('Sucesso!', 'Parâmetros de email salvos com sucesso.');
                btn.innerHTML = 'Salvar Configuração';
            } else {
                showNotification('Erro', data.error, 'error');
                btn.innerHTML = 'Salvar Configuração';
            }
        });

        // SMTP Help Modal
        document.getElementById('btn-help-smtp').addEventListener('click', () => {
            const m = document.getElementById('modal-help-smtp');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
        });

        // Evolution Help Modal
        document.getElementById('btn-help-evolution').addEventListener('click', () => {
            const m = document.getElementById('modal-help-evolution');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
        });

        window.closeHelpEvolution = () => {
            const m = document.getElementById('modal-help-evolution');
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        };

        // Template Help Modal
        document.getElementById('btn-help-template').addEventListener('click', () => {
            const m = document.getElementById('modal-help-template');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
        });

        window.closeHelpTemplate = () => {
            const m = document.getElementById('modal-help-template');
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        };

        const closeHelp = () => {
            const m = document.getElementById('modal-help-smtp');
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        };
        document.getElementById('btn-close-help-smtp').addEventListener('click', closeHelp);
        document.getElementById('btn-entendi-smtp').addEventListener('click', closeHelp);

        // Assistant Modal Logic
        document.getElementById('btn-open-assistant').addEventListener('click', () => {
            const m = document.getElementById('modal-assistant');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
        });

        const closeAssistant = () => {
            const m = document.getElementById('modal-assistant');
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        };

        document.getElementById('btn-close-assistant').addEventListener('click', closeAssistant);
        document.getElementById('btn-close-assistant-footer').addEventListener('click', closeAssistant);

        document.getElementById('form-assistant').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-save-assistant');
            btn.innerHTML = 'Salvando...';

            const fd = new URLSearchParams(new FormData(e.target));
            fd.append('action', 'save_assistant');

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            
            if(data.success) {
                closeAssistant();
                setTimeout(() => {
                    showNotification('Sucesso!', 'Dados do assistente salvos com sucesso.');
                    btn.innerHTML = 'Salvar Dados do Robô';
                    fetchStats();
                }, 300);
            } else {
                showNotification('Erro', data.error, 'error');
                btn.innerHTML = 'Salvar Dados do Robô';
            }
        });

        document.getElementById('assistant-toggle').addEventListener('change', async (e) => {
            const fd = new URLSearchParams();
            fd.append('action', 'toggle_assistant');
            fd.append('enabled', e.target.checked ? '1' : '0');
            await fetch(API, { method: 'POST', body: fd });
            showNotification('Atualizado!', `O assistente foi ${e.target.checked ? 'ativado' : 'desativado'} com sucesso.`);
        });

        // Maintenance Toggle logic
        if(document.getElementById('maintenance-toggle')) {
            document.getElementById('maintenance-toggle').addEventListener('change', async (e) => {
                const fd = new URLSearchParams();
                fd.append('action', 'set_maintenance');
                fd.append('status', e.target.checked ? '1' : '0');
                await fetch(API, { method: 'POST', body: fd });
                showNotification('Atualizado!', `O modo manutenção foi ${e.target.checked ? 'ativado' : 'desativado'} com sucesso.`);
            });
        }

        // Assistant Messages CRUD Logic
        document.getElementById('btn-add-assistant-msg').addEventListener('click', () => {
            document.getElementById('msg_id').value = '0';
            document.getElementById('msg_pergunta').value = '';
            document.getElementById('msg_resposta').value = '';
            document.getElementById('msg-modal-title').innerText = 'NOVA RESPOSTA';
            const m = document.getElementById('modal-assistant-msg');
            m.classList.remove('hidden');
        });

        window.editAssistantMsg = function(msg) {
            document.getElementById('msg_id').value = msg.id;
            document.getElementById('msg_pergunta').value = msg.pergunta;
            document.getElementById('msg_resposta').value = msg.resposta;
            document.getElementById('msg-modal-title').innerText = 'EDITAR RESPOSTA';
            const m = document.getElementById('modal-assistant-msg');
            m.classList.remove('hidden');
        };

        window.deleteAssistantMsg = async function(id) {
            if(!confirm('Deseja excluir esta resposta?')) return;
            const fd = new URLSearchParams();
            fd.append('action', 'delete_assistant_msg');
            fd.append('id', id);
            const res = await fetch(API, { method: 'POST', body: fd });
            if((await res.json()).success) {
                fetchStats();
            }
        };

        document.getElementById('btn-close-msg-modal').addEventListener('click', () => {
            document.getElementById('modal-assistant-msg').classList.add('hidden');
        });

        document.getElementById('form-assistant-msg').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new URLSearchParams(new FormData(e.target));
            fd.append('action', 'save_assistant_msg');
            const res = await fetch(API, { method: 'POST', body: fd });
            if((await res.json()).success) {
                document.getElementById('modal-assistant-msg').classList.add('hidden');
                fetchStats();
            }
        });

        function toggleCustomRange() {
            const cr = document.getElementById('custom-range');
            cr.classList.toggle('hidden');
        }

        async function fetchBilling(period) {
            let url = `${API}?action=billing_report&period=${period}`;

            if (period === 'custom') {
                const s = document.getElementById('bill-start').value;
                const e = document.getElementById('bill-end').value;
                if (!s || !e) return alert('Selecione as datas!');
                url = `${API}?action=billing_report&start=${s}&end=${e}`;
            }

            const res = await fetch(url);
            const data = await res.json();

            if (data.success) {
                const resDiv = document.getElementById('billing-result');
                resDiv.classList.remove('hidden');
                document.getElementById('bill-total').textContent = data.total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                document.getElementById('bill-count').textContent = `${data.count} Reservas Pagas`;

                let label = 'Faturamento';
                if (period === 'today') label = 'Faturamento de Hoje';
                if (period === '7') label = 'Faturamento (7 dias)';
                if (period === '30') label = 'Faturamento (30 dias)';
                document.getElementById('bill-label').textContent = label;
            }
        }

        document.getElementById('btn-help-password').addEventListener('click', () => {
            document.getElementById('modal-password-help').classList.remove('hidden');
        });
        document.getElementById('btn-close-password-help').addEventListener('click', () => {
            document.getElementById('modal-password-help').classList.add('hidden');
        });

        document.getElementById('btn-integrations').addEventListener('click', async () => {
            const modal = document.getElementById('modal-integrations');

            // Fetch setup
            const res = await fetch(`${API}?action=get_integration`);
            const data = await res.json();
            if (data.gateway) {
                document.getElementById('gateway-provider').value = data.gateway;
            }
            toggleGatewayFields(); 
            if (data.gateway_token) document.getElementById('gateway-token').value = data.gateway_token;
            if (data.efi_client_id) document.getElementById('efi-client-id').value = data.efi_client_id;
            if (data.efi_client_secret) document.getElementById('efi-client-secret').value = data.efi_client_secret;
            if (data.efi_cert_name) document.getElementById('cert-status').textContent = "Arquivo atual: " + data.efi_cert_name;
            
            if (data.tempo_pagamento) document.getElementById('tempo-pagamento').value = data.tempo_pagamento;
            if (data.group_vip) document.getElementById('group-vip').value = data.group_vip;
            if (data.whatsapp_suporte) document.getElementById('whatsapp-suporte').value = data.whatsapp_suporte;
            if (data.mensagem_suporte) document.getElementById('mensagem-suporte').value = data.mensagem_suporte;
            if (data.repassar_taxa) document.getElementById('repassar_taxa').checked = data.repassar_taxa === '1';
            if (data.whatsapp_share_template) document.getElementById('whatsapp-share-template').value = data.whatsapp_share_template;
            if (data.password_complexity) document.getElementById('password-complexity').value = data.password_complexity;
            
            // Evolution API
            if (data.evolution_api_url) document.getElementById('evolution_api_url').value = data.evolution_api_url;
            if (data.evolution_api_key) document.getElementById('evolution_api_key').value = data.evolution_api_key;
            if (data.evolution_instance) document.getElementById('evolution_instance').value = data.evolution_instance;
            
            document.getElementById('repassar_taxa').checked = data.repassar_taxa === '1';

            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.add('opacity-100'); }, 10);
        });

        function toggleGatewayFields() {
            const gateway = document.getElementById('gateway-provider').value;
            const mpFields = document.getElementById('fields-mercadopago');
            const efiFields = document.getElementById('fields-efi');
            const feeContainer = document.getElementById('fee-repassar-container');

            if (gateway === 'mercadopago') {
                mpFields.classList.remove('hidden');
                efiFields.classList.add('hidden');
                feeContainer.classList.add('hidden'); // Oculta repasse para Mercado Pago
            } else if (gateway === 'efi') {
                mpFields.classList.add('hidden');
                efiFields.classList.remove('hidden');
                feeContainer.classList.remove('hidden'); // Mostra repasse para Efí
            }
        }

        window.toggleVisibility = function(id) {
            const input = document.getElementById(id);
            const btn = input.nextElementSibling;
            const icon = btn.querySelector('.eye-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        };

        document.getElementById('gateway-provider').addEventListener('change', () => {
            toggleGatewayFields();
        });

        document.getElementById('btn-close-integrations').addEventListener('click', () => {
            const modal = document.getElementById('modal-integrations');
            modal.classList.remove('opacity-100');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        });

        document.getElementById('form-integrations').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-save-integrations');
            btn.innerHTML = 'Salvando...';

            try {
                const fd = new FormData();
                fd.append('action', 'save_integration');
                fd.append('gateway', document.getElementById('gateway-provider').value);
                fd.append('token', document.getElementById('gateway-token').value);
                fd.append('efi_client_id', document.getElementById('efi-client-id').value);
                fd.append('efi_client_secret', document.getElementById('efi-client-secret').value);
                
                const certFile = document.getElementById('efi-cert-file').files[0];
                if (certFile) {
                    fd.append('efi_cert_file', certFile);
                }

                fd.append('tempo_pagamento', document.getElementById('tempo-pagamento').value);
                fd.append('group_vip', document.getElementById('group-vip').value);
                fd.append('whatsapp_suporte', document.getElementById('whatsapp-suporte').value);
                fd.append('mensagem_suporte', document.getElementById('mensagem-suporte').value);
                fd.append('repassar_taxa', document.getElementById('repassar_taxa').checked ? '1' : '0');
                fd.append('whatsapp_share_template', document.getElementById('whatsapp-share-template').value);
                fd.append('password_complexity', document.getElementById('password-complexity').value);

                // Evolution API
                fd.append('evolution_api_url', document.getElementById('evolution_api_url').value);
                fd.append('evolution_api_key', document.getElementById('evolution_api_key').value);
                fd.append('evolution_instance', document.getElementById('evolution_instance').value);

                const res = await fetch(API, { method: 'POST', body: fd });
                const result = await res.json();
                
                if (result.success) {
                    btn.innerHTML = 'Salvo com sucesso!';
                    setTimeout(() => {
                        document.getElementById('btn-close-integrations').click();
                        btn.innerHTML = 'Salvar Configurações';
                    }, 1000);
                } else {
                    showNotification('Erro', result.error || 'Erro ao salvar', 'error');
                    btn.innerHTML = 'Salvar Configurações';
                }
            } catch (err) {
                console.error(err);
                showNotification('Erro', 'Erro na requisição. Verifique o console.', 'error');
                btn.innerHTML = 'Salvar Configurações';
            }
        });

        // Nova Rifa Logic
        document.getElementById('btn-new-rifa').addEventListener('click', () => {
            const modal = document.getElementById('modal-new-rifa');
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.add('opacity-100'); }, 10);
        });

        document.getElementById('btn-close-new').addEventListener('click', () => {
            const modal = document.getElementById('modal-new-rifa');
            modal.classList.remove('opacity-100');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        });

        document.getElementById('form-new-rifa').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-new');

            btn.disabled = true;
            btn.innerHTML = 'Criando (Aguarde, pode demorar)...';

            const fd = new FormData();
            fd.append('action', 'create_rifa');
            fd.append('nome', document.getElementById('new-nome').value);
            fd.append('preco', document.getElementById('new-preco').value);
            fd.append('imagem', document.getElementById('new-imagem').value);

            const fileInput = document.getElementById('new-imagem-file');
            if (fileInput.files.length > 0) {
                fd.append('imagem_file', fileInput.files[0]);
            }

            fd.append('qtd', document.getElementById('new-qtd').value);
            fd.append('sorteio', document.getElementById('new-sorteio').value);
            fd.append('p1', document.getElementById('new-premio1').value);
            fd.append('p2', document.getElementById('new-premio2').value);
            fd.append('p3', document.getElementById('new-premio3').value);
            fd.append('p4', document.getElementById('new-premio4').value);
            fd.append('p5', document.getElementById('new-premio5').value);

            try {
                const req = await fetch(API, { method: 'POST', body: fd });
                const res = await req.json();

                if (res.success) {
                    alert('Rifa criada com sucesso!');
                    window.location.reload();
                } else {
                    alert(res.error || 'Erro ao criar rifa');
                    btn.disabled = false;
                    btn.innerHTML = 'Criar e Ativar Rifa';
                }
            } catch (err) {
                alert('Erro ao criar rifa');
                btn.disabled = false;
                btn.innerHTML = 'Criar e Ativar';
            }
        });

        // Security Modal Monitor
        document.getElementById('btn-open-security-monitor').addEventListener('click', () => {
            const m = document.getElementById('modal-security-monitor');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
            fetchSecurityStats();
        });

        window.closeSecurityMonitor = () => {
            const m = document.getElementById('modal-security-monitor');
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        };

        async function fetchSecurityStats() {
            try {
                const cat = document.getElementById('filter-log-cat').value;
                const ip = document.getElementById('filter-log-ip').value;
                const res = await fetch(`${API}?action=security_stats&category=${cat}&ip=${ip}`);
                const data = await res.json();
                
                // Render Online
                const onlineCont = document.getElementById('security-online-list');
                onlineCont.innerHTML = '';
                const types = { 'visitante': '👤 Visitantes', 'afiliado': '🤝 Afiliados', 'admin': '🛡️ Admin' };
                
                for (let type in types) {
                    const qtd = data.online[type] || 0;
                    onlineCont.innerHTML += `
                        <div class="flex justify-between items-center text-xs font-bold text-gray-700 bg-white p-2 rounded shadow-sm border border-gray-100">
                            <span>${types[type]}</span>
                            <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">${qtd}</span>
                        </div>
                    `;
                }

                // Render Top Pages
                const pCont = document.getElementById('security-top-pages');
                pCont.innerHTML = data.top_pages.map(p => `
                    <div class="flex items-center gap-2 bg-white px-2 py-1.5 rounded border border-gray-100 overflow-hidden text-ellipsis whitespace-nowrap">
                        <span class="bg-gray-100 px-1.5 rounded text-gray-400">${p.acessos}x</span>
                        <span class="flex-1">${p.pagina}</span>
                    </div>
                `).join('');

                // Render Logs
                const tbody = document.getElementById('security-logs-tbody');
                const cats = {
                    'acesso_site': '<span class="px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded-full font-black text-[9px] uppercase">Acesso</span>',
                    'acao_admin': '<span class="px-1.5 py-0.5 bg-red-100 text-red-600 rounded-full font-black text-[9px] uppercase">Admin</span>',
                    'acao_afiliado': '<span class="px-1.5 py-0.5 bg-purple-100 text-purple-600 rounded-full font-black text-[9px] uppercase">Afiliado</span>'
                };

                tbody.innerHTML = data.logs.map(l => `
                    <tr class="text-gray-600 hover:bg-gray-50 transition-colors">
                        <td class="py-3 pr-4 font-mono">${new Date(l.data_hora).toLocaleString('pt-BR', {hour:'2-digit', minute:'2-digit', day:'2-digit', month:'2-digit'})}</td>
                        <td class="py-3 pr-4">
                            <div class="font-bold text-gray-800">${l.ip}</div>
                            <div class="text-[9px] text-gray-400 uppercase">${l.cidade}, ${l.pais}</div>
                        </td>
                        <td class="py-3 pr-4">${cats[l.categoria] || l.categoria}</td>
                        <td class="py-3 leading-tight">
                            ${l.acao.includes('falhou') ? '<span class="text-red-600 font-bold">⚠️ '+l.acao+'</span>' : l.acao} 
                            <div class="text-[9px] text-gray-300 font-mono">${l.pagina}</div>
                        </td>
                    </tr>
                `).join('');
            } catch(e) {}
        }

        let lastFailedAlert = 0;
        async function checkSecurityAlerts(count) {
            if (count > 0 && Date.now() - lastFailedAlert > 60000) {
                const m = document.getElementById('modal-security-alert');
                m.classList.remove('hidden');
                setTimeout(() => m.classList.add('opacity-100'), 10);
                lastFailedAlert = Date.now();
            }
        }

        window.closeSecurityAlert = () => {
            const m = document.getElementById('modal-security-alert');
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 500);
        };

        window.openSecurityMonitorAndCloseAlert = () => {
            closeSecurityAlert();
            document.getElementById('btn-open-security-monitor').click();
        };

        async function getLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject('Seu navegador não suporta geolocalização exata.');
                } else {
                    navigator.geolocation.getCurrentPosition(
                        (pos) => resolve({lat: pos.coords.latitude, lng: pos.coords.longitude}),
                        (err) => {
                            if (err.code === 1) reject('A geolocalização exata é obrigatória para parceiros por segurança. Por favor, autorize no seu navegador.');
                            else reject('Erro ao obter localização: ' + err.message);
                        },
                        { enableHighAccuracy: true, timeout: 5000 }
                    );
                }
            });
        }

        // Test Location Admin Panel
        document.getElementById('btn-test-location-admin').addEventListener('click', async () => {
            const btn = document.getElementById('btn-test-location-admin');
            const resCont = document.getElementById('location-test-cont-admin');
            const resDiv = document.getElementById('location-test-result-admin');

            btn.disabled = true;
            btn.innerHTML = '🔍 Localizando...';
            resCont.classList.add('hidden');

            try {
                const coords = await getLocation();
                
                const res = await fetch('../backend/api/login.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'test_location', lat: coords.lat, lng: coords.lng})
                });
                const data = await res.json();
                
                if (data.success) {
                    resDiv.innerHTML = `
                        <div class="font-black uppercase border-b border-blue-100 pb-1 mb-1">Seus Dados de Acesso Atuais:</div>
                        <div class="mb-1"><strong>🖥 Seu IP:</strong> ${data.ip}</div>
                        <div><strong>📍 Endereço Resolvido:</strong> ${data.address}</div>
                    `;
                    resCont.classList.remove('hidden');
                } else {
                    alert('Erro ao testar: ' + (data.error || 'Erro desconhecido.'));
                }
            } catch (err) {
                alert(err);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '📍 Testar Meu Acesso (GPS)';
            }
        });

        // Test Email
        document.getElementById('btn-test-email').addEventListener('click', async () => {
            const btn = document.getElementById('btn-test-email');
            const icon = document.getElementById('icon-test-email');
            const form = document.getElementById('form-smtp');
            const fd = new FormData(form);
            fd.append('action', 'send_test_email');

            const originalText = btn.innerHTML;
            btn.innerHTML = 'Enviando...';
            btn.disabled = true;

            try {
                const res = await fetch(API, { method: 'POST', body: fd });
                const data = await res.json();
                if(data.success) {
                    alert('✅ Sucesso! O e-mail de teste foi enviado para ' + data.email);
                } else {
                    alert('❌ Erro no envio: ' + (data.error || 'Verifique as configurações.'));
                }
            } catch(e) {
                alert('Erro na comunicação com o servidor.');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
        
        // Test WhatsApp
        document.getElementById('btn-test-whatsapp').addEventListener('click', async () => {
            const btn = document.getElementById('btn-test-whatsapp');
            const num = document.getElementById('test-whatsapp-number').value;
            if(!num) return alert('Informe o número de teste!');
            
            btn.innerHTML = 'Enviando...';
            btn.disabled = true;

            const fd = new URLSearchParams();
            fd.append('action', 'test_whatsapp');
            fd.append('test_number', num);

            try {
                const res = await fetch(API, { method: 'POST', body: fd });
                const data = await res.json();
                if(data.success) {
                    showNotification('Sucesso!', 'A mensagem de teste foi enviada 🚀', 'success');
                } else {
                    showNotification('Erro na API', data.error || 'Verifique se a URL e a API Key estão corretas.', 'error');
                }
            } catch(e) {
                showNotification('Erro Fatal', 'Erro de conexão com o servidor', 'error');
            } finally {
                btn.innerHTML = 'Testar Agora';
                btn.disabled = false;
            }
        });

        // Popup Config
        document.getElementById('btn-open-popup').addEventListener('click', async () => {
            const modal = document.getElementById('modal-popup-config');
            
            // Fetch Setup
            const res = await fetch(`${API}?action=get_popup_settings`);
            const json = await res.json();
            if(json.success && json.data) {
                const d = json.data;
                document.getElementById('popup-active').checked = d.popup_active === '1';
                document.getElementById('popup-title').value = d.popup_title || '';
                document.getElementById('popup-content').value = d.popup_content || '';
                document.getElementById('popup-button').value = d.popup_button || 'Entendi';
                document.getElementById('popup-link').value = d.popup_link || '';
                document.getElementById('popup-video').value = d.popup_video || '';
                document.getElementById('current-popup-image').value = d.popup_image || '';
                
                const preview = document.getElementById('popup-image-preview');
                const img = document.getElementById('popup-img-tag');
                if(d.popup_image) {
                    img.src = '../' + d.popup_image;
                    preview.classList.remove('hidden');
                } else {
                    preview.classList.add('hidden');
                }
            }
            
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.add('opacity-100'); }, 10);
        });

        document.getElementById('btn-close-popup-config').addEventListener('click', () => {
            const modal = document.getElementById('modal-popup-config');
            modal.classList.remove('opacity-100');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        });

        document.getElementById('form-popup-config').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-save-popup');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Salvando...';
            btn.disabled = true;

            const fd = new FormData(e.target);
            fd.append('action', 'save_popup_settings');
            if (!fd.has('popup_active')) fd.append('popup_active', '0');

            try {
                const res = await fetch(API, { method: 'POST', body: fd });
                const result = await res.json();
                if (result.success) {
                    btn.innerHTML = '✅ Salvo!';
                    setTimeout(() => { 
                        document.getElementById('btn-close-popup-config').click();
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 1000);
                } else {
                    showNotification('Erro', result.error || 'Erro ao salvar', 'error');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                showNotification('Erro', 'Erro na requisição', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });

        fetchStats();
        setInterval(() => {
            fetchStats();
            const mon = document.getElementById('modal-security-monitor');
            if(mon && !mon.classList.contains('hidden')) fetchSecurityStats();
        }, 10000);

        // Toggle Menu on Click (Universal)
        document.getElementById('btn-menu').addEventListener('click', (e) => {
            e.stopPropagation();
            const drop = document.getElementById('dropdown-menu');
            drop.classList.toggle('hidden');
            drop.classList.toggle('flex');
        });

        // Close menu when an item inside is clicked (unless it's a toggle)
        document.getElementById('dropdown-menu').addEventListener('click', (e) => {
            const isToggle = e.target.closest('label') || e.target.closest('input');
            if (!isToggle) {
                const drop = document.getElementById('dropdown-menu');
                drop.classList.add('hidden');
                drop.classList.remove('flex');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const btn = document.getElementById('btn-menu');
            const drop = document.getElementById('dropdown-menu');
            if (btn && drop && !btn.contains(e.target) && !drop.contains(e.target)) {
                drop.classList.add('hidden');
                drop.classList.remove('flex');
            }
        });

        // Image Preview Helper (Global)
        function setupImagePreview(inputId, imgId, containerId) {
            const input = document.getElementById(inputId);
            const img = document.getElementById(imgId);
            const container = document.getElementById(containerId);
            if (!input || !img || !container) return;

            input.onchange = (e) => {
                const [file] = input.files;
                if (file) {
                    img.src = URL.createObjectURL(file);
                    container.classList.remove('hidden');
                }
            };
        }

        // Apply previews
        setupImagePreview('new-imagem-file', 'img-new-rifa', 'preview-new-rifa');
        setupImagePreview('popup-image-file', 'popup-img-tag', 'popup-image-preview');

        // URL input preview for new rifa
        if(document.getElementById('new-imagem')) {
            document.getElementById('new-imagem').addEventListener('input', (e) => {
                const val = e.target.value;
                const img = document.getElementById('img-new-rifa');
                const cont = document.getElementById('preview-new-rifa');
                if(val && val.startsWith('http')) {
                    img.src = val;
                    cont.classList.remove('hidden');
                }
            });
        }

        window.clearImagePreview = function(inputId, urlId, imgId, contId, hiddenId = '') {
            if(inputId) document.getElementById(inputId).value = '';
            if(urlId) document.getElementById(urlId).value = '';
            if(imgId) document.getElementById(imgId).src = '';
            if(contId) document.getElementById(contId).classList.add('hidden');
            if(hiddenId) document.getElementById(hiddenId).value = '';
        };

        // Affiliates Logic
        document.getElementById('btn-affiliates').addEventListener('click', () => {
            const m = document.getElementById('modal-affiliates');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
            fetchAffiliates();
        });

        async function fetchAffiliates() {
            const res = await fetch(`${API}?action=get_affiliates`);
            const data = await res.json();
            const tbody = document.getElementById('table-affiliates');
            tbody.innerHTML = '';

            data.affiliates.forEach(af => {
                const tr = document.createElement('tr');
                tr.className = 'border-b hover:bg-gray-50';
                
                let btnAttr = af.can_payout ? '' : 'disabled';
                let btnClass = af.can_payout 
                    ? 'bg-green-500 text-white hover:bg-green-600' 
                    : 'bg-gray-100 text-gray-400 cursor-not-allowed';
                
                let cycleMsg = af.can_payout 
                    ? '<span class="text-[10px] text-green-600 font-bold">✓ Ciclo Completo</span>' 
                    : `<span class="text-[10px] text-red-400">Faltam ${af.days_remaining} dias</span>`;

                tr.innerHTML = `
                    <td class="p-3">
                        <div class="font-bold">${af.nome}</div>
                        <div class="text-[10px] text-gray-400">${af.whatsapp}</div>
                    </td>
                    <td class="p-3 font-bold">${af.vendas_pagas}</td>
                    <td class="p-3 font-bold text-[#8e44ad]">R$ ${af.saldo.replace('.', ',')}</td>
                    <td class="p-3 text-gray-400">R$ ${af.total_ganho.replace('.', ',')}</td>
                    <td class="p-3 space-y-1 text-center">
                        <div class="flex flex-col gap-1 items-center">
                            <button onclick="viewAffiliateSales(${af.id}, '${af.nome}')" class="text-[10px] bg-blue-50 text-blue-600 px-2 py-1 rounded-lg border border-blue-100 hover:bg-blue-100 font-bold w-32">Ver Detalhes</button>
                            ${cycleMsg}
                            <button onclick="payoutAffiliate(${af.id}, '${af.nome}', ${af.saldo})" ${btnAttr} class="text-[10px] font-black px-2 py-2 rounded-lg w-32 transition-all ${btnClass}">
                                PAGAR AGORA
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        async function viewAffiliateSales(id, nome) {
            document.getElementById('af-sales-title').innerText = `Vendas de ${nome}`;
            const res = await fetch(`${API}?action=get_affiliate_sales&id=${id}`);
            const data = await res.json();
            
            const tbody = document.getElementById('table-af-sales-body');
            tbody.innerHTML = '';

            data.sales.forEach(s => {
                const tr = document.createElement('tr');
                tr.className = 'border-b hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="p-3 text-gray-400 text-[10px]">${new Date(s.data_reserva).toLocaleDateString()}</td>
                    <td class="p-3 font-bold">${s.comprador}</td>
                    <td class="p-3 text-gray-500">${s.rifa_nome}</td>
                    <td class="p-3 font-mono text-[9px] text-indigo-600 leading-tight">${s.numeros}</td>
                    <td class="p-3 text-right font-bold">R$ ${parseFloat(s.valor_total).toFixed(2).replace('.', ',')}</td>
                `;
                tbody.appendChild(tr);
            });

            const m = document.getElementById('modal-af-sales');
            m.classList.remove('hidden');
            setTimeout(() => m.classList.add('opacity-100'), 10);
        }

        async function payoutAffiliate(id, nome, saldo) {
            if (!confirm(`Confirmar fechamento de ciclo e pagamento de R$ ${parseFloat(saldo).toFixed(2).replace('.', ',')} para ${nome} via PIX?\n\nO saldo do afiliado será zerado e registrado um saque pendente.`)) return;
            
            const fd = new URLSearchParams();
            fd.append('action', 'payout_affiliate');
            fd.append('id', id);

            const res = await fetch(API, { method: 'POST', body: fd });
            const data = await res.json();
            
            if(data.success) {
                showNotification('Ciclo Fechado!', data.message, 'success');
                fetchAffiliates();
                fetchStats();
            } else {
                showNotification('Erro', data.error, 'error');
            }
        }

        // Helper to close specific modal
        window.closeModal = (id) => {
            const m = document.getElementById(id);
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        };
    </script>
</body>

</html>