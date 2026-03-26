<?php
require_once 'backend/config.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Página não encontrada.");
}

// 1. Fetch Raffle Data
$stmt = $pdo->prepare("SELECT r.*, u.nome as criador_nome, u.whatsapp as criador_whatsapp 
                       FROM rifas r 
                       JOIN usuarios u ON r.usuario_id = u.id 
                       WHERE r.id = ?");
$stmt->execute([$id]);
$rifa = $stmt->fetch();

if (!$rifa) {
    die("Campanha não encontrada.");
}

// 2. Fetch occupied numbers
$stmt = $pdo->prepare("SELECT numero, status FROM numeros WHERE rifa_id = ?");
$stmt->execute([$id]);
$ocupados = [];
while($row = $stmt->fetch()){
    $ocupados[$row['numero']] = $row['status'];
}

$status_label = "Campanha Ativa";
$status_color = "bg-green-500";
if($rifa['status'] == 'pendente_ativacao'){
    $status_label = "Aguardando Ativação";
    $status_color = "bg-orange-500";
} else if($rifa['status'] == 'finalizado'){
    $status_label = "Finalizada";
    $status_color = "bg-gray-500";
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $rifa['titulo']; ?> - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
            gap: 8px;
        }
        .num-item {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 8px;
        }
        .num-available { background: #fff; border: 1px solid #e2e8f0; color: #64748b; }
        .num-available:hover { border-color: #00a650; transform: scale(1.1); }
        .num-selected { background: #00a650 !important; color: #fff !important; transform: scale(1.1); box-shadow: 0 4px 12px rgba(0,166,80,0.3); }
        .num-reserved { background: #fef3c7; color: #d97706; cursor: not-allowed; opacity: 0.6; }
        .num-paid { background: #f1f5f9; color: #cbd5e1; cursor: not-allowed; }
    </style>
</head>
<body class="bg-[#f8fafc] text-gray-800 antialiased">

    <!-- Header / Nav -->
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-xl font-black italic tracking-tighter text-[#00a650]">
                $UPER<span style="color: #2c3e50;">$ORTE</span>
            </a>
            <div class="flex items-center gap-4">
               <span class="text-[9px] font-black uppercase tracking-widest text-gray-400">Organizado por: <span class="text-gray-800 tracking-normal"><?php echo $rifa['criador_nome']; ?></span></span>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-8 pb-32">
        
        <!-- Main Info -->
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden mb-8">
            <div class="relative h-64 md:h-80 overflow-hidden group">
                <img src="<?php echo $rifa['imagem_url'] ?: 'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'; ?>" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" alt="Prêmio">
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-gray-900/80 to-transparent p-8">
                    <div class="flex items-center gap-2 mb-2">
                         <span class="<?php echo $status_color; ?> text-white text-[8px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                            <?php echo $status_label; ?>
                         </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black text-white italic tracking-tighter uppercase"><?php echo $rifa['titulo']; ?></h1>
                </div>
            </div>
            
            <div class="p-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Valor da Cota</p>
                        <p class="text-3xl font-black text-[#00a650]">R$ <?php echo number_format($rifa['valor_numero'], 2, ',', '.'); ?></p>
                    </div>
                    <div class="flex gap-4">
                         <div class="bg-gray-50 px-6 py-3 rounded-2xl border border-gray-100 text-center">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Participantes</p>
                            <p class="text-xs font-black text-gray-800"><?php echo count($ocupados); ?> / <?php echo $rifa['total_numeros']; ?></p>
                         </div>
                    </div>
                </div>
                
                <?php if($rifa['subtitulo']): ?>
                <div class="mt-8 pt-8 border-t border-gray-100">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Informações</h3>
                    <p class="text-sm text-gray-500 font-medium leading-relaxed italic"><?php echo nl2br($rifa['subtitulo']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Numbers Selection -->
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 p-8 lg:p-12 mb-8">
            <div class="flex justify-between items-center mb-10">
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Selecione seus Números</h3>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-white border border-gray-200"></div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Livre</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-orange-100"></div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Reservado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-gray-200"></div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Pago</span>
                    </div>
                </div>
            </div>

            <div class="grid-container" id="numbersGrid">
                <?php for($i=0; $i<$rifa['total_numeros']; $i++): 
                    $class = "num-available";
                    $onclick = "selectNumber($i)";
                    if(isset($ocupados[$i])){
                        if($ocupados[$i] == 'pago'){
                            $class = "num-paid";
                            $onclick = "";
                        } else {
                            $class = "num-reserved";
                            $onclick = "";
                        }
                    }
                    // Format number with leading zeros based on total
                    $label = str_pad($i, strlen($rifa['total_numeros']-1), "0", STR_PAD_LEFT);
                ?>
                    <div id="num-<?php echo $i; ?>" class="num-item <?php echo $class; ?>" onclick="<?php echo $onclick; ?>">
                        <?php echo $label; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

    </main>

    <!-- Bottom Checkout Bar (Floating) -->
    <div id="checkoutBar" class="fixed inset-x-0 bottom-0 bg-white/90 backdrop-blur-xl border-t border-gray-100 py-6 px-8 translate-y-full transition-transform duration-300 z-40">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <p id="selectedCountText" class="text-[10px] font-black text-[#00a650] uppercase tracking-widest">0 Cotas Selecionadas</p>
                <p id="totalPriceText" class="text-xl font-black text-[#2c3e50]">R$ 0,00</p>
            </div>
            <button onclick="openCheckoutModal()" class="w-full sm:w-auto bg-gray-900 text-white font-black px-12 py-5 rounded-2xl shadow-xl hover:bg-black transition-all transform hover:scale-105 uppercase tracking-widest text-[10px] flex items-center justify-center gap-3">
                Finalizar Compra <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>
    </div>

    <!-- Buyer Modal -->
    <div id="buyerModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeModal('buyerModal')"></div>
        <div class="relative min-h-screen flex items-center justify-center p-6 pointer-events-none">
            <div class="bg-white w-full max-w-md rounded-[3rem] shadow-2xl pointer-events-auto relative overflow-hidden">
                <div class="p-8 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-xl font-black text-[#2c3e50] uppercase tracking-tighter italic">Dados do Comprador</h3>
                    <button onclick="closeModal('buyerModal')" class="text-gray-400 hover:text-gray-800 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form id="purchaseForm" class="p-8 space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Nome Completo</label>
                        <input type="text" id="nome_comprador" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Seu WhatsApp</label>
                        <input type="tel" id="whatsapp_comprador" placeholder="(00) 00000-0000" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-[#00a650] text-white font-black py-5 rounded-2xl shadow-xl hover:bg-[#009647] transition-all transform hover:scale-105 uppercase tracking-widest text-[10px]">
                            Gerar PIX agora
                        </button>
                        <p class="text-[8px] text-gray-400 font-bold uppercase tracking-widest text-center mt-6">Seus dados estão seguros conosco.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let selectedNumbers = [];
        const valorCota = <?php echo (float)$rifa['valor_numero']; ?>;
        const rifaId = <?php echo (int)$rifa['id']; ?>;

        function selectNumber(num) {
            const index = selectedNumbers.indexOf(num);
            const el = document.getElementById('num-' + num);
            
            if (index > -1) {
                selectedNumbers.splice(index, 1);
                el.classList.remove('num-selected');
            } else {
                selectedNumbers.push(num);
                el.classList.add('num-selected');
            }
            
            updateCheckoutBar();
        }

        function updateCheckoutBar() {
            const bar = document.getElementById('checkoutBar');
            const countText = document.getElementById('selectedCountText');
            const priceText = document.getElementById('totalPriceText');
            
            if (selectedNumbers.length > 0) {
                bar.classList.remove('translate-y-full');
                countText.innerText = selectedNumbers.length + (selectedNumbers.length === 1 ? ' Cota Selecionada' : ' Cotas Selecionadas');
                const total = selectedNumbers.length * valorCota;
                priceText.innerText = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            } else {
                bar.classList.add('translate-y-full');
            }
        }

        function openCheckoutModal() {
            const modal = document.getElementById('buyerModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                const content = modal.querySelector('.relative');
                content.style.transition = 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
                content.style.opacity = '1';
                content.style.transform = 'scale(1)';
            }, 10);
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        document.getElementById('purchaseForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const nome = document.getElementById('nome_comprador').value;
            const whatsapp = document.getElementById('whatsapp_comprador').value;
            
            btn.disabled = true;
            btn.innerText = 'Reservando...';
            
            try {
                const response = await fetch('backend/api/reservar_cotas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        rifa_id: rifaId,
                        nome: nome,
                        whatsapp: whatsapp,
                        cotas: selectedNumbers
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'checkout.php?reserva_id=' + data.reserva_id;
                } else {
                    alert(data.error || 'Erro ao reservar cotas. Verifique se os números ainda estão disponíveis.');
                    btn.disabled = false;
                    btn.innerText = 'Gerar PIX agora';
                }
            } catch (error) {
                alert('Erro na conexão. Tente novamente.');
                btn.disabled = false;
                btn.innerText = 'Gerar PIX agora';
            }
        });
    </script>
</body>
</html>
