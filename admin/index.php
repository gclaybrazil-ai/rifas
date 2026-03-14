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
            <button id="btn-draw" class="bg-[#f1c40f] text-black font-bold px-4 py-2 rounded shadow hover:bg-yellow-500">Sortear</button>
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

    <!-- Modal Winner -->
    <div id="modal-winner" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full text-center shadow-2xl relative">
            <button id="btn-close-winner" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                 <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="inline-block p-4 rounded-full bg-yellow-100 mb-4">
                <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
            <h2 class="text-3xl font-black text-[#8e44ad] mb-2 uppercase tracking-wide">Temos um Vencedor!</h2>
            <p class="text-gray-500 mb-6 font-medium text-sm">O prêmio da sua rifa será entregue para:</p>
            
            <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 text-left mb-6 shadow-sm">
                <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-200">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider w-16">Número:</span>
                    <span id="winner-number" class="text-2xl font-black text-[#2c3e50] bg-yellow-400 w-12 h-12 rounded flex items-center justify-center shadow"></span>
                </div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider w-16">Nome:</span>
                    <span id="winner-name" class="font-bold text-gray-800 break-words flex-1">...</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider w-16">Whats:</span>
                    <span id="winner-whatsapp" class="font-mono text-sm text-[#00a650] break-words flex-1">...</span>
                </div>
            </div>

            <a id="winner-whatsapp-link" href="#" target="_blank" class="w-full inline-flex items-center justify-center gap-2 bg-[#25D366] text-white font-bold py-3 px-6 rounded-xl hover:bg-[#128C7E] transition-colors relative overflow-hidden group">
                Chamar no WhatsApp
            </a>
        </div>
        </div>
    </div>

    <!-- Modal Nova Rifa -->
    <div id="modal-new-rifa" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl relative">
            <button id="btn-close-new" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                 <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <h2 class="text-xl font-black text-[#2c3e50] mb-4 uppercase">Criar Nova Rifa</h2>
            
            <form id="form-new-rifa" class="flex flex-col gap-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase ml-1">Nome da Rifa</label>
                    <input type="text" id="new-nome" required class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#00a650] outline-none" placeholder="Ex: Sorteio do PIX">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase ml-1">Preço do Número (R$)</label>
                    <input type="number" step="0.01" id="new-preco" required class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#00a650] outline-none" placeholder="Ex: 0.10">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase ml-1">Quantidade de Números</label>
                    <select id="new-qtd" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#00a650] outline-none">
                        <option value="100">100 Números (00 a 99)</option>
                    </select>
                </div>
                <button type="submit" id="btn-submit-new" class="w-full bg-[#00a650] text-white font-bold py-3 mt-2 rounded-xl hover:bg-[#009647] uppercase text-sm">Criar e Ativar</button>
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

        document.getElementById('btn-draw').addEventListener('click', async () => {
             if(confirm('Sortear um número ganhador entre os PAGOS?')) {
                 const fd = new URLSearchParams();
                 fd.append('action', 'draw');
                 
                 const res = await fetch(API, { method: 'POST', body: fd });
                 const data = await res.json();
                 
                 if(data.error) {
                     alert(data.error);
                 } else {
                     document.getElementById('winner-number').textContent = data.winner;
                     document.getElementById('winner-name').textContent = data.user.nome;
                     document.getElementById('winner-whatsapp').textContent = data.user.whatsapp;
                     
                     // Limpar whatsapp para link da api (remove não numéricos)
                     const wppNumber = data.user.whatsapp.replace(/\D/g, '');
                     document.getElementById('winner-whatsapp-link').href = `https://wa.me/55${wppNumber}?text=Parabéns, você ganhou a rifa na Top Sorte com o número ${data.winner}!`;
                     
                     const modal = document.getElementById('modal-winner');
                     modal.classList.remove('hidden');
                     setTimeout(() => { modal.classList.add('opacity-100'); }, 10);
                 }
             }
        });

        document.getElementById('btn-close-winner').addEventListener('click', () => {
             const modal = document.getElementById('modal-winner');
             modal.classList.remove('opacity-100');
             setTimeout(() => { modal.classList.add('hidden'); }, 300);
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
            const nome = document.getElementById('new-nome').value;
            const preco = document.getElementById('new-preco').value;

            btn.disabled = true;
            btn.innerHTML = 'Criando...';

            const fd = new URLSearchParams();
            fd.append('action', 'create_rifa');
            fd.append('nome', nome);
            fd.append('preco', preco);

            try {
                await fetch(API, { method: 'POST', body: fd });
                alert('Rifa criada com sucesso!');
                window.location.reload();
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
