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
    <title>Publicar Ganhadores - $UPER$ORTE</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../frontend/png/cifrao.png">
</head>
<body class="bg-gray-50 text-gray-800 font-sans p-6 pb-32">

    <!-- Header / Navigation -->
    <div class="max-w-4xl mx-auto mb-6 bg-white p-6 rounded-lg shadow border border-gray-100 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-[#8e44ad]">Ganhadores</h1>
            <p class="text-sm text-gray-500">Publicações da Galeria de Ganhadores</p>
        </div>
        <a href="index.php" class="bg-gray-200 text-gray-700 font-bold px-4 py-2 rounded hover:bg-gray-300 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Voltar ao Painel
        </a>
    </div>

    <!-- Container actions -->
    <div class="max-w-4xl mx-auto mb-6 flex justify-end">
         <button onclick="openModal()" class="bg-[#00a650] text-white font-bold px-4 py-2 rounded shadow hover:bg-[#009647]">+ Nova Publicação</button>
    </div>

    <!-- Tabela -->
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="font-bold text-gray-700 uppercase tracking-wide">TODAS AS PUBLICAÇÕES</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-white border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="p-4">Foto</th>
                        <th class="p-4">Nome</th>
                        <th class="p-4">Número</th>
                        <th class="p-4">Descrição</th>
                        <th class="p-4 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <!-- Injetado via JS -->
                    <tr><td colspan="5" class="p-4 text-center">Carregando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Adicionar -->
    <div id="modal-pub" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md modal-box relative">
            <h2 class="text-xl font-black text-[#8e44ad] mb-4" id="modal-title">Nova Publicação</h2>
            <form id="form-pub">
                <input type="hidden" id="ipt-id" value="0">
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nome do Ganhador</label>
                    <input type="text" id="ipt-nome" class="w-full border border-gray-300 rounded p-2 focus:ring-[#8e44ad] focus:border-[#8e44ad] uppercase" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Número Premiado (Ex: #123)</label>
                    <input type="text" id="ipt-numero" class="w-full border border-gray-300 rounded p-2 focus:ring-[#8e44ad] focus:border-[#8e44ad]" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Foto do Ganhador (Opcional)</label>
                    <input type="file" id="ipt-foto" accept="image/*" class="w-full border border-gray-300 rounded p-2 text-sm bg-gray-50 focus:outline-none">
                    <p class="text-[10px] text-gray-400 mt-1">Sugerido: Rosto do ganhador ou pessoa com o prêmio.</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Descrição / Prêmio</label>
                    <textarea id="ipt-desc" class="w-full border border-gray-300 rounded p-2 h-20 focus:ring-[#8e44ad] focus:border-[#8e44ad]" required placeholder="Ex: Ganhou um iPhone 15 Pro Max na Rifa #12!"></textarea>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 font-bold text-gray-500 bg-gray-100 rounded hover:bg-gray-200 transition-colors">Cancelar</button>
                    <button type="submit" id="btn-save" class="px-4 py-2 font-bold text-white bg-[#00a650] rounded hover:bg-[#009647] shadow transition-colors">Publicar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const API = '../backend/api/admin.php';

        document.getElementById('form-pub').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-save');
            btn.textContent = 'Salvando...';

            const fd = new FormData();
            fd.append('action', 'save_publicacao');
            const editId = document.getElementById('ipt-id').value;
            if (editId > 0) {
                fd.append('id', editId);
            }
            fd.append('nome', document.getElementById('ipt-nome').value);
            fd.append('numero', document.getElementById('ipt-numero').value);
            fd.append('desc', document.getElementById('ipt-desc').value);
            
            const file = document.getElementById('ipt-foto').files[0];
            if(file) {
                fd.append('foto', file);
            }

            try {
                const res = await fetch(API, { method: 'POST', body: fd });
                const json = await res.json();
                if(json.success) {
                    closeModal();
                    fetchList();
                } else {
                    alert(json.error || 'Erro ao publicar');
                }
            } catch(e) { console.error(e); }
            btn.textContent = 'Publicar';
        });

        async function fetchList() {
            try {
                const res = await fetch(API + '?action=get_publicacoes_admin');
                const text = await res.text();
                // fix any possible PHP warning breaking JSON
                let json;
                try {
                    json = JSON.parse(text);
                } catch(e) {
                    // Extract json from possible output
                    const match = text.match(/\{.*\}/s);
                    if(match) json = JSON.parse(match[0]);
                }

                if(!json || !json.data) return;

                const tbody = document.getElementById('table-body');
                tbody.innerHTML = '';

                if(json.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-gray-400 font-bold">Nenhuma publicação ainda.</td></tr>';
                    return;
                }

                window.publicacoesData = json.data;

                json.data.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b hover:bg-gray-50';
                    
                    const imgUrl = p.imagem_url ? `../${p.imagem_url}` : 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQcbkK3z3Q93lZ3q71_gK_3y313hT38qf7VjA&usqp=CAU';

                    tr.innerHTML = `
                        <td class="p-4 align-middle">
                            <img src="${imgUrl}" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                        </td>
                        <td class="p-4 align-middle font-bold text-gray-800">${p.nome_ganhador}</td>
                        <td class="p-4 text-xs font-bold text-purple-700 bg-purple-50 px-2 py-1 rounded inline-block mt-4">${p.numero_premiado}</td>
                        <td class="p-4 align-middle text-xs text-gray-500 max-w-xs truncate">${p.premio_descricao}</td>
                        <td class="p-4 align-middle text-right">
                             <button onclick="editPub(${p.id})" class="text-xs bg-[#2c3e50] text-white font-bold px-3 py-1.5 rounded shadow hover:bg-gray-800 uppercase mr-1">Editar</button>
                             <button onclick="deletePub(${p.id})" class="text-xs bg-red-500 text-white font-bold px-3 py-1.5 rounded shadow hover:bg-red-600 uppercase">Excluir</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch(e) {
                console.error(e);
            }
        }

        async function deletePub(id) {
            if(!confirm('Certeza que deseja deletar?')) return;
            const fd = new URLSearchParams();
            fd.append('action', 'delete_publicacao');
            fd.append('id', id);
            await fetch(API, { method: 'POST', body: fd });
            fetchList();
        }

        function editPub(id) {
            const p = window.publicacoesData.find(x => x.id == id);
            if(!p) return;

            document.getElementById('ipt-id').value = p.id;
            document.getElementById('ipt-nome').value = p.nome_ganhador;
            document.getElementById('ipt-numero').value = p.numero_premiado;
            document.getElementById('ipt-desc').value = p.premio_descricao;
            document.getElementById('ipt-foto').value = ''; // can't view files
            
            document.getElementById('modal-title').innerText = 'Editar Publicação';
            document.getElementById('btn-save').innerText = 'Salvar Alterações';

            const modal = document.getElementById('modal-pub');
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.add('opacity-100'); modal.querySelector('.modal-box').classList.add('show'); }, 10);
        }

        function openModal() {
            document.getElementById('form-pub').reset();
            document.getElementById('ipt-id').value = '0';
            document.getElementById('ipt-numero').value = 'Número Premiado ';
            document.getElementById('modal-title').innerText = 'Nova Publicação';
            document.getElementById('btn-save').innerText = 'Publicar';

            const modal = document.getElementById('modal-pub');
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.add('opacity-100'); modal.querySelector('.modal-box').classList.add('show'); }, 10);
        }

        function closeModal() {
            const modal = document.getElementById('modal-pub');
            modal.querySelector('.modal-box').classList.remove('show');
            modal.classList.remove('opacity-100');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        // Init
        document.addEventListener('DOMContentLoaded', fetchList);
    </script>
    <style>
        .modal-box {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .modal-box.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</body>
</html>
