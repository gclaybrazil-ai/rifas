<?php
session_start();
if(!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Top Sorte</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans p-6">

    <div class="max-w-4xl mx-auto mb-6 bg-white p-6 rounded-lg shadow border border-gray-100 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-[#8e44ad]">Painel Administrativo</h1>
            <p class="text-sm text-gray-500">Gestão da Rifa</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-2 flex-wrap justify-end">
            <button id="btn-new-rifa" class="bg-[#00a650] text-white font-bold px-4 py-2 rounded shadow hover:bg-[#009647]">Nova Rifa</button>
            <a href="rifas.php" class="bg-blue-500 text-white font-bold px-4 py-2 rounded shadow hover:bg-blue-600">Gerenciar Rifas</a>
            <button id="btn-integrations" class="bg-indigo-500 text-white font-bold px-4 py-2 rounded shadow hover:bg-indigo-600">Integrações</button>
            <button id="btn-reset" class="bg-red-500 text-white font-bold px-4 py-2 rounded shadow hover:bg-red-600 focus:outline-none" title="Limpar reservas atuais">Resetar</button>
            <a href="../index.html" class="bg-gray-200 text-gray-700 font-bold px-4 py-2 rounded hover:bg-gray-300">Site</a>
            <a href="../backend/api/logout.php" class="bg-gray-800 text-white font-bold px-4 py-2 rounded hover:bg-black">Sair</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" id="stats-grid">
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
    </div>

    <!-- Tabela Reservas -->
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-700 uppercase tracking-wide">Últimas Reservas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase font-bold text-xs">
                        <th class="p-4 border-b">ID / Nome</th>
                        <th class="p-4 border-b">WhatsApp</th>
                        <th class="p-4 border-b">Valor</th>
                        <th class="p-4 border-b">Status</th>
                        <th class="p-4 border-b text-right">Ação</th>
                    </tr>
                </thead>
                <tbody id="table-reservas">
                    <tr><td colspan="5" class="p-4 text-center text-gray-500">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Integracoes -->
    <div id="modal-integrations" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-left shadow-2xl relative">
            <button id="btn-close-integrations" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                 <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="flex items-center gap-3 mb-6">
                <div class="p-3 bg-indigo-100 rounded-lg text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-800">Integrações</h2>
                    <p class="text-xs text-gray-500">Gateway de Pagamento PIX</p>
                </div>
            </div>
            
            <form id="form-integrations" class="flex flex-col gap-4">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Escolha o Gateway</label>
                    <select id="gateway-provider" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="mercadopago">Mercado Pago</option>
                        <option value="efi">Efí Bank (Gerencianet)</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-1 block">Token / Access Key</label>
                    <input type="password" id="gateway-token" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="APP_USR-...">
                </div>
                
                <button type="submit" id="btn-save-integrations" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-xl shadow uppercase text-sm mt-2 hover:bg-indigo-700 transition-colors">
                    Salvar Configurações
                </button>
            </form>
        </div>
        </div>
    </div>

    <!-- Modal Nova Rifa -->
    <div id="modal-new-rifa" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl relative max-h-[95vh] overflow-y-auto">
            <button id="btn-close-new" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                 <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <h2 class="text-xl font-black text-[#2c3e50] mb-4 uppercase">Criar Nova Rifa</h2>
            
            <form id="form-new-rifa" class="flex flex-col gap-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Nome da Rifa</label>
                        <input type="text" id="new-nome" required class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#00a650] outline-none" placeholder="Ex: Sorteio do PIX">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Preço do Número (R$)</label>
                        <input type="number" step="0.01" id="new-preco" required class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#00a650] outline-none" placeholder="Ex: 0.10">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Imagem Banner (URL ou Arquivo Próprio)</label>
                    <div class="flex gap-2">
                        <input type="url" id="new-imagem" class="w-1/2 bg-gray-50 border border-gray-200 rounded-lg p-2 text-[10px] md:text-xs focus:ring-2 focus:ring-[#00a650] outline-none" placeholder="Ou cole o Link https://...">
                        <input type="file" id="new-imagem-file" accept="image/*" class="w-1/2 bg-gray-50 border border-gray-200 rounded-lg p-1.5 text-[10px] md:text-xs file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-[#00a650] file:text-white file:font-bold hover:file:bg-[#009647]">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Quantidade de Números</label>
                        <input type="number" id="new-qtd" min="10" max="10000" required class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#00a650] outline-none" placeholder="Mín: 10, Máx: 10000">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Sorteado Por</label>
                        <select id="new-sorteio" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#00a650] outline-none">
                            <option value="Loteria Federal">Loteria Federal</option>
                            <option value="Jogo do Bicho">Jogo do Bicho</option>
                            <option value="Sorteador.com.br">Sorteador.com.br</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-3 mt-1">
                    <p class="text-[10px] font-black text-[#8e44ad] uppercase mb-2">Prêmios Específicos (Opcional)</p>
                    <div class="flex flex-col gap-2">
                        <input type="text" id="new-premio1" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-[#8e44ad] outline-none" placeholder="1º Prêmio (Ex: iPhone 16)">
                        <input type="text" id="new-premio2" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-[#8e44ad] outline-none" placeholder="2º Prêmio">
                        <input type="text" id="new-premio3" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-[#8e44ad] outline-none" placeholder="3º Prêmio">
                        <input type="text" id="new-premio4" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-[#8e44ad] outline-none" placeholder="4º Prêmio">
                        <input type="text" id="new-premio5" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-[#8e44ad] outline-none" placeholder="5º Prêmio">
                    </div>
                </div>

                <button type="submit" id="btn-submit-new" class="w-full bg-[#00a650] text-white font-bold py-3 mt-3 rounded-xl hover:bg-[#009647] uppercase text-sm shadow">Criar e Ativar Rifa</button>
            </form>
        </div>
    </div>


    <script>
        const API = '../backend/api/admin.php';

        async function fetchStats() {
            try {
                const res = await fetch(`${API}?action=stats`);
                const data = await res.json();
                
                document.getElementById('stat-livre').textContent = data.stats['disponivel'] || 0;
                document.getElementById('stat-reservado').textContent = data.stats['reservado'] || 0;
                document.getElementById('stat-pago').textContent = data.stats['pago'] || 0;
                document.getElementById('stat-faturamento').textContent = parseFloat(data.faturamento).toLocaleString('pt-BR', {style:'currency', currency:'BRL'});

                const tbody = document.getElementById('table-reservas');
                tbody.innerHTML = '';
                
                if(data.reservas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-400 font-medium">Nenhuma reserva encontrada.</td></tr>';
                    return;
                }

                data.reservas.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b hover:bg-gray-50';
                    
                    let bgStatus = 'bg-gray-100 text-gray-600';
                    if(r.status === 'pago') bgStatus = 'bg-purple-100 text-purple-700';
                    else if(r.status === 'pendente') bgStatus = 'bg-yellow-100 text-yellow-700';

                    let btn = r.status === 'pendente' 
                        ? `<button onclick="markPaid(${r.id})" class="text-xs bg-green-500 text-white px-3 py-1 rounded shadow hover:bg-green-600 focus:outline-none transition-colors">Marcar Pago</button>`
                        : `<span class="text-xs text-gray-400">—</span>`;

                    tr.innerHTML = `
                        <td class="p-4 align-top">
                            <div class="font-bold text-gray-800">#${r.id}</div>
                            <div class="text-gray-500 text-xs mt-1 truncate max-w-[150px] shadow-sm bg-white border px-2 py-1 rounded inline-block" title="${r.nome}">${r.nome}</div>
                        </td>
                        <td class="p-4 font-mono text-xs text-[#00a650] align-top whitespace-nowrap">${r.whatsapp}</td>
                        <td class="p-4 text-sm font-bold text-gray-700 align-top">${parseFloat(r.valor_total).toLocaleString('pt-BR', {style:'currency', currency:'BRL'})}</td>
                        <td class="p-4 align-top">
                            <span class="px-2 py-1 rounded text-[10px] uppercase font-bold tracking-wider ${bgStatus}">${r.status}</span>
                        </td>
                        <td class="p-4 text-right align-top">${btn}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch(e) {
                console.error(e);
            }
        }

        async function markPaid(id) {
            if(!confirm('Marcar esta reserva como PAGA manualmente?')) return;
            const fd = new URLSearchParams();
            fd.append('action', 'mark_paid');
            fd.append('id', id);

            await fetch(API, { method: 'POST', body: fd });
            fetchStats();
        }

        document.getElementById('btn-reset').addEventListener('click', async () => {
             if(prompt('Tem certeza? Digite "RESETAR" para liberar todos os números e excluir as reservas.') === 'RESETAR') {
                 const fd = new URLSearchParams();
                 fd.append('action', 'reset_rifa');
                 await fetch(API, { method: 'POST', body: fd });
                 fetchStats();
                 alert('Rifa resetada.');
             }
        });

        document.getElementById('btn-integrations').addEventListener('click', async () => {
             const modal = document.getElementById('modal-integrations');
             
             // Fetch setup
             const res = await fetch(`${API}?action=get_integration`);
             const data = await res.json();
             if(data.gateway) document.getElementById('gateway-provider').value = data.gateway;
             if(data.gateway_token) document.getElementById('gateway-token').value = data.gateway_token;

             modal.classList.remove('hidden');
             setTimeout(() => { modal.classList.add('opacity-100'); }, 10);
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
             
             const fd = new URLSearchParams();
             fd.append('action', 'save_integration');
             fd.append('gateway', document.getElementById('gateway-provider').value);
             fd.append('token', document.getElementById('gateway-token').value);
             
             await fetch(API, { method: 'POST', body: fd });
             
             btn.innerHTML = 'Salvo com sucesso!';
             setTimeout(() => {
                 document.getElementById('btn-close-integrations').click();
                 btn.innerHTML = 'Salvar Configurações';
             }, 1000);
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
            if(fileInput.files.length > 0) {
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
                
                if(res.success) {
                    alert('Rifa criada com sucesso!');
                    window.location.reload();
                } else {
                    alert(res.error || 'Erro ao criar rifa');
                    btn.disabled = false;
                    btn.innerHTML = 'Criar e Ativar Rifa';
                }
            } catch(err) {
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
