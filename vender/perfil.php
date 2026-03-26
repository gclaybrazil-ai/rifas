<?php
require_once 'backend/config.php';

$username = $_GET['u'] ?? null;
if (!$username) {
    die("Página não encontrada.");
}

// 1. Fetch User Data
$stmt = $pdo->prepare("SELECT id, nome, username FROM usuarios WHERE username = ? AND status = 'ativo'");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    die("Criador não encontrado ou suspenso.");
}

// 2. Fetch Active Raffles
$stmt = $pdo->prepare("SELECT * FROM rifas WHERE usuario_id = ? AND status = 'ativa' ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$rifas = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user['nome']; ?> - Campanhas Ativas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-gray-800 antialiased">

    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-6 py-6 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-500 rounded-2xl shadow-lg shadow-green-500/20 flex items-center justify-center text-white font-black text-xl italic">
                    <?php echo substr($user['nome'], 0, 1); ?>
                </div>
                <div>
                    <h1 class="text-xl font-black text-[#2c3e50] tracking-tighter uppercase italic leading-none"><?php echo $user['nome']; ?></h1>
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-1">@<?php echo $user['username']; ?></p>
                </div>
            </div>
            <a href="index.php" class="text-[10px] font-black text-[#00a650] uppercase tracking-widest bg-green-50 px-4 py-2 rounded-full border border-green-100">Criar minha Rifa</a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-12">
        
        <div class="mb-10">
            <h2 class="text-2xl font-black text-[#2c3e50] tracking-tighter uppercase italic mb-2">Campanhas Ativas</h2>
            <div class="h-1.5 w-12 bg-[#00a650] rounded-full"></div>
        </div>

        <?php if (empty($rifas)): ?>
            <div class="bg-white rounded-[2.5rem] p-12 text-center border border-gray-100 shadow-xl">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-lg font-black text-gray-800 uppercase italic mb-2">Nenhuma campanha no momento</h3>
                <p class="text-sm text-gray-400 font-medium">Este criador ainda não possui campanhas ativas para exibição.</p>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 gap-8">
                <?php foreach ($rifas as $rifa): ?>
                    <a href="rifa.php?id=<?php echo $rifa['id']; ?>" class="group block bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden transform hover:-translate-y-2 transition-all duration-300">
                        <div class="relative h-48 overflow-hidden">
                            <img src="<?php echo $rifa['imagem_url'] ?: 'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'; ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="Capa">
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-md px-4 py-2 rounded-2xl shadow-lg border border-white/20">
                                <p class="text-[9px] font-black text-[#00a650] uppercase tracking-widest leading-none">Apenas</p>
                                <p class="text-base font-black text-gray-800 tracking-tighter">R$ <?php echo number_format($rifa['valor_numero'], 2, ',', '.'); ?></p>
                            </div>
                        </div>
                        <div class="p-8">
                            <h4 class="text-lg font-black text-gray-800 uppercase italic tracking-tighter leading-tight mb-2 group-hover:text-[#00a650] transition-colors"><?php echo htmlspecialchars($rifa['titulo']); ?></h4>
                            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Participar Agora
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <footer class="bg-white border-t border-gray-100 py-12">
        <div class="max-w-4xl mx-auto px-6 text-center">
             <a href="../index.php" class="text-xl font-black italic tracking-tighter text-[#00a650]">
                $UPER<span style="color: #2c3e50;">$ORTE</span>
            </a>
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-4">Plataforma de Rifas Online © 2026</p>
        </div>
    </footer>

</body>
</html>
