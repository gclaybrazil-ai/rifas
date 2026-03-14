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
                        actions += `<button onclick="setStatus(${r.id}, 'fechada')" class="text-xs bg-gray-500 text-white px-3 py-1 rounded shadow hover:bg-gray-600 mr-2">Finalizar (Fechar)</button>`;
                    } else {
                        actions += `<button onclick="setStatus(${r.id}, 'aberta')" class="text-xs bg-green-500 text-white px-3 py-1 rounded shadow hover:bg-green-600 mr-2">Reabrir</button>`;
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

        window.setStatus = async function(id, status) {
            if(!confirm(`Deseja alterar a rifa para: ${status.toUpperCase()}?`)) return;
            const fd = new URLSearchParams();
            fd.append('action', 'set_rifa_status');
            fd.append('id', id);
            fd.append('status', status);

            await fetch(API, { method: 'POST', body: fd });
            fetchRifas();
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
    </script>
</body>
</html>
