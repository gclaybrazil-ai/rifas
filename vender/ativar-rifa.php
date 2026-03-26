<?php
require_once 'backend/config.php';

// Proteção da Página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$u_id = $_SESSION['usuario_id'];
$r_id = $_GET['id'] ?? 0;

// Buscar detalhes da rifa no banco SaaS
$stmt = $pdo->prepare("SELECT * FROM rifas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$r_id, $u_id]);
$rifa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rifa) {
    die("Rifa não encontrada ou você não tem permissão para acessá-la.");
}

// Lógica de Estimativa de Taxa (Tabela Atualizada de 16 Tiers)
$total_colecao = $rifa['total_numeros'] * $rifa['valor_numero'];
$taxa = 7.00;

if ($total_colecao > 150000) $taxa = 3967.00;
else if ($total_colecao > 100000) $taxa = 2967.00;
else if ($total_colecao > 70000) $taxa = 1967.00;
else if ($total_colecao > 50000) $taxa = 1467.00;
else if ($total_colecao > 30000) $taxa = 967.00;
else if ($total_colecao > 20000) $taxa = 467.00;
else if ($total_colecao > 10000) $taxa = 217.00;
else if ($total_colecao > 7100) $taxa = 197.00;
else if ($total_colecao > 4000) $taxa = 127.00;
else if ($total_colecao > 2000) $taxa = 77.00;
else if ($total_colecao > 1000) $taxa = 67.00;
else if ($total_colecao > 701) $taxa = 47.00;
else if ($total_colecao > 400) $taxa = 37.00;
else if ($total_colecao > 200) $taxa = 27.00;
else if ($total_colecao > 100) $taxa = 17.00;
else $taxa = 7.00;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ativar Campanha - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-dark { background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-gray-900 min-h-screen flex flex-col antialiased text-white">

    <header class="p-8 flex justify-between items-center border-b border-white/5">
        <a href="dashboard.php" class="text-2xl font-black italic tracking-tighter text-[#00a650]">
            $UPER<span class="text-white">$ORTE</span>
        </a>
        <a href="dashboard.php" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-white transition-colors">Voltar para Dashboard</a>
    </header>

    <main class="flex-grow flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-4xl grid md:grid-cols-2 gap-12 items-center">
            
            <!-- Detalhes da Rifa (Review) -->
            <div class="space-y-8">
                <div>
                    <span class="text-[9px] font-black bg-yellow-500/20 text-yellow-500 px-4 py-1.5 rounded-full uppercase tracking-widest mb-4 inline-block italic">Aguardando Ativação</span>
                    <h1 class="text-4xl font-black tracking-tighter italic uppercase leading-tight">
                        Quase lá! <br> Ative sua <span class="text-green-500 italic">Campanha.</span>
                    </h1>
                    <p class="text-gray-400 font-medium text-sm mt-4">Sua rifa está salva e pronta para ser publicada. <br> Para começar a vender, realize a ativação abaixo.</p>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-[2.5rem] p-8 space-y-6">
                    <div class="flex justify-between items-center border-b border-white/5 pb-4">
                        <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Rifa:</p>
                        <p class="text-sm font-black text-white italic"><?php echo htmlspecialchars($rifa['titulo']); ?></p>
                    </div>
                    <div class="flex justify-between items-center border-b border-white/5 pb-4">
                        <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Metas:</p>
                        <p class="text-sm font-black text-white italic"><?php echo $rifa['total_numeros']; ?> Números @ R$ <?php echo number_format($rifa['valor_numero'], 2, ',', '.'); ?></p>
                    </div>
                    <div class="flex justify-between items-center">
                        <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Potencial de Venda:</p>
                        <p class="text-lg font-black text-green-500 italic">R$ <?php echo number_format($total_colecao, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Preço de Ativação -->
            <div class="bg-white rounded-[3rem] p-12 shadow-2xl relative overflow-hidden text-gray-800">
                <div class="absolute top-0 right-0 w-32 h-32 bg-green-50 rounded-full blur-3xl -mr-16 -mt-16"></div>
                
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 relative z-10">Valor da Ativação Única</p>
                <h2 class="text-5xl font-black text-[#2c3e50] mb-8 relative z-10 italic">R$ <?php echo number_format($taxa, 2, ',', '.'); ?></h2>

                <div class="space-y-4 mb-10 relative z-10">
                    <div class="flex items-center gap-3 text-sm font-black text-gray-700 italic border-l-2 border-green-500 pl-4">
                        Ativação Imediata ⚡
                    </div>
                    <div class="flex items-center gap-3 text-sm font-black text-gray-700 italic border-l-2 border-green-500 pl-4">
                        Receba 100% das suas vendas 🎯
                    </div>
                    <div class="flex items-center gap-3 text-sm font-black text-gray-700 italic border-l-2 border-green-500 pl-4">
                        Zero Mensalidades 🚀
                    </div>
                </div>

                <?php 
                    try {
                        $global = $pdo->query("SELECT whatsapp_suporte FROM global_config ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
                        $zap_master = $global['whatsapp_suporte'] ?? '5511999999999';
                    } catch (Exception $e) {
                        $zap_master = '5511999999999';
                    }
                ?>

                <div id="pix_area" class="hidden animate-fade-in relative z-10">
                     <div class="w-40 h-40 bg-gray-50 p-3 rounded-3xl mx-auto mb-6 border border-gray-100 shadow-inner">
                        <img id="qr_img" src="" class="w-full h-full mix-blend-multiply">
                    </div>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3 italic">PIX Copia e Cola / Chave:</p>
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 mb-8 flex items-center justify-between gap-4">
                        <p id="pix_key" class="text-gray-800 font-bold text-[10px] truncate">Gerando...</p>
                        <button onclick="copyPix()" class="bg-[#00a650] text-white px-4 py-2 rounded-xl text-[8px] font-black uppercase transition-all active:scale-95">Copiar</button>
                    </div>
                    <a href="https://wa.me/<?php echo $zap_master; ?>?text=Envio+comprovante+rifa+ID+<?php echo $r_id; ?>+Valor+R%24+<?php echo number_format($taxa, 2, ',', '.'); ?>" class="block w-full text-center bg-[#00a650] text-white font-black py-5 rounded-2xl shadow-xl hover:bg-[#009647] transition-all transform hover:-translate-y-1 uppercase tracking-widest text-[10px]">
                        Enviar Comprovante
                    </a>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    let statusInterval = null;

                    async function gerarPix() {
                        const btn = document.getElementById('btn_gerar');
                        btn.disabled = true;
                        btn.innerText = 'GERANDO PIX...';
                        try {
                            const response = await fetch('backend/api/gerar_pix_ativacao.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ rifa_id: <?php echo $r_id; ?> })
                            });
                            const data = await response.json();
                            if (data.success) {
                                document.getElementById('pix_area').classList.remove('hidden');
                                document.getElementById('pix_key').innerText = data.copy_paste;
                                document.getElementById('qr_img').src = data.qr_code.startsWith('http') ? data.qr_code : 'data:image/png;base64,' + data.qr_code;
                                btn.remove();
                                
                                // Iniciar monitoramento automático (Live Update)
                                startMonitoring();
                            } else {
                                alert(data.error);
                                btn.disabled = false;
                                btn.innerText = 'GERAR PIX DE ATIVAÇÃO';
                            }
                        } catch (e) {
                            alert('Erro ao conectar ao servidor.');
                            btn.disabled = false;
                            btn.innerText = 'GERAR PIX DE ATIVAÇÃO';
                        }
                    }

                    function startMonitoring() {
                        if (statusInterval) clearInterval(statusInterval);
                        statusInterval = setInterval(async () => {
                            const res = await fetch('backend/api/get_rifa_status.php?id=<?php echo $r_id; ?>');
                            const data = await res.json();
                            if (data.status === 'ativa') {
                                clearInterval(statusInterval);
                                Swal.fire({
                                    icon: 'success', 
                                    title: 'Pagamento Aprovado!', 
                                    text: 'Sua rifa agora está ATIVA e pronta para vender!',
                                    background: '#1a1a1a', 
                                    color: '#fff',
                                    confirmButtonColor: '#00a650'
                                }).then(() => { window.location.href = 'dashboard.php'; });
                            }
                        }, 3000);
                    }

                    function copyPix() {
                        const text = document.getElementById('pix_key').innerText;
                        navigator.clipboard.writeText(text).then(() => {
                            const btn = event.target;
                            const original = btn.innerText;
                            btn.innerText = 'COPIADO!';
                            setTimeout(() => btn.innerText = original, 2000);
                        });
                    }
                </script>

                <button id="btn_gerar" onclick="gerarPix()" 
                        class="relative z-10 block w-full text-center bg-[#00a650] text-white font-black py-5 rounded-2xl shadow-xl hover:bg-[#009647] transition-all transform hover:-translate-y-1 uppercase tracking-widest text-[11px]">
                    Gerar PIX de Ativação
                </button>
                
                <p class="text-center text-[9px] font-black text-gray-400 uppercase tracking-widest mt-6">Atendimento 24/7 para Ativações</p>
            </div>

        </div>
    </main>

    <footer class="p-8 text-center bg-black/20">
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.3em]">© 2026 $UPER$ORTE - Tecnologia para Sorteios</p>
    </footer>

</body>
</html>
