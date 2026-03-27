<?php
require_once 'backend/config.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_role'] !== 'criador' && $_SESSION['usuario_role'] !== 'admin')) {
    header("Location: login.php");
    exit;
}

$u_id = $_SESSION['usuario_id'];
$r_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM rifas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$r_id, $u_id]);
$rifa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rifa) {
    header("Location: dashboard.php");
    exit;
}

// Estatísticas
try {
    $stmtStats = $pdo->prepare("SELECT COUNT(CASE WHEN status='pago' THEN 1 END) as pagos, COUNT(CASE WHEN status='reservado' THEN 1 END) as reservados FROM numeros WHERE rifa_id = ?");
    $stmtStats->execute([$r_id]);
    $stats = $stmtStats->fetch();
    $pagos = $stats['pagos'] ?? 0;
    $reservados = $stats['reservados'] ?? 0;
} catch (Exception $e) {
    $pagos = 0;
    $reservados = 0;
}

$total_arrecadado = $pagos * $rifa['valor_numero'];
$porcentagem = $rifa['total_numeros'] > 0 ? ($pagos / $rifa['total_numeros']) * 100 : 0;

// Status badge
$status_map = [
    'ativo'              => ['label' => 'ATIVA 🔥', 'style' => 'background:#ecfdf5; color:#16a34a; border:1px solid #bbf7d0;'],
    'ativa'              => ['label' => 'ATIVA 🔥', 'style' => 'background:#ecfdf5; color:#16a34a; border:1px solid #bbf7d0;'],
    'pendente_ativacao'  => ['label' => 'PENDENTE',   'style' => 'background:#fff7ed; color:#f97316; border:1px solid #fed7aa;'],
    'pendente'           => ['label' => 'PENDENTE',   'style' => 'background:#fff7ed; color:#f97316; border:1px solid #fed7aa;'],
    'encerrado'          => ['label' => 'ENCERRADO',  'style' => 'background:#f3f4f6; color:#9ca3af; border:1px solid #e5e7eb;'],
    'suspenso'           => ['label' => 'SUSPENSO',   'style' => 'background:#fef2f2; color:#ef4444; border:1px solid #fecaca;'],
];
$status_info = $status_map[$rifa['status']] ?? ['label' => strtoupper($rifa['status']), 'style' => 'background:#f3f4f6; color:#9ca3af; border:1px solid #e5e7eb;'];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Rifa - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f4f6f9;
        }

        .action-card {
            background: #fff;
            border: 1px solid #eef0f3;
            border-radius: 1.25rem;
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .action-card:hover {
            border-color: #00a650;
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 166, 80, 0.1);
        }

        .main-card {
            background: #fff;
            border-radius: 2.5rem;
            box-shadow: 0 8px 48px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.06);
        }

        .progress-track {
            background: #f0f2f5;
            height: 5px;
            border-radius: 99px;
            overflow: hidden;
        }

        .progress-fill {
            background: #00a650;
            height: 100%;
            border-radius: 99px;
            transition: width 1s ease;
        }

        .value-box {
            background: #f8fafc;
            border: 1px solid #eef0f3;
            border-radius: 1.25rem;
        }
    </style>
</head>

