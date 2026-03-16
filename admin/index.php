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
        </div>
        <div class="w-full md:w-auto grid grid-cols-2 sm:flex sm:flex-wrap gap-1.5 justify-center md:justify-end">
            <button id="btn-new-rifa"
                class="bg-[#00a650] text-white font-bold px-2 py-1.5 rounded shadow hover:bg-[#009647] text-[11px] md:text-xs text-center">Criar
                Rifa</button>
            <a href="rifas.php"
                class="bg-blue-500 text-white font-bold px-2 py-1.5 rounded shadow hover:bg-blue-600 text-[11px] md:text-xs text-center flex justify-center items-center">Gerenciar
                Rifas</a>
            <button id="btn-billing"
                class="bg-purple-600 text-white font-bold px-2 py-1.5 rounded shadow hover:bg-purple-700 text-[11px] md:text-xs text-center">FIN</button>
            <a href="ganhadores.php"
                class="bg-yellow-500 text-white font-bold px-2 py-1.5 rounded shadow hover:bg-yellow-600 text-[11px] md:text-xs text-center flex justify-center items-center text-[#2c3e50]">Ganhadores</a>
            <div class="relative group">
                <button type="button" class="bg-indigo-600 text-white font-bold px-3 py-1.5 rounded shadow hover:bg-indigo-700 text-[11px] md:text-xs text-center flex items-center gap-1 h-full">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Configurações
                </button>
                <div class="hidden group-hover:block absolute right-0 mt-0 w-48 bg-white border border-gray-100 rounded-xl shadow-2xl z-[60] p-3 space-y-2">
                    <button id="btn-integrations" type="button" class="w-full bg-indigo-500 text-white font-bold px-3 py-2 rounded shadow hover:bg-indigo-600 text-[11px] md:text-xs text-center flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Integrações
                    </button>
                    <button id="btn-security" type="button" class="w-full bg-red-500 text-white font-bold px-3 py-2 rounded shadow hover:bg-red-600 text-[11px] md:text-xs text-center flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Novo Acesso
                    </button>
                    <button id="btn-open-smtp" type="button" class="w-full bg-blue-600 text-white font-bold px-3 py-2 rounded shadow hover:bg-blue-700 text-[11px] md:text-xs text-center flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Acesso Email
                    </button>
                    <div class="flex items-center justify-between gap-2 bg-gray-50 px-3 py-2 rounded border border-gray-200">
                        <span class="text-[10px] font-bold text-gray-500 uppercase">Manutenção</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="maintenance-toggle" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-500"></div>
                        </label>
                    </div>
                </div>
            </div>
            <a href="../index.html"
                class="bg-gray-200 text-gray-700 font-bold px-2 py-1.5 rounded hover:bg-gray-300 text-[11px] md:text-xs text-center flex justify-center items-center">Site</a>
            <a href="../backend/api/logout.php"
                class="bg-gray-800 text-white font-bold px-2 py-1.5 rounded hover:bg-black text-[11px] md:text-xs text-center flex justify-center items-center">Sair</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-5 gap-4 mb-6" id="stats-grid">
        <div class="bg-white p-4 rounded-lg shadow text-center border-l-4 border-green-500">
            <h3 class="text-gray-500 text-xs font-bold uppercase mb-1">Livres</h3>
            <p class="text-3xl font-black text-[#2c3e50]" id="stat-livre">0</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow text-center border-l-4 border-yellow-500">
            <h3 class="text-gray-500 text-xs font-bold uppercase mb-1">Reservados</h3>
            <p class="text-3xl font-black text-[#2c3e50]" id="stat-reservado">0</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow text-center border-l-4 border-purple-500">
            <h3 class="text-gray-500 text-xs font-bold uppercase mb-1">Pagos</h3>
            <p class="text-3xl font-black text-[#9b59b6]" id="stat-pago">0</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow text-center border-l-4 border-blue-500">
            <h3 class="text-gray-500 text-xs font-bold uppercase mb-1">Faturamento</h3>
            <p class="text-3xl font-black text-blue-600" id="stat-faturamento">R$ 0,00</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow text-center border-l-4 border-red-500">
            <h3 class="text-gray-500 text-xs font-bold uppercase mb-1">Repassado (1.19%)</h3>
            <p class="text-3xl font-black text-red-600" id="stat-taxas">R$ 0,00</p>
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
                    <h2 class="text-xl font-black text-gray-800">Integrações</h2>
                    <p class="text-xs text-gray-500">Gateway de Pagamento PIX</p>
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
                    <div class="relative">
                        <input type="password" id="gateway-token"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 pr-10 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                            placeholder="APP_USR-...">
                        <button type="button" onclick="toggleVisibility('gateway-token')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
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
                        <div class="relative">
                            <input type="password" id="efi-client-secret"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 pr-10 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                placeholder="Client_Secret_...">
                            <button type="button" onclick="toggleVisibility('efi-client-secret')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition-colors">
                                <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Certificado .p12 (Upload)</label>
                        <input type="file" id="efi-cert-file" accept=".p12"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <p id="cert-status" class="text-[9px] text-gray-400 mt-1 ml-1"></p>
                    </div>
                </div>
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
                        Padrão (WhatsApp)</label>
                    <textarea id="mensagem-suporte"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none h-20"
                        placeholder="Olá, $uper$orte! Preciso de ajuda."></textarea>
                </div>
                <button type="submit" id="btn-save-integrations"
                    class="w-full bg-indigo-600 text-white font-bold py-4 rounded-xl shadow uppercase text-sm mt-4 mb-4 hover:bg-indigo-700 transition-colors">
                    Salvar Configurações
                </button>
            </form>
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
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Imagem Banner (URL ou Arquivo
                        Próprio)</label>
                    <div class="flex gap-2">
                        <input type="url" id="new-imagem"
                            class="w-1/2 bg-gray-50 border border-gray-200 rounded-lg p-2 text-[10px] md:text-xs focus:ring-2 focus:ring-[#00a650] outline-none"
                            placeholder="Ou cole o Link https://...">
                        <input type="file" id="new-imagem-file" accept="image/*"
                            class="w-1/2 bg-gray-50 border border-gray-200 rounded-lg p-1.5 text-[10px] md:text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-[#00a650] file:text-white file:font-bold hover:file:bg-[#009647]">
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
                <button type="submit" id="btn-save-smtp" class="md:col-span-2 bg-[#00a650] text-white font-bold py-4 rounded-xl shadow uppercase text-sm mt-2 hover:bg-[#009647] transition-colors">Salvar Configuração</button>
            </form>
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


    <!-- Modal Notificação -->
    <div id="modal-notif" class="fixed inset-0 bg-black bg-opacity-80 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl relative">
            <div id="notif-icon-success" class="hidden mx-auto w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
            </div>
            <div id="notif-icon-error" class="hidden mx-auto w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>
            <h2 id="notif-title" class="text-xl font-black text-gray-800 mb-2">Sucesso!</h2>
            <p id="notif-message" class="text-sm text-gray-500 mb-6 font-medium">Informação atualizada com sucesso.</p>
            <button id="btn-close-notif" class="w-full bg-[#2c3e50] text-white font-black py-4 rounded-xl shadow uppercase text-sm hover:bg-gray-800 transition-colors">Entendido</button>
        </div>
    </div>

    <script>
        const API = '../backend/api/admin.php';

        function showNotification(title, message, type = 'success', callback = null) {
            const modal = document.getElementById('modal-notif');
            document.getElementById('notif-title').textContent = title;
            document.getElementById('notif-message').textContent = message;
            
            const iconSuccess = document.getElementById('notif-icon-success');
            const iconError = document.getElementById('notif-icon-error');
            const btnClose = document.getElementById('btn-close-notif');

            if(type === 'success') {
                iconSuccess.classList.remove('hidden');
                iconError.classList.add('hidden');
            } else {
                iconSuccess.classList.add('hidden');
                iconError.classList.remove('hidden');
            }

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
        let timerInterval = null;

        async function fetchStats(page = 1) {
            currentPage = page;
            try {
                const ts = new Date().getTime();
                const res = await fetch(`${API}?action=stats&page=${page}&status=${currentStatus}&_=${ts}`);
                const data = await res.json();

                // Server Time Sync
                const serverTime = new Date(data.server_time).getTime();
                serverTimeOffset = serverTime - new Date().getTime();
                tempoPagamento = data.tempo_pagamento;
                countdowns = [];

                document.getElementById('stat-livre').textContent = data.stats['disponivel'] || 0;
                document.getElementById('stat-reservado').textContent = data.stats['reservado'] || 0;
                document.getElementById('stat-pago').textContent = data.stats['pago'] || 0;
                document.getElementById('stat-faturamento').textContent = parseFloat(data.faturamento).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            document.getElementById('stat-taxas').textContent = parseFloat(data.total_repassado).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                if (document.getElementById('maintenance-toggle')) {
                    document.getElementById('maintenance-toggle').checked = (data.maintenance === '1');
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
                        const dataReserva = new Date(r.data_reserva).getTime();
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

            } catch (e) {
                console.error(e);
            }
        }

        function startGlobalTimer() {
            if (timerInterval) clearInterval(timerInterval);
            updateCountdowns();
            timerInterval = setInterval(updateCountdowns, 1000);
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

        const closeHelp = () => {
            const m = document.getElementById('modal-help-smtp');
            m.classList.remove('opacity-100');
            setTimeout(() => m.classList.add('hidden'), 300);
        };
        document.getElementById('btn-close-help-smtp').addEventListener('click', closeHelp);
        document.getElementById('btn-entendi-smtp').addEventListener('click', closeHelp);

        // Maintenance Toggle
        document.getElementById('maintenance-toggle').addEventListener('change', async (e) => {
            const val = e.target.checked ? '1' : '0';
            const fd = new URLSearchParams();
            fd.append('action', 'set_maintenance');
            fd.append('status', val);
            await fetch(API, { method: 'POST', body: fd });
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

                const res = await fetch(API, { method: 'POST', body: fd });
                const result = await res.json();
                
                if (result.success) {
                    btn.innerHTML = 'Salvo com sucesso!';
                    setTimeout(() => {
                        document.getElementById('btn-close-integrations').click();
                        btn.innerHTML = 'Salvar Configurações';
                    }, 1000);
                } else {
                    alert(result.error || 'Erro ao salvar');
                    btn.innerHTML = 'Salvar Configurações';
                }
            } catch (err) {
                console.error(err);
                alert('Erro na requisição. Verifique o console.');
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

        fetchStats();
        setInterval(fetchStats, 10000);
    </script>
</body>

</html>