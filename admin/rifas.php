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
    <title>Gerenciar Rifas - $UPER$ORTE</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../frontend/png/cifrao.png">
</head>

<body class="bg-gray-50 text-gray-800 font-sans p-6">

    <div
        class="max-w-4xl mx-auto mb-6 bg-white p-6 rounded-lg shadow border border-gray-100 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-[#8e44ad]">Gerenciar Rifas</h1>
            <p class="text-sm text-gray-500">Controle completo sobre seus Sorteios</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-2">
            <span id="session-timer" class="hidden text-[10px] font-black text-gray-400 bg-white px-3 py-2 rounded-xl border border-gray-200 shadow-sm">EXPIRA EM: 20:00</span>
            <a href="index.php"
                class="text-[10px] md:text-xs font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-xl px-4 py-2 transition-colors flex items-center gap-1 shadow-sm">
                Voltar
            </a>
        </div>
    </div>

    <!-- Tabela Rifas -->
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-bold text-gray-700 uppercase tracking-wide">Todas as Rifas</h2>
            <div class="flex gap-2">
                <button onclick="setStatusFilter(this, '')"
                    class="filter-btn active-filter bg-gray-200 text-gray-700 rounded-full px-4 py-1.5 text-[10px] font-black uppercase tracking-wider transition-all">TODAS</button>
                <button onclick="setStatusFilter(this, 'aberta')"
                    class="filter-btn bg-white border border-gray-200 text-gray-400 rounded-full px-4 py-1.5 text-[10px] font-black uppercase tracking-wider hover:bg-gray-50 transition-all">ABERTAS</button>
                <button onclick="setStatusFilter(this, 'fechada')"
                    class="filter-btn bg-white border border-gray-200 text-gray-400 rounded-full px-4 py-1.5 text-[10px] font-black uppercase tracking-wider hover:bg-gray-50 transition-all">FECHADAS</button>
                <input type="hidden" id="filter-status" value="">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase font-bold text-xs">
                        <th class="p-4 border-b">ID</th>
                        <th class="p-4 border-b">Nome da Rifa</th>
                        <th class="p-4 border-b">Preço</th>
                        <th class="p-4 border-b">Acesso</th>
                        <th class="p-4 border-b">Vendas</th>
                        <th class="p-4 border-b">Status</th>
                        <th class="p-4 border-b text-right">Ação</th>
                    </tr>
                </thead>
                <tbody id="table-rifas">
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-500">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Paginação -->
        <div id="pagination" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-center gap-2">
            <!-- Botões injetados via JS -->
        </div>
    </div>

    <!-- Modal Sortear Setup -->
    <div id="modal-draw"
        class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl relative">
            <button id="btn-close-draw"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 class="text-xl font-black text-[#2c3e50] mb-2 uppercase flex items-center gap-2">
                <svg class="w-6 h-6 text-[#f1c40f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Configurar Sorteio
            </h2>
            <p class="text-xs text-gray-500 mb-4">Atenção: Rodar este sorteio fechará a rifa definitivamente.</p>

            <form id="form-draw" class="flex flex-col gap-3">
                <div>
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 block mb-1">Modo de Sorteio</label>
                    <select id="draw-type"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#f1c40f] outline-none">
                        <option value="manual">Sorteio Manual (Eu defino os ganhadores)</option>
                        <option value="auto">Sorteio Automático (Sorteador Interno)</option>
                    </select>
                </div>

                <div id="box-draw-auto" class="hidden">
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 block mb-1">Quantidade de
                        Ganhadores</label>
                    <select id="draw-qtd"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#f1c40f] outline-none">
                        <option value="1">1 Ganhador</option>
                        <option value="2">2 Ganhadores</option>
                        <option value="3">3 Ganhadores</option>
                        <option value="4">4 Ganhadores</option>
                        <option value="5">5 Ganhadores</option>
                    </select>
                </div>

                <div id="box-draw-manual">
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 block mb-1">Números Sorteados
                        (Manualmente)</label>
                    <input type="text" id="draw-manual"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#f1c40f] outline-none"
                        placeholder="Ex: 005, 012, 098">
                    <p class="text-[10px] text-gray-400 mt-1 ml-1 leading-tight">Separe os ganhadores com vírgula (Ex:
                        "1º, 2º, 3º"). ATENÇÃO: Os números informados devem estar pagos.</p>
                </div>
                <button type="submit" id="btn-submit-draw"
                    class="w-full bg-[#f1c40f] text-black font-black py-4 mt-2 rounded-xl hover:bg-yellow-500 transition-colors uppercase text-sm shadow">Sortear
                    Ganhadores</button>
            </form>
        </div>
    </div>

    <!-- Modal Editar Rifa -->
    <div id="modal-edit"
        class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
            <button id="btn-close-edit"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 class="text-xl font-black text-[#2c3e50] mb-4 uppercase flex items-center gap-2">
                <svg class="w-6 h-6 text-[#2980b9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                    </path>
                </svg>
                Editar Rifa
            </h2>

            <form id="form-edit" class="flex flex-col gap-3">
                <input type="hidden" id="edit-id" name="id">

                <div>
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Nome da Rifa</label>
                    <input type="text" id="edit-nome" name="nome"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm outline-none" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Preço (R$)</label>
                        <input type="number" step="0.01" id="edit-preco" name="preco"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm outline-none"
                            required>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 block mb-1">Origem do
                            Sorteio</label>
                        <select name="sorteio" id="edit-sorteio"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm outline-none">
                            <option value="Loteria Federal">Loteria Federal</option>
                            <option value="Jogo do Bicho">Jogo do Bicho</option>
                            <option value="Sorteador.com.br">Sorteador.com.br</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Mudar Imagem de Fundo (Deixe em branco para manter)</label>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        <input type="text" id="edit-imagem" name="imagem" placeholder="URL opcional..."
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs">
                        <input type="file" id="edit-imagem-file" name="imagem_file" accept="image/*"
                            class="w-full text-[10px] text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 p-1">
                    </div>
                    <!-- Image Preview -->
                    <div id="preview-edit-rifa" class="hidden mt-2 h-20 w-full rounded-lg overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center relative group">
                        <img id="img-edit-rifa" class="h-full w-full object-cover">
                        <button type="button" onclick="clearEditImage()" class="absolute top-1 right-1 bg-red-500 text-white p-1.5 rounded-full shadow-md hover:bg-red-600 z-10" title="Remover">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg flex flex-col gap-2 mt-2">
                    <p class="text-[10px] font-bold text-gray-500 uppercase">Preencher Prêmios</p>
                    <input type="text" id="edit-p1" name="p1" placeholder="1º prêmio (Opcional)"
                        class="w-full p-2 text-xs border border-gray-200 rounded">
                    <input type="text" id="edit-p2" name="p2" placeholder="2º prêmio (Opcional)"
                        class="w-full p-2 text-xs border border-gray-200 rounded">
                    <input type="text" id="edit-p3" name="p3" placeholder="3º prêmio (Opcional)"
                        class="w-full p-2 text-xs border border-gray-200 rounded">
                    <input type="text" id="edit-p4" name="p4" placeholder="4º prêmio (Opcional)"
                        class="w-full p-2 text-xs border border-gray-200 rounded">
                    <input type="text" id="edit-p5" name="p5" placeholder="5º prêmio (Opcional)"
                        class="w-full p-2 text-xs border border-gray-200 rounded">
                </div>

                <button type="submit" id="btn-submit-edit"
                    class="w-full bg-[#2980b9] text-white font-black py-3 mt-2 rounded-xl hover:bg-blue-700 transition-colors uppercase text-sm shadow">Salvar
                    Alterações</button>
            </form>
        </div>
    </div>

    <!-- Modal Resultados Sorteados -->
    <div id="modal-winners"
        class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div
            class="bg-white rounded-2xl p-6 max-w-md w-full text-center shadow-2xl relative max-h-[90vh] flex flex-col">
            <button id="btn-close-winners"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="inline-block p-4 rounded-full bg-yellow-100 mb-2 mx-auto mt-2">
                <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
            </div>
            <h2 class="text-3xl font-black text-[#8e44ad] mb-1 uppercase tracking-wide">Vencedores!</h2>
            <p class="text-gray-500 mb-6 font-medium text-xs">Os sortudos do prêmio são:</p>

            <div id="winners-container" class="flex-1 overflow-y-auto pr-2">
                <!-- Injetados aqui -->
            </div>

            <p class="text-[10px] text-gray-400 mt-4 uppercase font-bold tracking-wider">A rifa foi fechada com sucesso.
            </p>
        </div>
    </div>

    <!-- Modal Confirmação Excluir Rifa (Melhorado) -->
    <div id="modal-confirm-delete" class="fixed inset-0 bg-black bg-opacity-90 z-[120] hidden flex items-center justify-center p-4 backdrop-blur-md opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-sm w-full shadow-2xl relative text-center border-t-8 border-red-500 transform scale-95 transition-transform duration-300" id="modal-delete-box">
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full mx-auto flex items-center justify-center mb-6 shadow-inner">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <h2 class="text-2xl font-black text-gray-800 mb-2 tracking-tight uppercase">APAGAR RIFA?</h2>
            <p class="text-[11px] font-bold text-gray-400 uppercase mb-8 leading-relaxed px-4">Esta ação é irreversível e excluirá todos os bilhetes e reservas vinculadas.</p>
            
            <div class="mb-8">
                <label class="text-[9px] font-black text-gray-400 uppercase block mb-2 tracking-widest text-left ml-2">Digite EXCLUIR para confirmar</label>
                <input type="text" id="input-confirm-delete" 
                    class="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl p-4 text-center font-black text-gray-800 outline-none focus:border-red-500 transition-all placeholder:text-gray-200"
                    placeholder="E-X-C-L-U-I-R" autocomplete="off">
            </div>

            <div class="flex flex-col gap-3">
                <button id="btn-do-delete" class="w-full bg-red-600 hover:bg-red-700 text-white font-black py-5 rounded-2xl shadow-xl shadow-red-100 uppercase text-xs transition-all tracking-widest">
                    CONFIRMAR EXCLUSÃO
                </button>
                <button id="btn-cancel-delete" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-500 font-black py-4 rounded-2xl uppercase text-[10px] transition-all tracking-widest">
                    CANCELAR
                </button>
            </div>
        </div>
    </div>

    <script>
        const API = '../backend/api/admin.php';
        let allRifas = [];
        let currentPage = 1;
        let sessionTimerInterval = null;
        let secondsLeft = 0;

        async function fetchRifas(page = 1) {
            currentPage = page;
            const status = document.getElementById('filter-status').value;
            try {
                const res = await fetch(`${API}?action=get_rifas_list&page=${page}&status=${status}`);
                const data = await res.json();

                if (data.error && data.expired) {
                    alert('Sua sessão expirou por segurança. Por favor, entre novamente.');
                    window.location.href = 'login.php';
                    return;
                }
                if (data.error) {
                    window.location.href = 'login.php';
                    return;
                }

                if (data.expires_in) {
                    secondsLeft = parseInt(data.expires_in);
                    startTimer();
                }

                const tbody = document.getElementById('table-rifas');
                tbody.innerHTML = '';

                if (!data.rifas || data.rifas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-gray-400 font-medium">Nenhuma rifa encontrada.</td></tr>';
                    return;
                }

                allRifas = data.rifas;

                data.rifas.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b hover:bg-gray-50';

                    let bgStatus = r.status === 'aberta' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600';

                    const pct = r.quantidade_numeros > 0 ? Math.floor((r.pagos / r.quantidade_numeros) * 100) : 0;
                    const pctColor = pct === 100 ? 'bg-[#00a650]' : (pct >= 50 ? 'bg-[#f1c40f]' : 'bg-[#8e44ad]');

                    let btnEditar = '';
                    let btnAcao = '';

                    if (r.status === 'fechada') {
                        btnEditar = `<button disabled class="text-xs bg-gray-200 text-gray-400 font-bold px-2 py-1.5 rounded uppercase tracking-wider mr-1 cursor-not-allowed w-20">Editar</button>`;
                        btnAcao = `<button onclick="openWinnersModal(${r.id})" class="text-xs bg-[#9b59b6] text-white font-bold px-2 py-1.5 rounded shadow hover:bg-purple-700 transition-colors uppercase tracking-wider w-24">Sorteados</button>`;
                    } else {
                        btnEditar = `<button onclick="openEditModal(${r.id})" class="text-xs bg-[#2c3e50] text-white font-bold px-2 py-1.5 rounded shadow hover:bg-gray-800 transition-colors uppercase tracking-wider mr-1 w-20">Editar</button>`;

                        const isFull = pct >= 100;
                        const btnClass = isFull ? "bg-[#f1c40f] hover:bg-yellow-500 text-black shadow" : "bg-gray-200 text-gray-400 cursor-not-allowed";
                        const btnClick = isFull ? `onclick="openDrawModal(${r.id})"` : "";

                        btnAcao = `<button ${btnClick} class="text-xs font-bold px-2 py-1.5 rounded uppercase tracking-wider w-24 transition-all ${btnClass}">Sortear</button>`;
                    }

                    const btnExcluir = `<button onclick="deleteRifa(${r.id})" class="text-xs bg-red-500 text-white flex items-center justify-center p-2 rounded shadow hover:bg-red-700 transition-colors mr-1 h-[34px] w-9" title="EXCLUIR RIFA">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>`;
                    
                    let btnShare = '';
                    if (r.status === 'aberta') {
                        const raffleUrl = `${window.location.origin}${window.location.pathname.replace('admin/rifas.php', '')}rifa.php?id=${r.id}`;
                        const waMsg = encodeURIComponent(`🚨 *NOVA RIFA LANÇADA!* 🚨\n\n🎟️ *${r.nome}*\n💰 Apenas R$ ${parseFloat(r.preco_numero).toFixed(2).replace('.', ',')} por número!\n\n👇 *PARTICIPE AGORA:* \n${raffleUrl}`);
                        const waLink = `https://api.whatsapp.com/send?text=${waMsg}`;
                        btnShare = `<a href="${waLink}" target="_blank" class="text-xs bg-[#25D366] text-white flex items-center justify-center p-2 rounded shadow hover:bg-[#128C7E] transition-colors mr-1 h-[34px] w-9" title="Compartilhar WhatsApp">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.412-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.309 1.656zm6.29-4.143c1.589.943 3.143 1.416 4.703 1.417 5.432.001 9.853-4.42 9.856-9.853.002-2.633-1.025-5.109-2.892-6.977-1.867-1.868-4.341-2.896-6.976-2.898-5.432 0-9.854 4.421-9.857 9.855-.001 1.737.457 3.432 1.326 4.906l-.527 1.922 2.019-.53zm10.744-7.404c-.232-.117-1.371-.677-1.583-.754-.212-.077-.366-.117-.52.117-.154.234-.597.754-.732.909-.136.155-.271.174-.503.057-.232-.117-.98-.362-1.868-1.152-.69-.615-1.156-1.376-1.291-1.61-.136-.234-.015-.361.102-.477.105-.104.232-.271.348-.407.116-.136.155-.234.232-.39s.039-.291-.019-.407c-.058-.117-.52-1.255-.712-1.714-.187-.449-.377-.388-.52-.395-.135-.007-.29-.008-.444-.008-.154 0-.405.058-.617.291-.212.234-.81.792-.81 1.932 0 1.14.83 2.242.946 2.399.116.156 1.632 2.492 3.954 3.493.552.238.983.38 1.32.487.554.174 1.057.149 1.456.09.444-.066 1.371-.56 1.563-1.103.193-.544.193-1.01.136-1.103-.058-.095-.212-.154-.445-.271z"></path></svg>
                        </a>`;
                    }

                    let actions = `<div class="flex justify-end items-center">` + btnExcluir + btnShare + btnEditar + btnAcao + `</div>`;

                    const precoNum = parseFloat(r.preco_numero).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                    tr.innerHTML = `
                        <td class="p-4 align-middle font-bold text-gray-500">#${r.id}</td>
                        <td class="p-4 align-middle">
                            <div class="font-bold text-gray-800">${r.nome}</div>
                            <div class="text-[10px] text-gray-400 mt-1 uppercase">${r.quantidade_numeros} Números</div>
                        </td>
                        <td class="p-4 align-middle font-black text-gray-700">${precoNum}</td>
                        <td class="p-4 align-middle">
                            <a href="../rifa.html?id=${r.id}" target="_blank" class="text-blue-500 hover:underline text-xs flex items-center gap-1">Visitar <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg></a>
                        </td>
                        <td class="p-4 align-middle">
                            <div class="flex items-center gap-2">
                                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[10px] font-bold" title="${r.pagos} números vendidos">${pct}%</span>
                                <div class="w-16 sm:w-20 h-2 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                                    <div class="${pctColor} h-full rounded-full transition-all duration-500" style="width: ${pct}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4 align-middle">
                            <span class="px-2 py-1 rounded text-[10px] uppercase font-bold tracking-wider ${bgStatus}">${r.status}</span>
                        </td>
                        <td class="p-4 text-right align-middle min-w-[180px]">
                            ${actions}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                renderPagination(data.total_pages, data.current_page);
            } catch (e) {
                console.error(e);
            }
        }

        function setStatusFilter(btn, status) {
            document.getElementById('filter-status').value = status;

            // UI Update
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('active-filter', 'bg-gray-200', 'text-gray-700');
                b.classList.add('bg-white', 'border', 'border-gray-200', 'text-gray-400');
            });

            btn.classList.remove('bg-white', 'border', 'border-gray-200', 'text-gray-400');
            btn.classList.add('active-filter', 'bg-gray-200', 'text-gray-700');

            fetchRifas(1);
        }

        function renderPagination(totalPages, current) {
            const cont = document.getElementById('pagination');
            cont.innerHTML = '';

            if (totalPages <= 1) return;

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className = `w-8 h-8 rounded font-bold text-xs transition-colors ${i === current ? 'bg-[#8e44ad] text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-100'}`;
                btn.onclick = () => fetchRifas(i);
                cont.appendChild(btn);
            }
        }

        /* Variables for the draw */
        let currentDrawRifaId = 0;

        window.openDrawModal = function (id) {
            currentDrawRifaId = id;
            document.getElementById('modal-draw').classList.remove('hidden');
            setTimeout(() => { document.getElementById('modal-draw').classList.add('opacity-100'); }, 10);
        };

        window.openEditModal = function (id) {
            const rifa = allRifas.find(r => r.id === id);
            if (!rifa) return;

            document.getElementById('edit-id').value = rifa.id;
            document.getElementById('edit-nome').value = rifa.nome;
            document.getElementById('edit-preco').value = rifa.preco_numero;
            document.getElementById('edit-sorteio').value = rifa.sorteio_por || 'Loteria Federal';
            document.getElementById('edit-imagem').value = '';
            document.getElementById('edit-imagem-file').value = '';
            document.getElementById('edit-p1').value = rifa.premio1 || '';
            document.getElementById('edit-p2').value = rifa.premio2 || '';
            document.getElementById('edit-p3').value = rifa.premio3 || '';
            document.getElementById('edit-p4').value = rifa.premio4 || '';
            document.getElementById('edit-p5').value = rifa.premio5 || '';

            document.getElementById('modal-edit').classList.remove('hidden');
            setTimeout(() => { document.getElementById('modal-edit').classList.add('opacity-100'); }, 10);
        };

        window.openWinnersModal = async function (id) {
            try {
                const m = document.getElementById('modal-winners');
                const cont = document.getElementById('winners-container');
                cont.innerHTML = '<div class="text-center p-4">Carregando vencedores...</div>';

                m.classList.remove('hidden');
                setTimeout(() => { m.classList.add('opacity-100'); }, 10);

                const res = await fetch(`${API}?action=get_winners&rifa_id=${id}`);
                const data = await res.json();

                if (data.winners && data.winners.length > 0) {
                    cont.innerHTML = '';
                    data.winners.forEach((w) => {
                        const wppNumber = w.whatsapp.replace(/\D/g, '');
                        const prizeDesc = data.prizes ? data.prizes['premio' + w.premio_ordem] : '';
                        const box = `
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-left shadow-sm flex items-center justify-between mb-2">
                                <div>
                                    <div class="text-[10px] text-[#8e44ad] font-black uppercase mb-1">${prizeDesc ? 'Ganhou ' + prizeDesc : 'Prêmio ' + w.premio_ordem}</div>
                                    <div class="font-bold text-gray-800 text-lg flex items-center gap-2">
                                        <span class="bg-yellow-400 w-8 h-8 rounded flex items-center justify-center shadow font-black text-[#2c3e50] text-sm">${w.numero}</span>
                                        ${w.nome}
                                    </div>
                                </div>
                                <a href="https://wa.me/55${wppNumber}?text=Parabéns, você ganhou na $UPER$ORTE com o número ${w.numero}!" target="_blank" class="w-10 h-10 bg-[#25D366] text-white flex items-center justify-center rounded-full hover:bg-[#128C7E] shadow transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 21.41a10.985 10.985 0 0 1-5.6-1.53l-6.22 1.63 1.66-6.07a10.992 10.992 0 1 1 10.16 5.97zm0-19.14a8.77 8.77 0 1 0 8.77 8.77 8.78 8.78 0 0 0-8.77-8.77zm4.8 12c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.57.71-.7.86-.26.16-.48.05a6.044 6.044 0 0 1-1.78-1.09 6.64 6.64 0 0 1-1.23-1.53c-.11-.2-.01-.31.1-.42.1-.1.22-.26.33-.4.11-.14.15-.22.22-.38.07-.15.03-.3-.02-.42-.05-.11-.5-.1.22-.68.21s-.33.27-.33.32a2.02 2.02 0 0 0 .61 1.41 5.925 5.925 0 0 0 1.94 1.34 13.4 13.4 0 0 0 2.44.82 2.924 2.924 0 0 0 1.34 0 2.053 2.053 0 0 0 .54-1.77 1.68 1.68 0 0 0-.25-.43z"></path></svg>
                                </a>
                            </div>
                         `;
                        cont.insertAdjacentHTML('beforeend', box);
                    });
                } else {
                    cont.innerHTML = '<div class="text-center p-4 text-red-500 font-bold">Nenhum vencedor gravado encontrado.</div>';
                }

            } catch (e) {
                console.error(e);
            }
        };

        /* Variables for the delete */
        let currentDeleteRifaId = 0;

        window.deleteRifa = function (id) {
            currentDeleteRifaId = id;
            document.getElementById('input-confirm-delete').value = '';
            
            const m = document.getElementById('modal-confirm-delete');
            const box = document.getElementById('modal-delete-box');
            m.classList.remove('hidden');
            setTimeout(() => {
                m.classList.remove('opacity-0');
                m.classList.add('opacity-100');
                box.classList.remove('scale-95');
                box.classList.add('scale-100');
            }, 10);
        };

        document.getElementById('btn-cancel-delete').addEventListener('click', () => {
            const m = document.getElementById('modal-confirm-delete');
            const box = document.getElementById('modal-delete-box');
            m.classList.remove('opacity-100');
            m.classList.add('opacity-0');
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
            setTimeout(() => { m.classList.add('hidden'); }, 300);
        });

        document.getElementById('btn-do-delete').addEventListener('click', async () => {
            const input = document.getElementById('input-confirm-delete').value.trim().toUpperCase();
            if (input !== 'EXCLUIR') {
                showNotification('Atenção', 'Digite EXCLUIR para confirmar.', 'error');
                return;
            }

            const btn = document.getElementById('btn-do-delete');
            btn.innerHTML = 'Excluindo...';
            btn.disabled = true;

            const fd = new URLSearchParams();
            fd.append('action', 'delete_rifa');
            fd.append('id', currentDeleteRifaId);

            try {
                const res = await fetch(API, { method: 'POST', body: fd });
                const data = await res.json();
                
                if(data.success) {
                    document.getElementById('btn-cancel-delete').click();
                    fetchRifas();
                } else {
                    showNotification('Erro', data.error || 'Erro ao excluir.', 'error');
                }
            } catch(e) {
                showNotification('Erro', 'Erro ao excluir rifa.', 'error');
            } finally {
                btn.innerHTML = 'CONFIRMAR EXCLUSÃO';
                btn.disabled = false;
            }
        });

        fetchRifas();

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
                    showNotification('Sessão Expirada', 'Sua sessão expirou por segurança. Fazendo logout...', 'error', () => {
                         window.location.href = 'login.php';
                    });
                    return;
                }

                const mins = Math.floor(secondsLeft / 60);
                const secs = secondsLeft % 60;
                display.textContent = `EXPIRA EM: ${mins}:${secs.toString().padStart(2, '0')}`;
            }, 1000);
        }

        // Close functions
        document.getElementById('btn-close-draw').addEventListener('click', () => {
            const m = document.getElementById('modal-draw');
            m.classList.remove('opacity-100');
            setTimeout(() => { m.classList.add('hidden'); }, 300);
        });
        document.getElementById('btn-close-edit').addEventListener('click', () => {
            const m = document.getElementById('modal-edit');
            m.classList.remove('opacity-100');
            setTimeout(() => { m.classList.add('hidden'); }, 300);
        });
        document.getElementById('btn-close-winners').addEventListener('click', () => {
            const m = document.getElementById('modal-winners');
            m.classList.remove('opacity-100');
            setTimeout(() => { m.classList.add('hidden'); }, 300);
        });

        // Submit Edit Form
        document.getElementById('form-edit').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-edit');
            const form = e.target;
            const fd = new FormData(form);
            fd.append('action', 'edit_rifa');

            btn.innerHTML = 'Salvando...';
            btn.disabled = true;

            try {
                const res = await fetch(API, { method: 'POST', body: fd });
                const data = await res.json();

                btn.innerHTML = 'Salvar Alterações';
                btn.disabled = false;

                if (data.success) {
                    document.getElementById('btn-close-edit').click();
                    fetchRifas();
                } else {
                    showNotification('Erro', data.error || 'Erro ao editar.', 'error');
                }
            } catch (e) {
                console.error(e);
                showNotification('Erro', 'Erro fatal. Veja o console.', 'error');
            }
        });

        document.getElementById('draw-type').addEventListener('change', (e) => {
            if (e.target.value === 'manual') {
                document.getElementById('box-draw-auto').classList.add('hidden');
                document.getElementById('box-draw-manual').classList.remove('hidden');
            } else {
                document.getElementById('box-draw-auto').classList.remove('hidden');
                document.getElementById('box-draw-manual').classList.add('hidden');
            }
        });

        // Submit Draw
        document.getElementById('form-draw').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-draw');
            const type = document.getElementById('draw-type').value;
            const qtd = document.getElementById('draw-qtd').value;
            const manual = document.getElementById('draw-manual').value;

            btn.innerHTML = 'Sorteando...';
            btn.disabled = true;

            const fd = new URLSearchParams();
            fd.append('action', 'draw_multiple');
            fd.append('rifa_id', currentDrawRifaId);

            if (type === 'manual') {
                fd.append('manual', manual);
            } else {
                fd.append('qtd', qtd);
            }

            try {
                const res = await fetch(API, { method: 'POST', body: fd });
                const data = await res.json();

                btn.innerHTML = 'Sortear Ganhadores';
                btn.disabled = false;
                document.getElementById('btn-close-draw').click();

                if (data.success) {
                    if (data.winners && data.winners.length > 0) {
                        const cont = document.getElementById('winners-container');
                        cont.innerHTML = '';
                        data.winners.forEach((w, index) => {
                            const wppNumber = w.whatsapp.replace(/\D/g, '');
                            const prizeDesc = data.prizes ? data.prizes['premio' + (index + 1)] : '';
                            const box = `
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-left shadow-sm flex items-center justify-between mb-2">
                                    <div>
                                        <div class="text-[10px] text-[#8e44ad] font-black uppercase mb-1">${prizeDesc ? 'Ganhou ' + prizeDesc : 'Prêmio ' + (index + 1)}</div>
                                        <div class="font-bold text-gray-800 text-lg flex items-center gap-2">
                                            <span class="bg-yellow-400 w-8 h-8 rounded flex items-center justify-center shadow font-black text-[#2c3e50] text-sm">${w.numero}</span>
                                            ${w.nome}
                                        </div>
                                    </div>
                                    <a href="https://wa.me/55${wppNumber}?text=Parabéns, você ganhou na $UPER$ORTE com o número ${w.numero}!" target="_blank" class="w-10 h-10 bg-[#25D366] text-white flex items-center justify-center rounded-full hover:bg-[#128C7E] shadow transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 21.41a10.985 10.985 0 0 1-5.6-1.53l-6.22 1.63 1.66-6.07a10.992 10.992 0 1 1 10.16 5.97zm0-19.14a8.77 8.77 0 1 0 8.77 8.77 8.78 8.78 0 0 0-8.77-8.77zm4.8 12c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.57.71-.7.86-.26.16-.48.05a6.044 6.044 0 0 1-1.78-1.09 6.64 6.64 0 0 1-1.23-1.53c-.11-.2-.01-.31.1-.42.1-.1.22-.26.33-.4.11-.14.15-.22.22-.38.07-.15.03-.3-.02-.42-.05-.11-.5-.1.22-.68.21s-.33.27-.33.32a2.02 2.02 0 0 0 .61 1.41 5.925 5.925 0 0 0 1.94 1.34 13.4 13.4 0 0 0 2.44.82 2.924 2.924 0 0 0 1.34 0 2.053 2.053 0 0 0 .54-1.77 1.68 1.68 0 0 0-.25-.43z"></path></svg>
                                    </a>
                                </div>
                             `;
                            cont.insertAdjacentHTML('beforeend', box);
                        });
                        document.getElementById('modal-winners').classList.remove('hidden');
                        setTimeout(() => { document.getElementById('modal-winners').classList.add('opacity-100'); }, 10);
                        fetchRifas(); // Update table visually to reflect 'fechada'
                    } else {
                        showNotification('Atenção', 'Nenhum número pago nesta rifa para ser sorteado!', 'error');
                    }
                } else {
                    showNotification('Erro', data.error || 'Erro ao sortear.', 'error');
                }
            } catch (e) {
                console.error(e);
                showNotification('Erro', 'Erro fatal. Veja o console.', 'error');
            }
        });
        // Image Preview logic
        document.getElementById('edit-imagem-file').onchange = (e) => {
            const [file] = e.target.files;
            if (file) {
                document.getElementById('img-edit-rifa').src = URL.createObjectURL(file);
                document.getElementById('preview-edit-rifa').classList.remove('hidden');
            }
        };
        document.getElementById('edit-imagem').oninput = (e) => {
             const val = e.target.value;
             const img = document.getElementById('img-edit-rifa');
             const cont = document.getElementById('preview-edit-rifa');
             if(val && val.startsWith('http')) {
                 img.src = val;
                 cont.classList.remove('hidden');
             }
        };

        window.clearEditImage = function() {
            document.getElementById('edit-imagem-file').value = '';
            document.getElementById('edit-imagem').value = '';
            document.getElementById('img-edit-rifa').src = '';
            document.getElementById('preview-edit-rifa').classList.add('hidden');
        };

        fetchRifas();
    </script>
    <!-- Modal Notificação -->
    <div id="modal-notif" class="fixed inset-0 bg-black bg-opacity-80 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-[2rem] p-8 max-w-sm w-full text-center shadow-2xl relative border border-gray-100">
            <h2 id="notif-title" class="text-2xl font-black text-[#2c3e50] mb-4 uppercase tracking-tight italic">$UPER$ORTE</h2>
            <p id="notif-message" class="text-sm text-gray-500 mb-8 font-medium leading-relaxed">Informação aqui.</p>
            <button id="btn-close-notif" class="w-full bg-[#8e44ad] text-white font-black py-4 rounded-2xl shadow-lg uppercase text-xs tracking-widest hover:bg-[#7d3c98] transition-all">Entendido</button>
        </div>
    </div>

    <script>
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
    </script>
</body>

</html>