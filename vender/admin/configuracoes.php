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
        <body class="bg-gray-50 text-gray-900 font-['Inter'] min-h-screen flex items-center justify-center p-8 text-center">
            <div class="max-w-md w-full">
                <h1 class="text-3xl font-black uppercase text-red-500 italic">Acesso Restrito</h1>
                <p class="text-gray-500 mt-4">Somente Administrador Master.</p>
                <a href="../dashboard.php" class="mt-8 inline-block bg-white border border-gray-200 text-gray-900 font-black px-8 py-4 rounded-2xl text-[10px] uppercase tracking-widest shadow-sm">Voltar</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// 1. Fetch Global Settings
$stmt = $pdo->prepare("SELECT * FROM global_config LIMIT 1");
$stmt->execute();
$config = $stmt->fetch();

// 2. Handle POST
$msg = "";
$status_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chave_pix = $_POST['chave_pix'];
    $mp_token = $_POST['mp_access_token'];
    $mp_public = $_POST['mp_public_key'] ?? '';
    $whatsapp = $_POST['whatsapp_suporte'];

    if ($config) {
        $stmt = $pdo->prepare("UPDATE global_config SET chave_pix=?, mp_access_token=?, mp_public_key=?, whatsapp_suporte=? WHERE id=?");
        $stmt->execute([$chave_pix, $mp_token, $mp_public, $whatsapp, $config['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO global_config (chave_pix, mp_access_token, mp_public_key, whatsapp_suporte) VALUES (?, ?, ?, ?)");
        $stmt->execute([$chave_pix, $mp_token, $mp_public, $whatsapp]);
    }
    $msg = "Parâmetros Globais atualizados com sucesso!";
    $status_msg = "success";
    // Refresh fetch
    $stmt = $pdo->prepare("SELECT * FROM global_config LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Admin Settings - $UPER$ORTE</title>
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
            <a href="index.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                Gerenciar Campanhas
            </a>
            <a href="configuracoes.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest sidebar-item-active">
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
                <p class="text-[9px] font-bold text-gray-400 uppercase italic">SaaS Parameters Control</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center text-[#00a650] font-black italic border border-green-100">
                    M
                </div>
            </div>
        </header>

        <section class="p-8">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h1 class="text-4xl font-black text-[#2c3e50] tracking-tighter uppercase italic leading-[0.9] mb-4">
                        Configuração <br> <span class="text-[#00a650]">Global Master.</span>
                    </h1>
                    <p class="text-gray-400 font-medium text-sm">Defina seus parâmetros de recebimento e suporte do SaaS.</p>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="bg-green-50 border border-green-100 text-green-700 p-6 rounded-3xl mb-12 flex items-center gap-4 animate-fade-in shadow-sm">
                    <div class="bg-green-100 w-10 h-10 rounded-full flex items-center justify-center border border-green-200">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <p class="text-sm font-black uppercase tracking-widest"><?php echo $msg; ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="max-w-4xl space-y-8">
                <div class="bg-white rounded-[2.5rem] border border-gray-100 p-10 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-green-50 rounded-full blur-3xl -mr-16 -mt-16 opacity-50"></div>
                    
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-10 italic border-l-4 border-[#00a650] pl-4">Recebimento (Taxas de Ativação)</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Chave PIX (Para Ativação Manual)</label>
                            <input type="text" name="chave_pix" value="<?php echo htmlspecialchars($config['chave_pix'] ?? ''); ?>" required
                                   class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all"
                                   placeholder="Sua Chave PIX Master">
                        </div>

                        <div class="col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Access Token Mercado Pago (API de Ativação)</label>
                            <input type="text" name="mp_access_token" value="<?php echo htmlspecialchars($config['mp_access_token'] ?? ''); ?>"
                                   class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all font-mono mb-4"
                                   placeholder="APP_USR-...">

                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">Public Key Mercado Pago (Página de Ativação)</label>
                            <input type="text" name="mp_public_key" value="<?php echo htmlspecialchars($config['mp_public_key'] ?? ''); ?>"
                                   class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all font-mono"
                                   placeholder="APP_USR-...">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2.5rem] border border-gray-100 p-10 shadow-sm">
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-8 italic border-l-4 border-gray-800 pl-4">Suporte & Contato</h2>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 block">WhatsApp de Suporte Master (Somente números)</label>
                        <input type="text" name="whatsapp_suporte" value="<?php echo htmlspecialchars($config['whatsapp_suporte'] ?? ''); ?>" required
                               class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-6 py-4 text-sm font-medium focus:outline-none focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all"
                               placeholder="5511999999999">
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" 
                            class="bg-gray-900 text-white font-black px-12 py-5 rounded-2xl text-[11px] uppercase tracking-widest hover:bg-black transition-all transform hover:-translate-y-1 shadow-2xl active:scale-95">
                        Salvar Parâmetros Globais
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