<body class="flex min-h-screen">

    <!-- SIDEBAR ESTREITA (exata como no print) -->
    <aside class="w-44 bg-white border-r border-gray-100 flex flex-col min-h-screen hidden lg:flex">
        <div class="px-4 py-6">
            <a href="dashboard.php">
                <div class="text-xl font-black italic text-[#00a650] tracking-tighter leading-none">$UPER<span
                        class="text-gray-800">$ORTE</span></div>
                <div class="text-[7px] font-black text-gray-300 uppercase tracking-widest mt-1">SaaS Platform</div>
            </a>
        </div>

        <nav class="flex-grow px-3 space-y-1 mt-2">
            <a href="dashboard.php"
                class="flex items-center gap-2 px-3 py-3 rounded-xl text-[10px] font-black uppercase tracking-wide text-gray-400 hover:bg-gray-50 transition-all">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Visão Geral
            </a>
            <a href="#"
                class="flex items-center gap-2 px-3 py-3 rounded-xl text-[10px] font-black uppercase tracking-wide bg-[#00a650] text-white shadow-lg shadow-green-500/30">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Gerenciar Rifa
            </a>
        </nav>

        <div class="px-4 py-6 mt-auto">
            <a href="logout.php"
                class="text-[9px] font-black text-red-400 uppercase tracking-widest hover:text-red-500 transition-colors">Sair
                do SaaS</a>
        </div>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="flex-grow flex flex-col">

        <!-- TOP BAR: ícones à esquerda, link à direita (exato como no print) -->
        <div class="bg-white border-b border-gray-100 px-8 py-5 flex justify-between items-center sticky top-0 z-40">
            <div class="flex items-center gap-2">
                <!-- Ícone olho -->
                <button
                    class="w-9 h-9 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-300 hover:text-[#00a650] transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
                <!-- Ícone compartilhar -->
                <button
                    class="w-9 h-9 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-300 hover:text-[#00a650] transition-colors shadow-sm"
                    onclick="copiarLink()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </button>
                <!-- Ícone editar -->
                <a href="editar-rifa.php?id=<?php echo $r_id; ?>"
                    class="w-9 h-9 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-300 hover:text-[#00a650] transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </a>
            </div>
            <a href="dashboard.php" class="text-[9px] font-black text-gray-700 uppercase tracking-widest hover:text-gray-900 transition-colors">Voltar para Dashboard</a>
        </div>

        <!-- CARD PRINCIPAL -->
        <div class="px-8 pt-10 pb-10 flex-grow">
            <div class="main-card overflow-hidden max-w-5xl mx-auto">

                <!-- SEÇÃO SUPERIOR: imagem + info -->
                <div class="p-10 flex flex-col lg:flex-row gap-10 items-start">

                    <!-- Imagem da Rifa -->
                    <div class="w-full lg:w-5/12 flex-shrink-0">
                        <img src="<?php echo htmlspecialchars($rifa['imagem_url'] ?? ''); ?>"
                            onerror="this.src='https://placehold.co/500x300/e8f5e9/00a650?text=Rifa'"
                            class="w-full h-56 object-cover rounded-2xl shadow-lg">
                    </div>

                    <!-- Dados da Rifa -->
                    <div class="flex-grow space-y-5">
                        <!-- Título + badge status -->
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1
                                    class="text-4xl font-black italic text-gray-800 tracking-tighter uppercase leading-tight">
                                    <?php echo htmlspecialchars($rifa['titulo']); ?></h1>
                                <p class="text-xs font-semibold text-gray-400 mt-1 uppercase tracking-widest">
                                    <?php echo htmlspecialchars($rifa['subtitulo']); ?></p>
                            </div>
                            <span class="flex-shrink-0 text-[9px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest"
                                style="<?php echo $status_info['style']; ?>">
                                <?php echo $status_info['label']; ?>
                            </span>
                        </div>

                        <!-- Progresso -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span
                                    class="text-[10px] font-black text-gray-500 uppercase tracking-widest"><?php echo number_format($porcentagem, 2, ',', '.'); ?>%
                                    Vendido</span>
                                <span
                                    class="text-[10px] font-black text-gray-300 uppercase tracking-widest"><?php echo $pagos; ?>
                                    de <?php echo $rifa['total_numeros']; ?></span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" style="width: <?php echo min($porcentagem, 100); ?>%"></div>
                            </div>
                        </div>

                        <!-- Box Valor Arrecadado -->
                        <div class="value-box p-5">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Valor
                                Arrecadado</p>
                            <div class="flex items-center gap-3">
                                <span class="text-2xl font-black text-gray-800 italic" id="saldo_el">R$ ****</span>
                                <button onclick="toggleSaldo()"
                                    class="text-gray-300 hover:text-[#00a650] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO INFERIOR: 5 botões de ação -->
                <div class="border-t border-gray-100 bg-gray-50/60 px-8 py-8 grid grid-cols-5 gap-4">
                    <!-- Minhas Vendas -->
                    <button class="action-card p-5 flex flex-col items-center gap-3">
                        <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#00a650]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-[9px] font-black text-gray-500 uppercase tracking-wider text-center">Minhas
                            Vendas</span>
                    </button>

                    <!-- Título Premiado -->
                    <button class="action-card p-5 flex flex-col items-center gap-3"
                        onclick="openModal('titulosModal')">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <span class="text-[9px] font-black text-gray-500 uppercase tracking-wider text-center">Título
                            Premiado</span>
                    </button>

                    <!-- Ranking -->
                    <button class="action-card p-5 flex flex-col items-center gap-3"
                        onclick="openModal('rankingModal')">
                        <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <span
                            class="text-[9px] font-black text-gray-500 uppercase tracking-wider text-center">Ranking</span>
                    </button>

                    <!-- Maior e Menor -->
                    <button class="action-card p-5 flex flex-col items-center gap-3"
                        onclick="openModal('maiorMenorModal')">
                        <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </div>
                        <span class="text-[9px] font-black text-gray-500 uppercase tracking-wider text-center">Maior e
                            Menor</span>
                    </button>

                    <!-- Caixa Premiada -->
                    <button class="action-card p-5 flex flex-col items-center gap-3" onclick="openModal('caixaModal')">
                        <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <span class="text-[9px] font-black text-gray-500 uppercase tracking-wider text-center">Caixa
                            Premiada</span>
                    </button>
                </div>

                <!-- RODAPÉ -->
                <div
                    class="border-t border-gray-100 bg-white px-10 py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-gray-400">
                        <svg class="w-4 h-4 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-[10px] italic">Publique essa ação em até 72h ou ela vai expirar</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="window.open('<?php echo 'rifa.php?id=' . $r_id; ?>', '_blank')"
                            class="flex items-center gap-2 px-6 py-3 border border-gray-200 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-500 hover:bg-gray-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Visualizar Demonstração
                        </button>
                        <a href="ativar-rifa.php?id=<?php echo $r_id; ?>"
                            class="flex-grow sm:flex-none flex items-center gap-2 px-8 py-3 bg-[#00a650] text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-green-500/20 hover:bg-[#009647] transition-all transform hover:-translate-y-0.5"
                            style="background-color:#00a650;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            Publicar Campanha
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- MODAIS -->

    <!-- Modal Ranking -->
    <div id="rankingModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="closeModal('rankingModal')"></div>
        <div class="bg-white w-full max-w-md rounded-[2rem] shadow-2xl relative z-10 p-8 transform transition-all scale-95"
            id="rankingInner">
            <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h3 class="text-lg font-black uppercase italic text-center text-gray-800 mb-5">Ranking de Compradores</h3>
            <?php
            try {
                $stmtRanking = $pdo->prepare("SELECT r.nome, r.whatsapp, COUNT(n.id) as total_cotas FROM numeros n JOIN reservas r ON n.reserva_id = r.id WHERE n.rifa_id = ? AND n.status = 'pago' GROUP BY r.whatsapp ORDER BY total_cotas DESC LIMIT 5");
                $stmtRanking->execute([$r_id]);
                $ranking = $stmtRanking->fetchAll();
            } catch (Exception $e) {
                $ranking = [];
            }
            ?>
            <div class="space-y-2 mb-6">
                <?php if (empty($ranking)): ?>
                    <p class="text-center text-gray-400 text-[10px] font-black uppercase py-6">Nenhuma venda paga ainda</p>
                <?php else:
                    foreach ($ranking as $i => $row): ?>
                        <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <span class="text-lg font-black italic text-gray-300 w-6">#<?php echo $i + 1; ?></span>
                            <div class="flex-grow">
                                <p class="text-[11px] font-black text-gray-700"><?php echo htmlspecialchars($row['nome']); ?>
                                </p>
                            </div>
                            <span class="text-[10px] font-black text-purple-600"><?php echo $row['total_cotas']; ?> cotas</span>
                        </div>
                    <?php endforeach; endif; ?>
            </div>
            <button onclick="closeModal('rankingModal')"
                class="w-full bg-gray-900 text-white font-black py-3 rounded-xl uppercase text-[10px]">Fechar</button>
        </div>
    </div>

    <!-- Modal Título Premiado -->
    <div id="titulosModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="closeModal('titulosModal')"></div>
        <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl relative z-10 p-8">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-black uppercase italic text-center text-gray-800 mb-5">Título Premiado</h3>
            <form id="formTitulo" class="space-y-3 mb-5">
                <input type="hidden" name="rifa_id" value="<?php echo $r_id; ?>">
                <div class="grid grid-cols-3 gap-2">
                    <input type="number" name="numero" placeholder="Nº" required
                        class="bg-gray-50 border border-gray-100 rounded-xl px-3 py-2.5 text-xs font-black text-center">
                    <input type="text" name="descricao" placeholder="Descrição do Prêmio" required
                        class="col-span-2 bg-gray-50 border border-gray-100 rounded-xl px-3 py-2.5 text-xs font-black">
                </div>
                <button type="submit"
                    class="w-full bg-blue-500 text-white font-black py-2.5 rounded-xl uppercase text-[9px] hover:bg-blue-600">Adicionar
                    Título</button>
            </form>
            <?php
            try {
                $stmtTitulos = $pdo->prepare("SELECT * FROM titulos_premiados WHERE rifa_id = ? ORDER BY numero ASC");
                $stmtTitulos->execute([$r_id]);
                $titulos = $stmtTitulos->fetchAll();
            } catch (Exception $e) {
                $titulos = [];
            }
            ?>
            <div class="space-y-2 max-h-40 overflow-y-auto mb-5">
                <?php foreach ($titulos as $t): ?>
                    <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                        <span
                            class="text-[10px] font-black bg-white px-2 py-0.5 rounded-lg">#<?php echo $t['numero']; ?></span>
                        <span
                            class="text-[10px] font-black text-gray-600 flex-grow mx-2 truncate"><?php echo htmlspecialchars($t['descricao']); ?></span>
                        <span
                            class="text-[8px] font-bold text-gray-400"><?php echo $t['status'] === 'disponivel' ? 'LIVRE' : 'GANHOU'; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <button onclick="closeModal('titulosModal')"
                class="w-full bg-gray-900 text-white font-black py-3 rounded-xl uppercase text-[10px]">Fechar</button>
        </div>
    </div>

    <!-- Modal Maior e Menor -->
    <div id="maiorMenorModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="closeModal('maiorMenorModal')"></div>
        <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl relative z-10 p-8">
            <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            </div>
            <h3 class="text-lg font-black uppercase italic text-center text-gray-800 mb-5">Maior e Menor</h3>
            <?php
            try {
                $stmtMaior = $pdo->prepare("SELECT r.nome, COUNT(n.id) as total FROM numeros n JOIN reservas r ON n.reserva_id = r.id WHERE n.rifa_id = ? AND n.status='pago' GROUP BY r.whatsapp ORDER BY total DESC LIMIT 1");
                $stmtMaior->execute([$r_id]);
                $maior = $stmtMaior->fetch();
                $stmtPrimeiro = $pdo->prepare("SELECT r.nome FROM numeros n JOIN reservas r ON n.reserva_id = r.id WHERE n.rifa_id = ? AND n.status='pago' ORDER BY n.id ASC LIMIT 1");
                $stmtPrimeiro->execute([$r_id]);
                $primeiro = $stmtPrimeiro->fetch();
                $stmtUltimo = $pdo->prepare("SELECT r.nome FROM numeros n JOIN reservas r ON n.reserva_id = r.id WHERE n.rifa_id = ? AND n.status='pago' ORDER BY n.id DESC LIMIT 1");
                $stmtUltimo->execute([$r_id]);
                $ultimo = $stmtUltimo->fetch();
            } catch (Exception $e) {
                $maior = $primeiro = $ultimo = null;
            }
            ?>
            <div class="space-y-3 mb-6">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex justify-between items-center">
                    <div>
                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">🦈 Maior Comprador</p>
                        <p class="text-sm font-black text-gray-800 mt-0.5">
                            <?php echo $maior ? htmlspecialchars($maior['nome']) . ' (' . $maior['total'] . ' cotas)' : '—'; ?>
                        </p>
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex justify-between items-center">
                    <div>
                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">🥇 Pioneiro</p>
                        <p class="text-sm font-black text-gray-800 mt-0.5">
                            <?php echo $primeiro ? htmlspecialchars($primeiro['nome']) : '—'; ?></p>
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex justify-between items-center">
                    <div>
                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">🕐 Mais Recente</p>
                        <p class="text-sm font-black text-gray-800 mt-0.5">
                            <?php echo $ultimo ? htmlspecialchars($ultimo['nome']) : '—'; ?></p>
                    </div>
                </div>
            </div>
            <button onclick="closeModal('maiorMenorModal')"
                class="w-full bg-gray-900 text-white font-black py-3 rounded-xl uppercase text-[10px]">Fechar</button>
        </div>
    </div>

    <!-- Modal Caixa Premiada -->
    <div id="caixaModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="closeModal('caixaModal')"></div>
        <div class="bg-white w-full max-w-sm rounded-[2rem] shadow-2xl relative z-10 p-8">
            <div class="w-12 h-12 bg-yellow-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <h3 class="text-lg font-black uppercase italic text-center text-gray-800 mb-5">Caixa Premiada</h3>
            <form id="formCaixa" class="space-y-3 mb-5">
                <input type="hidden" name="rifa_id" value="<?php echo $r_id; ?>">
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" name="qtd_minima" placeholder="Mín. Cotas" required
                        class="bg-gray-50 border border-gray-100 rounded-xl px-3 py-2.5 text-xs font-black">
                    <input type="text" name="premio_descricao" placeholder="Prêmio" required
                        class="bg-gray-50 border border-gray-100 rounded-xl px-3 py-2.5 text-xs font-black">
                </div>
                <button type="submit"
                    class="w-full bg-yellow-500 text-white font-black py-2.5 rounded-xl uppercase text-[9px] hover:bg-yellow-600">Fixar
                    Meta</button>
            </form>
            <?php
            try {
                $stmtCaixas = $pdo->prepare("SELECT * FROM caixas_premiadas WHERE rifa_id = ? ORDER BY qtd_minima ASC");
                $stmtCaixas->execute([$r_id]);
                $caixas = $stmtCaixas->fetchAll();
            } catch (Exception $e) {
                $caixas = [];
            }
            ?>
            <div class="space-y-2 max-h-40 overflow-y-auto mb-5">
                <?php foreach ($caixas as $c): ?>
                    <div class="flex items-center justify-between bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                        <span class="text-[10px] font-black text-yellow-600"><?php echo $c['qtd_minima']; ?>+ cotas</span>
                        <span
                            class="text-[10px] font-black text-gray-600"><?php echo htmlspecialchars($c['premio_descricao']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <button onclick="closeModal('caixaModal')"
                class="w-full bg-gray-900 text-white font-black py-3 rounded-xl uppercase text-[10px]">Fechar</button>
        </div>
    </div>

    <script>
        // Saldo toggle
        let saldoVisivel = false;
        const saldoReal = "R$ <?php echo number_format($total_arrecadado, 2, ',', '.'); ?>";
        function toggleSaldo() {
            const el = document.getElementById('saldo_el');
            saldoVisivel = !saldoVisivel;
            el.textContent = saldoVisivel ? saldoReal : 'R$ ****';
        }

        // Modal system
        function openModal(id) {
            const m = document.getElementById(id);
            m.classList.remove('hidden');
            m.classList.add('flex');
            // Animate inner
            const inner = m.querySelector('.bg-white');
            if (inner) { setTimeout(() => inner.classList.replace('scale-95', 'scale-100'), 10); }
        }
        function closeModal(id) {
            const m = document.getElementById(id);
            const inner = m.querySelector('.bg-white');
            if (inner) inner.classList.replace('scale-100', 'scale-95');
            setTimeout(() => { m.classList.add('hidden'); m.classList.remove('flex'); }, 180);
        }

        // Copiar link
        function copiarLink() {
            const link = '<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/vender/rifa.php?id=" . $r_id; ?>';
            if (navigator.clipboard) {
                navigator.clipboard.writeText(link).then(() => alert('Link copiado!')).catch(() => legacyCopy(link));
            } else { legacyCopy(link); }
        }
        function legacyCopy(t) {
            const el = document.createElement('input'); el.value = t; document.body.appendChild(el); el.select(); document.execCommand('copy'); document.body.removeChild(el); alert('Link copiado!');
        }

        // Forms
        document.getElementById('formTitulo')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const r = await fetch('backend/api/salvar_titulo.php', { method: 'POST', body: new FormData(e.target) });
            const d = await r.json();
            if (d.success) location.reload(); else alert('Erro: ' + (d.error || 'tente novamente'));
        });
        document.getElementById('formCaixa')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const r = await fetch('backend/api/salvar_caixa.php', { method: 'POST', body: new FormData(e.target) });
            const d = await r.json();
            if (d.success) location.reload(); else alert('Erro: ' + (d.error || 'tente novamente'));
        });
    </script>
</body>

</html>