<?php
require_once 'backend/config.php';

// Proteção da Página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] !== 'criador' && $_SESSION['usuario_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$rifa_id = $_GET['id'] ?? null;
if (!$rifa_id) {
    die("Campanha não especificada.");
}

// 1. Fetch Raffle Info (Verify Ownership)
$stmt = $pdo->prepare("SELECT * FROM rifas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$rifa_id, $_SESSION['usuario_id']]);
$rifa = $stmt->fetch();

if (!$rifa && $_SESSION['usuario_role'] !== 'admin') {
    die("Rifa não encontrada ou você não tem permissão.");
}

// 2. Fetch Sales (Reservations)
$stmt = $pdo->prepare("SELECT * FROM reservas WHERE rifa_id = ? ORDER BY created_at DESC");
$stmt->execute([$rifa_id]);
$vendas = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Vendas - <?php echo $rifa['titulo']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] flex min-h-screen antialiased text-gray-800">

    <!-- Sidebar (Reusing dashboard style) -->
    <aside class="w-72 bg-white border-r border-gray-100 flex flex-col hidden lg:flex">
        <div class="p-8">
            <a href="dashboard.php" class="text-2xl font-black italic tracking-tighter text-[#00a650]">
                $UPER<span style="color: #2c3e50;">$ORTE</span>
            </a>
            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest mt-1">Gerenciamento de Vendas</p>
        </div>
        <nav class="flex-grow px-6 space-y-2">
            <a href="gerenciar-rifa.php?id=<?php echo $rifa_id; ?>" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                Painel da Rifa
            </a>
            <a href="#" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest bg-[#00a650] text-white shadow-xl">
                Relatório de Vendas
            </a>
            <a href="dashboard.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                Voltar Dashboard
            </a>
        </nav>
    </aside>

    <main class="flex-grow flex flex-col">
        <!-- Top Bar -->
        <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 px-8 py-4 flex justify-between items-center sticky top-0 z-50">
            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Vendas / <?php echo $rifa['titulo']; ?></h2>
            <div class="flex items-center gap-4">
                 <span class="text-[10px] bg-green-50 text-green-600 font-black px-4 py-2 rounded-xl">Total: R$ <?php echo number_format(array_sum(array_column($vendas, 'valor_total')), 2, ',', '.'); ?></span>
            </div>
        </header>

        <div class="p-8">
            <div class="bg-white rounded-[3.5rem] shadow-2xl border border-gray-100 overflow-hidden">
                <div class="p-8 lg:p-12">
                    
                    <div class="flex justify-between items-center mb-10">
                        <h3 class="text-xl font-black text-[#2c3e50] uppercase tracking-tighter italic">Lista de Reservas</h3>
                        <div class="flex gap-2">
                             <button class="bg-gray-50 text-[9px] font-black uppercase tracking-widest px-4 py-2 rounded-xl text-gray-400">Exportar CSV</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Data</th>
                                    <th class="px-6 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Comprador</th>
                                    <th class="px-6 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">WhatsApp</th>
                                    <th class="px-6 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Cotas</th>
                                    <th class="px-6 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                                    <th class="px-6 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                    <th class="px-6 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach($vendas as $v): 
                                    $status_css = [
                                        'pendente' => 'bg-orange-50 text-orange-600',
                                        'pago' => 'bg-green-50 text-green-600',
                                        'cancelado' => 'bg-red-50 text-red-600'
                                    ];
                                ?>
                                <tr class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 py-6">
                                        <p class="text-xs font-black text-gray-800"><?php echo date('d/m/Y', strtotime($v['created_at'])); ?></p>
                                        <p class="text-[9px] text-gray-400 uppercase"><?php echo date('H:i', strtotime($v['created_at'])); ?></p>
                                    </td>
                                    <td class="px-6 py-6">
                                        <p class="text-xs font-bold text-gray-800"><?php echo $v['nome']; ?></p>
                                        <p class="text-[9px] text-gray-400 uppercase tracking-widest">ID #<?php echo $v['id']; ?></p>
                                    </td>
                                    <td class="px-6 py-6">
                                        <a href="https://wa.me/<?php echo $v['whatsapp']; ?>" class="text-xs font-medium text-gray-500 hover:text-[#00a650] flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.417-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.305 1.652zm6.599-3.352l.3.179c1.472.873 3.166 1.334 4.89 1.335 5.432 0 9.851-4.42 9.851-9.854 0-2.628-1.023-5.099-2.88-6.958s-4.329-2.881-6.957-2.881c-5.433 0-9.851 4.417-9.851 9.853 0 1.831.503 3.618 1.458 5.174l.192.313-1.11 4.053 4.147-1.087zM17.446 14.12c-.3-.15-1.771-.874-2.045-.974-.275-.1-.475-.15-.675.15-.2.3-.775.974-.95 1.174-.175.2-.35.225-.65.075-.3-.15-1.265-.467-2.41-1.487-.89-.794-1.49-1.774-1.665-2.074-.175-.3-.018-.462.13-.611.134-.133.3-.349.45-.524.15-.175.2-.3.3-.5s.05-.375-.025-.525c-.075-.15-.675-1.625-.925-2.225-.25-.6-.503-.513-.675-.522l-.574-.01c-.2 0-.525.075-.8.375s-1.05 1.025-1.05 2.5 1.075 2.9 1.225 3.1c.15.2 2.11 3.22 5.11 4.52.714.309 1.27.494 1.703.631.714.227 1.365.195 1.88.118.573-.085 1.771-.724 2.021-1.424.25-.7.25-1.3.175-1.425-.075-.125-.275-.2-.575-.35z"></path></svg>
                                            <?php echo $v['whatsapp']; ?>
                                        </a>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex gap-1 overflow-hidden max-w-[120px]">
                                             <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest italic">Ver Cotas</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <p class="text-sm font-black text-gray-800">R$ <?php echo number_format($v['valor_total'], 2, ',', '.'); ?></p>
                                    </td>
                                    <td class="px-6 py-6">
                                        <span class="text-[8px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full <?php echo $status_css[$v['status']]; ?>">
                                            <?php echo strtoupper($v['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-6 text-right">
                                        <div class="flex justify-end gap-2 group-hover:opacity-100 opacity-0 transition-all">
                                            <?php if($v['status'] == 'pendente'): ?>
                                            <button onclick="confirmPayment(<?php echo $v['id']; ?>)" class="bg-green-500 text-white p-2 rounded-xl hover:bg-green-600 shadow-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></button>
                                            <button onclick="cancelPayment(<?php echo $v['id']; ?>)" class="bg-red-50 text-red-400 p-2 rounded-xl hover:bg-red-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
        async function updateStatus(id, status) {
            const btn = event.currentTarget;
            btn.disabled = true;
            try {
                const response = await fetch('backend/api/atualizar_status_reserva.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reserva_id: id, novo_status: status })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Erro ao atualizar.');
                    btn.disabled = false;
                }
            } catch (error) {
                alert('Erro na conexão.');
                btn.disabled = false;
            }
        }

        async function confirmPayment(id) {
            if(!confirm("Deseja confirmar o pagamento manual desta reserva?")) return;
            await updateStatus(id, 'pago');
        }
        async function cancelPayment(id) {
            if(!confirm("Deseja cancelar esta reserva? Os números ficarão livres novamente.")) return;
            await updateStatus(id, 'cancelado');
        }
    </script>
</body>
</html>
