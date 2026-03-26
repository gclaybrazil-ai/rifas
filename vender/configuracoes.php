<?php
require_once 'backend/config.php';

// Proteção da Página
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] !== 'criador' && $_SESSION['usuario_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$u_id = $_SESSION['usuario_id'];

// 1. Fetch Current Settings
$stmt = $pdo->prepare("SELECT * FROM criador_config WHERE usuario_id = ?");
$stmt->execute([$u_id]);
$config = $stmt->fetch();

// 2. Handle POST
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gateway = $_POST['gateway'];
    $chave_pix = $_POST['chave_pix'];
    $mp_token = $_POST['mp_access_token'];
    $mp_public = $_POST['mp_public_key'];
    $efi_id = $_POST['efi_client_id'];
    $efi_secret = $_POST['efi_client_secret'];
    $cartao = isset($_POST['habilitar_cartao']) ? 1 : 0;
    $taxas = isset($_POST['repassar_taxas']) ? 1 : 0;

    if ($config) {
        $stmt = $pdo->prepare("UPDATE criador_config SET gateway=?, chave_pix=?, mp_access_token=?, mp_public_key=?, efi_client_id=?, efi_client_secret=?, habilitar_cartao=?, repassar_taxas=? WHERE usuario_id=?");
        $stmt->execute([$gateway, $chave_pix, $mp_token, $mp_public, $efi_id, $efi_secret, $cartao, $taxas, $u_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO criador_config (usuario_id, gateway, chave_pix, mp_access_token, mp_public_key, efi_client_id, efi_client_secret, habilitar_cartao, repassar_taxas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$u_id, $gateway, $chave_pix, $mp_token, $mp_public, $efi_id, $efi_secret, $cartao, $taxas]);
    }
    $msg = "Configurações salvas com sucesso!";
    // Terminar o fetch de volta para atualizar a tela
    $stmt = $pdo->prepare("SELECT * FROM criador_config WHERE usuario_id = ?");
    $stmt->execute([$u_id]);
    $config = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parâmetros de Pagamento - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] flex min-h-screen antialiased text-gray-800">

    <aside class="w-72 bg-white border-r border-gray-100 flex flex-col hidden lg:flex">
        <div class="p-8">
            <a href="dashboard.php" class="text-2xl font-black italic tracking-tighter text-[#00a650]">
                $UPER<span style="color: #2c3e50;">$ORTE</span>
            </a>
            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest mt-1">SaaS Platform</p>
        </div>
        <nav class="flex-grow px-6 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 transition-all">Dashboard</a>
            <a href="#" class="flex items-center gap-3 px-6 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest bg-[#00a650] text-white shadow-xl">Pagamento</a>
        </nav>
    </aside>

    <main class="flex-grow flex flex-col">
        <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 px-8 py-4 flex justify-between items-center sticky top-0 z-50">
            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Painel SaaS / Configurações / Pagamento</h2>
            <a href="dashboard.php" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-800">Voltar</a>
        </header>

        <div class="p-8 max-w-4xl mx-auto w-full">
            <div class="bg-white rounded-[4rem] shadow-2xl border border-gray-100 overflow-hidden">
                <div class="p-10 lg:p-16">
                    <h1 class="text-4xl font-black tracking-tighter italic uppercase mb-2 leading-[0.9]">Seus Parâmetros</h1>
                    <p class="text-gray-400 text-sm font-medium mb-12 italic">Defina por onde você receberá o dinheiro das suas rifas.</p>

                    <?php if($msg): ?>
                        <div class="bg-green-50 text-green-700 px-6 py-4 rounded-2xl border border-green-100 mb-8 font-black text-xs uppercase tracking-widest">
                            <?php echo $msg; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-10">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 block italic">Método de Recebimento</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="gateway" value="pix_manual" class="hidden peer" <?php echo ($config['gateway'] ?? 'pix_manual') == 'pix_manual' ? 'checked' : ''; ?>>
                                    <div class="p-6 border border-gray-100 rounded-[2rem] text-center peer-checked:border-green-500 peer-checked:bg-green-50/50 transition-all">
                                        <p class="text-[10px] font-black uppercase tracking-widest">Chave PIX</p>
                                        <p class="text-[8px] text-gray-400 mt-1 uppercase italic">Manual (Sem API)</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="gateway" value="mercado_pago" class="hidden peer" <?php echo ($config['gateway'] ?? '') == 'mercado_pago' ? 'checked' : ''; ?>>
                                    <div class="p-6 border border-gray-100 rounded-[2rem] text-center peer-checked:border-blue-500 peer-checked:bg-blue-50/50 transition-all">
                                        <p class="text-[10px] font-black uppercase tracking-widest">Mercado Pago</p>
                                        <p class="text-[8px] text-gray-400 mt-1 uppercase italic">API de Recebimento</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="gateway" value="efi" class="hidden peer" <?php echo ($config['gateway'] ?? '') == 'efi' ? 'checked' : ''; ?>>
                                    <div class="p-6 border border-gray-100 rounded-[2rem] text-center peer-checked:border-orange-500 peer-checked:bg-orange-50/50 transition-all">
                                        <p class="text-[10px] font-black uppercase tracking-widest">Efí Pay</p>
                                        <p class="text-[8px] text-gray-400 mt-1 uppercase italic">API Oficial PIX</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-8">
                             <!-- Opções Adicionais -->
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-8 border-b border-gray-50">
                                <label class="flex items-center gap-4 bg-gray-50 p-6 rounded-[2rem] border border-gray-100 cursor-pointer hover:border-green-500 transition-all">
                                     <input type="checkbox" name="habilitar_cartao" class="w-5 h-5 accent-[#00a650]" <?php echo ($config['habilitar_cartao'] ?? 0) ? 'checked' : ''; ?>>
                                     <div>
                                         <p class="text-[10px] font-black uppercase tracking-widest text-gray-800 italic">Habilitar Cartão de Crédito</p>
                                         <p class="text-[8px] text-gray-400 mt-0.5 uppercase">Disponível para Mercado Pago e Efí</p>
                                     </div>
                                </label>
                                <label class="flex items-center gap-4 bg-gray-50 p-6 rounded-[2rem] border border-gray-100 cursor-pointer hover:border-green-500 transition-all">
                                     <input type="checkbox" name="repassar_taxas" class="w-5 h-5 accent-[#00a650]" <?php echo ($config['repassar_taxas'] ?? 0) ? 'checked' : ''; ?>>
                                     <div>
                                         <p class="text-[10px] font-black uppercase tracking-widest text-gray-800 italic">Repassar Taxa ao Comprador</p>
                                         <p class="text-[8px] text-gray-400 mt-0.5 uppercase">O cliente arca com os custos do gateway</p>
                                     </div>
                                </label>
                             </div>

                             <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block">Sua Chave PIX (Recebimento Direto)</label>
                                <input type="text" name="chave_pix" value="<?php echo $config['chave_pix'] ?? ''; ?>" 
                                       class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                       placeholder="Chave PIX para pagamentos manuais">
                            </div>

                            <div class="border-t border-gray-50 pt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block leading-none">Access Token (Secret Key)</label>
                                    <input type="text" name="mp_access_token" value="<?php echo $config['mp_access_token'] ?? ''; ?>" 
                                           class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                           placeholder="APP_USR-...">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block leading-none">Public Key (Opicional)</label>
                                    <input type="text" name="mp_public_key" value="<?php echo $config['mp_public_key'] ?? ''; ?>" 
                                           class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                           placeholder="APP_USR-...">
                                </div>
                            </div>

                            <div class="border-t border-gray-50 pt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block leading-none">Client ID Efí</label>
                                    <input type="text" name="efi_client_id" value="<?php echo $config['efi_client_id'] ?? ''; ?>" 
                                           class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all"
                                           placeholder="Client_Id_...">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 block leading-none">Client Secret Efí</label>
                                    <input type="text" name="efi_client_secret" value="<?php echo $config['efi_client_secret'] ?? ''; ?>" 
                                           class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all"
                                           placeholder="Client_Secret_...">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-8">
                            <button type="submit" class="bg-gray-900 text-white font-black px-12 py-5 rounded-2xl shadow-xl hover:bg-black transition-all transform hover:scale-105 uppercase tracking-widest text-[10px]">
                                Salvar Parâmetros
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
