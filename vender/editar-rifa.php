<?php
require_once 'backend/config.php';

// Proteção da Página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$u_id = $_SESSION['usuario_id'];
$r_id = $_GET['id'] ?? null;

if (!$r_id) {
    header("Location: dashboard.php");
    exit;
}

// 1. Fetch Raffle Data
$stmt = $pdo->prepare("SELECT * FROM rifas WHERE id = ?");
$stmt->execute([$r_id]);
$rifa = $stmt->fetch();

if (!$rifa) {
    header("Location: dashboard.php");
    exit;
}

// Check ownership
if ($rifa['usuario_id'] != $u_id && $_SESSION['usuario_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Rifa #<?php echo $r_id; ?> - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .step-active { color: #00a650; }
        .step-inactive { color: #cbd5e1; }
        .progress-bar { transition: width 0.4s ease-in-out; }
    </style>
</head>
<body class="bg-[#f8fafc] flex min-h-screen antialiased text-gray-800">

    <!-- Sidebar (Simplified Link) -->
    <aside class="w-72 bg-white border-r border-gray-100 flex flex-col hidden lg:flex">
        <div class="p-8 text-center lg:text-left">
            <a href="dashboard.php" class="text-2xl font-black italic tracking-tighter text-[#00a650]">
                $UPER<span style="color: #2c3e50;">$ORTE</span>
            </a>
            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest mt-1">SaaS Platform</p>
        </div>
        <nav class="flex-grow px-6 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a11 11 0 001 1h3m10-11l2 2m-2-2v10a11 11 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Visão Geral
            </a>
            <a href="gerenciar-rifa.php?id=<?php echo $r_id; ?>" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest bg-[#00a650] text-white shadow-xl">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                Voltar Gerenciar
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
            <h2 class="text-sm font-black text-gray-400 uppercase tracking-widest">Painel SaaS / Editar Campanha</h2>
            <a href="gerenciar-rifa.php?id=<?php echo $r_id; ?>" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-800 transition-colors">Cancelar Edição</a>
        </header>

        <div class="p-8 max-w-4xl mx-auto w-full">
            
            <!-- Steps Progress Dashboard -->
            <div class="mb-12">
                <div class="flex justify-between items-center mb-4">
                    <div id="step-label-1" class="text-[10px] font-black uppercase tracking-widest step-active">1. Identidade</div>
                    <div id="step-label-2" class="text-[10px] font-black uppercase tracking-widest step-inactive">2. Regras</div>
                    <div id="step-label-3" class="text-[10px] font-black uppercase tracking-widest step-inactive">3. Finalização</div>
                </div>
                <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div id="progress-bar" class="w-1/3 h-full bg-[#00a650] progress-bar"></div>
                </div>
            </div>

            <form id="editForm" class="bg-white rounded-[3.5rem] shadow-2xl border border-gray-100 overflow-hidden">
                <input type="hidden" name="id" value="<?php echo $r_id; ?>">
                <!-- Campos Blindados (Hidden para manter integridade da API) -->
                <input type="hidden" name="valor_numero" value="<?php echo $rifa['valor_numero']; ?>">
                <input type="hidden" name="total_numeros" value="<?php echo $rifa['total_numeros']; ?>">

                <div class="p-8 lg:p-12">
                    
                    <!-- STEP 1: INFORMAÇÕES -->
                    <div id="step-1" class="space-y-8 animate-in fade-in duration-500">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Nome da Campanha</label>
                                <input type="text" name="titulo" required value="<?php echo htmlspecialchars($rifa['titulo']); ?>"
                                       class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Por onde será extraído o resultado?</label>
                                <select name="extracao_tipo" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                                    <option value="Loteria Federal" <?php if(($rifa['extracao_tipo'] ?? '') == 'Loteria Federal') echo 'selected'; ?>>Loteria Federal</option>
                                    <option value="Sorteador.com.br" <?php if(($rifa['extracao_tipo'] ?? '') == 'Sorteador.com.br') echo 'selected'; ?>>Sorteador.com.br</option>
                                    <option value="Live no Instagram" <?php if(($rifa['extracao_tipo'] ?? '') == 'Live no Instagram') echo 'selected'; ?>>Live no Instagram</option>
                                    <option value="Live no Youtube" <?php if(($rifa['extracao_tipo'] ?? '') == 'Live no Youtube') echo 'selected'; ?>>Live no Youtube</option>
                                    <option value="Live no TikTok" <?php if(($rifa['extracao_tipo'] ?? '') == 'Live no TikTok') echo 'selected'; ?>>Live no TikTok</option>
                                    <option value="Outros" <?php if(($rifa['extracao_tipo'] ?? '') == 'Outros') echo 'selected'; ?>>Outros</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fixed Info Banner -->
                        <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100 flex items-center justify-between gap-4 grayscale opacity-60">
                            <div>
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Quantidade Fixa</p>
                                <p class="text-sm font-black text-gray-600"><?php echo number_format($rifa['total_numeros'], 0, ',', '.'); ?> Títulos</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest">Valor Fixo</p>
                                <p class="text-sm font-black text-gray-600">R$ <?php echo number_format($rifa['valor_numero'], 2, ',', '.'); ?></p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" onclick="nextStep(2)" 
                                    class="bg-green-500 text-white font-black px-12 py-5 rounded-2xl shadow-xl hover:bg-green-600 transition-all transform hover:scale-105 uppercase tracking-widest text-[10px] flex items-center gap-3">
                                Próximo Passo <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: REGULAMENTO -->
                    <div id="step-2" class="hidden space-y-8 animate-in slide-in-from-right duration-500">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Atualizar Imagem Principal</label>
                            <div class="relative group">
                                <input type="file" name="imagem" accept="image/*" id="imgInput"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                                <div id="dropZone" class="w-full h-32 bg-gray-50 border-2 border-dashed border-gray-100 rounded-[2.5rem] flex flex-col items-center justify-center gap-2 transition-all group-hover:bg-white group-hover:border-green-200">
                                    <div class="w-8 h-8 bg-white shadow-sm rounded-xl flex items-center justify-center text-green-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                    </div>
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest" id="imgName">Substituir Capa Atual</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Tempo de Reserva</label>
                                <select name="tempo_reserva" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                                    <option value="5" <?php if(($rifa['tempo_reserva'] ?? '') == '5') echo 'selected'; ?>>5 minutos</option>
                                    <option value="15" <?php if(($rifa['tempo_reserva'] ?? '') == '15') echo 'selected'; ?>>15 minutos</option>
                                    <option value="60" <?php if(($rifa['tempo_reserva'] ?? '') == '60') echo 'selected'; ?>>1 hora</option>
                                    <option value="1440" <?php if(($rifa['tempo_reserva'] ?? '') == '1440') echo 'selected'; ?>>24 horas</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Mínima</label>
                                    <input type="number" name="min_reserva" value="<?php echo $rifa['min_reserva'] ?? 1; ?>" 
                                           class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium text-center">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Máxima</label>
                                    <input type="number" name="max_reserva" value="<?php echo $rifa['max_reserva'] ?? 10; ?>" 
                                           class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium text-center">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Regulamento</label>
                            <textarea name="subtitulo" rows="4" 
                                      class="w-full bg-gray-50 border border-gray-100 rounded-[2rem] px-6 py-6 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"><?php echo htmlspecialchars($rifa['subtitulo']); ?></textarea>
                        </div>

                        <div class="flex justify-between items-center">
                            <button type="button" onclick="nextStep(1)" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-800 transition-colors">Voltar</button>
                            <button type="button" onclick="nextStep(3)" 
                                    class="bg-green-500 text-white font-black px-12 py-5 rounded-2xl shadow-xl hover:bg-green-600 transition-all transform hover:scale-105 uppercase tracking-widest text-[10px] flex items-center gap-3">
                                Próximo Passo <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: MODO/FINALIZAÇÃO -->
                    <div id="step-3" class="hidden space-y-8 animate-in slide-in-from-right duration-500">
                        <div class="bg-gray-900 p-10 rounded-[3rem] shadow-2xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-40 h-40 bg-green-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
                            
                            <h4 class="text-[10px] font-black text-green-500 uppercase tracking-[0.2em] mb-8">Revisão de Campanha</h4>
                            
                            <div class="space-y-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center text-white/40"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                                    <div>
                                        <p class="text-[8px] font-black text-gray-500 uppercase tracking-widest">Status Atual</p>
                                        <p class="text-xs font-black text-white uppercase italic"><?php echo strtoupper($rifa['status']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center text-white/40"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                                    <div>
                                        <p class="text-[8px] font-black text-gray-500 uppercase tracking-widest">Arrecadação Prevista</p>
                                        <p class="text-xs font-black text-green-400">R$ <?php echo number_format($rifa['total_numeros'] * $rifa['valor_numero'], 2, ',', '.'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <p class="text-[9px] text-gray-500 font-medium leading-relaxed mt-10 italic">Ao salvar as alterações, as informações públicas da sua rifa serão atualizadas instantaneamente para todos os seus clientes.</p>
                        </div>

                        <div class="flex justify-between items-center pt-6">
                            <button type="button" onclick="nextStep(2)" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-800 transition-colors">Voltar</button>
                            <button type="submit" 
                                    class="bg-gray-900 text-white font-black px-12 py-5 rounded-2xl shadow-xl hover:bg-black transition-all transform hover:scale-105 uppercase tracking-widest text-[10px] flex items-center gap-3">
                                Salvar Tudo <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div id="responseMsg" class="mt-8 text-center text-[10px] font-black tracking-widest uppercase transition-all h-2"></div>

                </div>
            </form>

        </div>
    </main>

    <script>
        function nextStep(step) {
            document.getElementById('step-1').classList.add('hidden');
            document.getElementById('step-2').classList.add('hidden');
            document.getElementById('step-3').classList.add('hidden');
            document.getElementById('step-' + step).classList.remove('hidden');
            const progress = document.getElementById('progress-bar');
            const l1 = document.getElementById('step-label-1');
            const l2 = document.getElementById('step-label-2');
            const l3 = document.getElementById('step-label-3');
            if(step === 1) { 
                progress.style.width = '33.33%'; 
                l1.classList.replace('step-inactive', 'step-active'); l2.classList.replace('step-active', 'step-inactive'); l3.classList.replace('step-active', 'step-inactive');
            } else if(step === 2) { 
                progress.style.width = '66.66%'; 
                l1.classList.replace('step-inactive', 'step-active'); l2.classList.replace('step-inactive', 'step-active'); l3.classList.replace('step-active', 'step-inactive');
            } else { 
                progress.style.width = '100%'; 
                l1.classList.replace('step-inactive', 'step-active'); l2.classList.replace('step-inactive', 'step-active'); l3.classList.replace('step-inactive', 'step-active');
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        const imgInput = document.getElementById('imgInput');
        const imgName = document.getElementById('imgName');
        const dropZone = document.getElementById('dropZone');
        imgInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                imgName.innerText = e.target.files[0].name;
                dropZone.classList.add('bg-green-50', 'border-green-100');
            }
        });

        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const msg = document.getElementById('responseMsg');
            const formData = new FormData(e.target);
            btn.disabled = true;
            btn.innerHTML = 'Processando...';
            try {
                const response = await fetch('backend/api/atualizar_rifa.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    msg.className = 'mt-8 text-center text-[10px] font-black tracking-widest uppercase text-green-500';
                    msg.innerText = 'RIFA ATUALIZADA COM SUCESSO! REDIRECIONANDO...';
                    setTimeout(() => window.location.href = 'gerenciar-rifa.php?id=' + data.id, 1500);
                } else {
                    msg.className = 'mt-8 text-center text-[10px] font-black tracking-widest uppercase text-red-500';
                    msg.innerText = data.error || 'Erro ao atualizar.';
                    btn.disabled = false;
                    btn.innerHTML = 'Salvar Tudo';
                }
            } catch (error) {
                msg.className = 'mt-8 text-center text-[10px] font-black tracking-widest uppercase text-red-500';
                msg.innerText = 'ERRO DE CONEXÃO.';
                btn.disabled = false;
                btn.innerHTML = 'Salvar Tudo';
            }
        });
    </script>
</body>
</html>
