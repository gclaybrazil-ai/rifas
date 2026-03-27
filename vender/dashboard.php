<?php
require_once 'backend/config.php';

// Proteção da Página (No Banco SaaS)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] !== 'criador' && $_SESSION['usuario_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Sincronizar Role (Evita precisar de Login/Logout se mudou no banco)
$stmt = $pdo->prepare("SELECT role FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$current_role = $stmt->fetchColumn();
if ($current_role) {
    $_SESSION['usuario_role'] = $current_role;
}

$u_id = $_SESSION['usuario_id'];
$u_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$u_role = $_SESSION['usuario_role'];

// Buscar estatísticas simples do criador NO BANCO SAAS
try {
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM rifas WHERE usuario_id = ?");
    $stmtCount->execute([$u_id]);
    $total_rifas = $stmtCount->fetchColumn();

    // Buscar rifas recentes
    $stmtRifas = $pdo->prepare("SELECT * FROM rifas WHERE usuario_id = ? ORDER BY id DESC LIMIT 5");
    $stmtRifas->execute([$u_id]);
    $rifas_recentes = $stmtRifas->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $total_rifas = 0; $rifas_recentes = []; }

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel SaaS - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item-active { 
            background: #00a650; 
            color: white; 
            box-shadow: 0 10px 20px -5px rgba(0, 166, 80, 0.4);
        }
    </style>
</head>
<body class="bg-[#f8fafc] flex min-h-screen antialiased text-gray-800">

    <!-- Sidebar -->
    <aside class="w-72 bg-white border-r border-gray-100 flex flex-col hidden lg:flex">
        <div class="p-8">
            <a href="index.php" class="text-2xl font-black italic tracking-tighter text-[#00a650]">
                $UPER<span style="color: #2c3e50;">$ORTE</span>
            </a>
            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest mt-1">SaaS Platform</p>
        </div>

        <nav class="flex-grow px-6 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest sidebar-item-active text-white">
                Dashboard
            </a>
            
            <?php if($_SESSION['usuario_role'] === 'admin'): ?>
            <a href="admin/index.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-[#00a650] bg-green-50/50 border border-green-100 transition-all">
                Configurações Master
            </a>
            <?php endif; ?>
            <a href="#" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Minhas Rifas
            </a>
            <a href="#" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Vendas & Relatórios
            </a>
            <a href="configuracoes.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Configurações de Recebimento
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
            <h2 class="text-sm font-black text-gray-400 uppercase tracking-widest">Painel SaaS</h2>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest leading-none mb-1">Bem-vindo</p>
                    <p class="text-xs font-black text-gray-800 uppercase italic"><?php echo htmlspecialchars($u_nome); ?></p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-[#00a650] font-black italic">
                    <?php echo strtoupper(substr($u_nome, 0, 1)); ?>
                </div>
            </div>
        </header>

        <!-- Dynamic Content -->
        <div class="p-8 space-y-8">
            
            <div class="bg-blue-50 border border-blue-100 p-6 rounded-3xl flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-500 text-white rounded-2xl flex items-center justify-center shrink-0 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-sm font-black text-blue-900 uppercase tracking-widest italic leading-none mb-1">Banco de Dados Independente</h4>
                    <p class="text-xs text-blue-700 font-medium">Este painel opera no banco SaaS isolado. Manutenções no portal principal não afetam suas rifas.</p>
                </div>
            </div>

            <!-- Cards Resumo -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-100 relative overflow-hidden group">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-green-50 rounded-full blur-2xl group-hover:bg-green-100 transition-all"></div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 relative z-10">Campanhas Criadas</p>
                    <h3 class="text-4xl font-black text-gray-800 relative z-10"><?php echo $total_rifas; ?></h3>
                </div>
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-100 relative overflow-hidden group">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 relative z-10">Vendas do Mês</p>
                    <h3 class="text-4xl font-black text-gray-800 relative z-10 text-green-600">R$ 0,00</h3>
                </div>
                <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-100 relative overflow-hidden group">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 relative z-10">Tickets Vendidos</p>
                    <h3 class="text-4xl font-black text-gray-800 relative z-10">0</h3>
                </div>
            </div>

            <!-- Rifas Recentes -->
            <div class="bg-white rounded-[3rem] shadow-2xl border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-20">
                    <h3 class="text-lg font-black text-[#2c3e50] uppercase tracking-tighter italic">Suas Rifas no SaaS</h3>
                    <a href="criar-rifa.php" class="bg-gray-900 text-white text-[9px] font-black px-6 py-2 rounded-full uppercase tracking-widest shadow-xl hover:bg-black transition-all">+ Nova Campanha</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 text-[9px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                            <tr>
                                <th class="px-8 py-5">Nome da Rifa</th>
                                <th class="px-8 py-5">Status SaaS</th>
                                <th class="px-8 py-5">Progresso</th>
                                <th class="px-8 py-5 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($rifas_recentes)): ?>
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center">
                                        <p class="text-gray-400 font-medium italic text-sm">Você ainda não criou nenhuma rifa neste banco SaaS. <br> Comece sua primeira campanha agora!</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rifas_recentes as $r): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-8 py-5">
                                            <p class="text-xs font-black text-gray-800 uppercase italic mb-1"><?php echo htmlspecialchars($r['titulo']); ?></p>
                                            <p class="text-[9px] text-gray-400 uppercase font-bold tracking-widest">ID: #<?php echo str_pad($r['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                        </td>
                                        <td class="px-8 py-5">
                                            <?php if (($r['status'] ?? '') === 'ativa'): ?>
                                                <span class="text-[9px] font-black bg-green-100 text-green-700 px-3 py-1 rounded-full uppercase tracking-widest italic">Ativado 🔥</span>
                                            <?php else: ?>
                                                <span class="text-[9px] font-black bg-yellow-50 text-yellow-600 px-3 py-1 rounded-full uppercase tracking-widest italic tracking-tighter">Aguardando Ativação</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-8 py-5 font-black text-xs text-gray-400">0 / <?php echo $r['total_numeros']; ?></td>
                                        <td class="px-8 py-5 text-right">
                                            <a href="gerenciar-rifa.php?id=<?php echo $r['id']; ?>" class="text-[10px] font-black text-blue-600 uppercase tracking-widest hover:underline transition-all">Gerenciar Rifa</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</body>
</html>
