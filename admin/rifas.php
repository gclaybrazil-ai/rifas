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
    <title>Gerenciar Rifas - Top Sorte</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans p-6">

    <div class="max-w-4xl mx-auto mb-6 bg-white p-6 rounded-lg shadow border border-gray-100 flex flex-col md:flex-row justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-[#8e44ad]">Gerenciar Rifas</h1>
            <p class="text-sm text-gray-500">Controle completo sobre seus Sorteios</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-2">
            <a href="index.php" class="bg-gray-200 text-gray-700 font-bold px-4 py-2 rounded hover:bg-gray-300">⬅ Voltar ao Painel</a>
        </div>
    </div>

    <!-- Tabela Rifas -->
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-700 uppercase tracking-wide">Todas as Rifas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase font-bold text-xs">
                        <th class="p-4 border-b">ID</th>
                        <th class="p-4 border-b">Nome da Rifa</th>
                        <th class="p-4 border-b">Preço</th>
                        <th class="p-4 border-b">Acesso</th>
                        <th class="p-4 border-b">Status</th>
                        <th class="p-4 border-b text-right">Ação</th>
                    </tr>
                </thead>
                <tbody id="table-rifas">
                    <tr><td colspan="6" class="p-4 text-center text-gray-500">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Sortear Setup -->
    <div id="modal-draw" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl relative">
            <button id="btn-close-draw" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                 <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <h2 class="text-xl font-black text-[#2c3e50] mb-2 uppercase flex items-center gap-2">
                <svg class="w-6 h-6 text-[#f1c40f]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Configurar Sorteio
            </h2>
            <p class="text-xs text-gray-500 mb-4">Atenção: Rodar este sorteio fechará a rifa definitivamente.</p>
            
            <form id="form-draw" class="flex flex-col gap-3">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase ml-1 block mb-1">Quantidade de Ganhadores (Prêmios)</label>
                    <select id="draw-qtd" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm focus:ring-2 focus:ring-[#f1c40f] outline-none">
                        <option value="1">1 Ganhador</option>
                        <option value="2">2 Ganhadores</option>
                        <option value="3">3 Ganhadores</option>
                        <option value="4">4 Ganhadores</option>
                        <option value="5">5 Ganhadores</option>
                    </select>
                </div>
                <button type="submit" id="btn-submit-draw" class="w-full bg-[#f1c40f] text-black font-black py-4 mt-2 rounded-xl hover:bg-yellow-500 transition-colors uppercase text-sm shadow">Sortear Ganhadores</button>
            </form>
        </div>
    </div>

    <!-- Modal Resultados Sorteados -->
    <div id="modal-winners" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full text-center shadow-2xl relative max-h-[90vh] flex flex-col">
            <button id="btn-close-winners" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
                 <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="inline-block p-4 rounded-full bg-yellow-100 mb-2 mx-auto mt-2">
                <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
            <h2 class="text-3xl font-black text-[#8e44ad] mb-1 uppercase tracking-wide">Vencedores!</h2>
            <p class="text-gray-500 mb-6 font-medium text-xs">Os sortudos do prêmio são:</p>
            
            <div id="winners-container" class="flex-1 overflow-y-auto pr-2">
                <!-- Injetados aqui -->
            </div>
            
            <p class="text-[10px] text-gray-400 mt-4 uppercase font-bold tracking-wider">A rifa foi fechada com sucesso.</p>
        </div>
    </div>

    <script>
        const API = '../backend/api/admin.php';

        async function fetchRifas() {
            try {
                const res = await fetch(`${API}?action=get_rifas_list`);
                const data = await res.json();
                
                const tbody = document.getElementById('table-rifas');
                tbody.innerHTML = '';
                
                if(!data.rifas || data.rifas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-gray-400 font-medium">Nenhuma rifa encontrada.</td></tr>';
                    return;
                }

                data.rifas.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b hover:bg-gray-50';
                    
                    let bgStatus = r.status === 'aberta' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600';

                    let actions = ``;
                    
                    if(r.status === 'aberta') {
                        actions += `<button onclick="openDrawModal(${r.id})" class="text-xs bg-[#f1c40f] text-black font-bold px-3 py-1 rounded shadow hover:bg-yellow-500 mr-2">Sortear</button>`;
                    }

                    actions += `<button onclick="deleteRifa(${r.id})" class="text-xs bg-red-500 text-white px-3 py-1 rounded shadow hover:bg-red-600">Excluir</button>`;

                    const precoNum = parseFloat(r.preco_numero).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                    
                    tr.innerHTML = `
                        <td class="p-4 align-middle font-bold text-gray-500">#${r.id}</td>
                        <td class="p-4 align-middle">
                            <div class="font-bold text-gray-800">${r.nome}</div>
                            <div class="text-[10px] text-gray-400 mt-1 uppercase">${r.quantidade_numeros} Números</div>
                        </td>
                        <td class="p-4 align-middle font-black text-gray-700">${precoNum}</td>
                        <td class="p-4 align-middle">
                            <a href="../rifa.html?id=${r.id}" target="_blank" class="text-blue-500 hover:underline text-xs">Visitar ↗</a>
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
            } catch(e) {
                console.error(e);
            }
        }

        /* Variables for the draw */
        let currentDrawRifaId = 0;

        window.openDrawModal = function(id) {
             currentDrawRifaId = id;
             document.getElementById('modal-draw').classList.remove('hidden');
             setTimeout(() => { document.getElementById('modal-draw').classList.add('opacity-100'); }, 10);
        };

        window.deleteRifa = async function(id) {
            if(!confirm('CUIDADO! Isso irá excluir permanentemente a Rifa, suas Vendas e Numerações. Deseja continuar?')) return;
            if(prompt('Digite EXCLUIR para confirmar') !== 'EXCLUIR') return;
            
            const fd = new URLSearchParams();
            fd.append('action', 'delete_rifa');
            fd.append('id', id);

            await fetch(API, { method: 'POST', body: fd });
            fetchRifas();
        };

        fetchRifas();

        // Close functions
        document.getElementById('btn-close-draw').addEventListener('click', () => {
             const m = document.getElementById('modal-draw');
             m.classList.remove('opacity-100');
             setTimeout(() => { m.classList.add('hidden'); }, 300);
        });
        document.getElementById('btn-close-winners').addEventListener('click', () => {
             const m = document.getElementById('modal-winners');
             m.classList.remove('opacity-100');
             setTimeout(() => { m.classList.add('hidden'); }, 300);
        });

        // Submit Draw
        document.getElementById('form-draw').addEventListener('submit', async (e) => {
             e.preventDefault();
             const btn = document.getElementById('btn-submit-draw');
             const qtd = document.getElementById('draw-qtd').value;
             
             btn.innerHTML = 'Sorteando...';
             btn.disabled = true;

             const fd = new URLSearchParams();
             fd.append('action', 'draw_multiple');
             fd.append('rifa_id', currentDrawRifaId);
             fd.append('qtd', qtd);

             try {
                 const res = await fetch(API, { method: 'POST', body: fd });
                 const data = await res.json();
                 
                 btn.innerHTML = 'Sortear Ganhadores';
                 btn.disabled = false;
                 document.getElementById('btn-close-draw').click();

                 if(data.success) {
                     if(data.winners && data.winners.length > 0) {
                         const cont = document.getElementById('winners-container');
                         cont.innerHTML = '';
                         data.winners.forEach((w, index) => {
                             const wppNumber = w.whatsapp.replace(/\D/g, '');
                             const box = `
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-left shadow-sm flex items-center justify-between mb-2">
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase mb-1">Prêmio ${index+1}</div>
                                        <div class="font-bold text-gray-800 text-lg flex items-center gap-2">
                                            <span class="bg-yellow-400 w-8 h-8 rounded flex items-center justify-center shadow font-black text-[#2c3e50] text-sm">${w.numero}</span>
                                            ${w.nome}
                                        </div>
                                    </div>
                                    <a href="https://wa.me/55${wppNumber}?text=Parabéns, você ganhou na Top Sorte com o número ${w.numero}!" target="_blank" class="w-10 h-10 bg-[#25D366] text-white flex items-center justify-center rounded-full hover:bg-[#128C7E] shadow transition-colors">
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
                         alert('Nenhum número pago nesta rifa para ser sorteado!');
                     }
                 } else {
                     alert(data.error || 'Erro ao sortear.');
                 }
             } catch(e) {
                 console.error(e);
                 alert('Erro fatal. Veja o console.');
             }
        });
    </script>
</body>
</html>
