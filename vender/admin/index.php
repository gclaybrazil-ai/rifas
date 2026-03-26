<?php
require_once '../backend/config.php';

// Proteção da Página (Somente Admin Global)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] !== 'admin') {
    // Sincronizar role em tempo real (caso tenha sido promovido agora)
    $stmt = $pdo->prepare("SELECT role FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $role = $stmt->fetchColumn();
    if ($role === 'admin') {
        $_SESSION['usuario_role'] = 'admin';
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8"><title>Acesso Negado - $UPER$ORTE</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;900&display=swap" rel="stylesheet">
        </head>
        <body class="bg-gray-50 text-gray-900 font-['Inter'] min-h-screen flex items-center justify-center p-8">
            <div class="max-w-md w-full text-center space-y-8">
                <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto border border-red-200 shadow-xl animate-pulse">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 17c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h1 class="text-3xl font-black uppercase italic tracking-tighter leading-none">Área Restrita <br> <span class="text-red-500">Apenas Admins</span></h1>
                <p class="text-gray-500 text-sm font-medium">Sua conta atual não possui privilégios de Administrador Master para acessar estas configurações.</p>
                <div class="pt-6">
                    <a href="../dashboard.php" class="inline-block bg-white border border-gray-200 text-gray-900 font-black px-8 py-4 rounded-2xl text-[10px] uppercase tracking-widest hover:bg-gray-50 transition-all shadow-sm">Voltar para o Dashboard</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// 1. Fetch Pendentes (Rifas que precisam de ativação)
$stmt = $pdo->prepare("
    SELECT r.*, u.username as criador_nome 
    FROM rifas r 
    JOIN usuarios u ON r.usuario_id = u.id 
    WHERE r.status = 'pendente_ativacao' 
    ORDER BY r.created_at DESC
");
$stmt->execute();
$pendentes = $stmt->fetchAll();

$u_nome = $_SESSION['usuario_nome'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Admin - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item-active {
            background: #00a650;
            color: white;
            box-shadow: 0 10px 20px -5px rgba(0, 166, 80, 0.3);
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
            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest mt-1 italic">Master Admin Panel</p>
        </div>

        <nav class="flex-grow px-6 space-y-2">
            <a href="index.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest sidebar-item-active">
                Gerenciar Campanhas
            </a>
            <a href="configuracoes.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                Configuração Global
            </a>
            <div class="pt-4 pb-2 px-6">
                <hr class="border-gray-50">
            </div>
            <a href="../dashboard.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 transition-all">
                Ver Meu Dashboard
            </a>
        </nav>

        <div class="p-6 border-t border-gray-50">
            <a href="../logout.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-red-400 hover:bg-red-50 transition-all">
                Sair do Sistema
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col">
        <!-- Top Bar -->
        <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 px-8 py-4 flex justify-between items-center sticky top-0 z-50">
            <div>
                <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest">Painel Administrativo</h2>
                <p class="text-[9px] font-bold text-gray-400 uppercase">Visão Geral da Plataforma SaaS</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest leading-none mb-1">Master Account</p>
                    <p class="text-xs font-black text-gray-800 uppercase italic"><?php echo htmlspecialchars($u_nome); ?></p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center text-[#00a650] font-black italic border border-green-100">
                    <?php echo strtoupper(substr($u_nome, 0, 1)); ?>
                </div>
            </div>
        </header>

        <section class="p-8">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-3xl font-black text-[#2c3e50] tracking-tighter uppercase italic leading-[0.9] mb-2">
                        Ativações <span class="text-[#00a650]">Pendentes.</span>
                    </h1>
                    <p class="text-gray-400 font-medium text-sm">Libere novas campanhas que aguardam pagamento.</p>
                </div>
            </div>

            <?php if (empty($pendentes)): ?>
                <div class="bg-white rounded-[2.5rem] border border-gray-100 p-16 text-center shadow-sm">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-300 uppercase tracking-widest italic">Tudo em dia!</h3>
                    <p class="text-gray-400 mt-2 font-medium">Não há campanhas aguardando ativação no momento.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($pendentes as $p): ?>
                        <div class="bg-white rounded-3xl border border-gray-100 p-6 flex items-center justify-between shadow-sm hover:shadow-md transition-all">
                            <div class="flex items-center gap-6">
                                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center border border-gray-100 overflow-hidden">
                                     <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-gray-800 uppercase italic mb-1"><?php echo htmlspecialchars($p['titulo']); ?></p>
                                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest leading-none">Organizador: <span class="text-green-600"><?php echo htmlspecialchars($p['criador_nome']); ?></span></p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="text-[8px] font-black bg-yellow-50 text-yellow-600 px-2 py-0.5 rounded-full uppercase tracking-tighter">Aguardando R$ 7,00</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-8">
                                <div class="text-right">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Potencial de Venda</p>
                                    <p class="text-lg font-black text-gray-800 italic">R$ <?php echo number_format($p['total_numeros'] * $p['valor_numero'], 2, ',', '.'); ?></p>
                                </div>
                                <button onclick="aprovarRifa(<?php echo $p['id']; ?>)" 
                                        class="bg-[#00a650] text-white font-black px-8 py-4 rounded-2xl text-[10px] uppercase tracking-widest hover:bg-[#009647] shadow-lg shadow-green-500/20 active:scale-95 transition-all">
                                    Aprovar Ativação
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        async function aprovarRifa(id) {
            const res = await Swal.fire({
                title: 'Confirmar Ativação?',
                text: "Isso liberará a rifa para o criador começar a vender.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#00a650',
                cancelButtonColor: '#f3f4f6',
                cancelButtonTextColor: '#000',
                confirmButtonText: 'Sim, Ativar!',
                cancelButtonText: 'Voltar',
                background: '#fff',
                color: '#1a1a1a'
            });

            if (res.isConfirmed) {
                try {
                    const response = await fetch('../backend/api/aprovar_rifa.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ rifa_id: id })
                    });
                    const data = await response.json();
                    if (data.success) {
                        Swal.fire({
                            icon: 'success', title: 'Sucesso!', text: 'Rifa ativada com sucesso.',
                            confirmButtonColor: '#00a650'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Erro!', data.error || 'Falha ao ativar rifa.', 'error');
                    }
                } catch (e) {
                    Swal.fire('Erro!', 'Erro na conexão com o servidor.', 'error');
                }
            }
        }
    </script>
</body>
</html>
