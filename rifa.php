<?php
require_once 'backend/config.php';
$protocol = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"));
$site_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

// --- SISTEMA DE REFERÊNCIA (AFILIADO) ---
if (isset($_GET['ref']) && is_numeric($_GET['ref'])) {
    // Salva ID e o Timestamp atual (ID|TIMESTAMP)
    // Cookie de SESSÃO (expira ao fechar navegador)
    $val = intval($_GET['ref']) . "|" . time();
    setcookie('ref_afiliado', $val, 0, "/");
} else {
    // Se não tem ?ref= na URL, verificamos se o cliente veio de fora
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'];
    if (!empty($referer) && strpos($referer, $host) === false) {
        setcookie('ref_afiliado', '', time() - 3600, "/");
    } else if (empty($referer)) {
        setcookie('ref_afiliado', '', time() - 3600, "/");
    }
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT nome, preco_numero, imagem_url, status, premio1, premio2, premio3, premio4, premio5 FROM rifas WHERE id = ?");
    $stmt->execute([$id]);
    $rifa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rifa || $rifa['status'] === 'fechada') {
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: index.php');
    exit;
}

$brand = '$UPER$ORTE';
$title = $rifa ? $rifa['nome'] . " - $brand" : "$brand - Rifa Online";
$desc = $rifa ? 'Concorra a ' . $rifa['nome'] . ' por apenas R$ ' . number_format($rifa['preco_numero'], 2, ',', '.') : 'Sua sorte está aqui. Participe das nossas rifas online.';

// Robust image path logic
$imgPath = ($rifa && !empty($rifa['imagem_url'])) ? $rifa['imagem_url'] : 'frontend/png/cifrao_premium.png';
$image = $site_url . "/" . ltrim(str_replace(' ', '%20', $imgPath), '/');
$raffleUrl = $site_url . "/rifa.php?id=" . $id;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    
    <!-- TAGS DE COMPARTILHAMENTO PREMIUM (WhatsApp/Instagram/Facebook) -->
    <meta property="og:site_name" content="<?php echo $brand; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $raffleUrl; ?>">
    <meta property="og:title" content="<?php echo $title; ?>">
    <meta property="og:description" content="<?php echo $desc; ?>">
    <meta property="og:image" content="<?php echo $image; ?>">
    <meta property="og:image:secure_url" content="<?php echo $image; ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1280">
    <meta property="og:image:height" content="720">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $title; ?>">
    <meta name="twitter:description" content="<?php echo $desc; ?>">
    <meta name="twitter:image" content="<?php echo $image; ?>">
    <meta name="description" content="<?php echo $desc; ?>">

    <!-- Tailwind CSS (via CDN para simplicidade) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="frontend/css/style.css">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">

    <!-- Mercado Pago SDK -->
    <script src="https://sdk.mercadopago.com/js/v2"></script>
</head>

<body class="bg-gray-100 text-gray-800 font-sans pb-32">

    <!-- Header -->
    <header class="bg-white shadow">
        <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-black italic tracking-tighter" style="color: #00a650;">$UPER<span
                    style="color: #2c3e50;">$ORTE</span></h1>
            <div class="flex gap-2">
                <a href="javascript:void(0)" onclick="showAlert('Esta funcionalidade está em desenvolvimento e será liberada em breve.', 'Em breve!', 'info')"
                    class="text-[10px] md:text-xs font-black text-white bg-[#00a650] hover:bg-[#009647] rounded-full px-3 py-1.5 transition-colors flex items-center gap-1 uppercase tracking-tighter">
                    Ganhe Dinheiro
                </a>
                <a href="index.php"
                    class="text-[10px] md:text-xs font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-full px-3 py-1.5 transition-colors flex items-center gap-1">
                    Voltar
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-md md:max-w-2xl mx-auto mt-6 px-4 bg-white rounded-xl shadow p-6 border border-gray-100 mb-10">
        <div class="text-center mb-6">
            <h2 class="text-lg md:text-xl font-extrabold text-[#2c3e50] uppercase tracking-wide">Grade de Números</h2>
            <div id="badge-sorteio"
                class="inline-block bg-[#00a650] text-white text-[10px] md:text-xs px-3 py-1 mt-2 rounded-full font-bold uppercase tracking-wider shadow-sm">
                Sorteio oficial...
            </div>
            <div id="edition-badge" class="hidden mt-2">
                <span class="bg-purple-50 text-purple-600 text-[10px] md:text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider">
                    EDIÇÃO #<span id="rifa-id-num">----</span>
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-2 font-medium">Selecione os números em verde</p>

            <!-- Botão de Compartilhar Rifa -->
            <div class="mt-6 flex justify-center">
                <button onclick="shareRaffle()" class="flex items-center gap-2 bg-[#25D366] text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg hover:bg-[#128C7E] active:scale-95 transition-all outline-none">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 21.41a10.985 10.985 0 0 1-5.6-1.53l-6.22 1.63 1.66-6.07a10.992 10.992 0 1 1 10.16 5.97zm0-19.14a8.77 8.77 0 1 0 8.77 8.77 8.78 8.78 0 0 0-8.77-8.77zm4.8 12c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.57.71-.7.86-.26.16-.48.05a6.044 6.044 0 0 1-1.78-1.09 6.64 6.64 0 0 1-1.23-1.53c-.11-.2-.01-.31.1-.42.1-.1.22-.26.33-.4.11-.14.15-.22.22-.38.07-.15.03-.3-.02-.42-.05-.11-.5-.1.22-.68.21s-.33.27-.33.32a2.02 2.02 0 0 0 .61 1.41 5.925 5.925 0 0 0 1.94 1.34 13.4 13.4 0 0 0 2.44.82 2.924 2.924 0 0 0 1.34 0 2.053 2.053 0 0 0 .54-1.77 1.68 1.68 0 0 0-.25-.43z"></path></svg>
                    Compartilhar
                </button>
            </div>
        </div>

        <!-- Legenda -->
        <div class="flex justify-center gap-3 text-[10px] sm:text-xs font-semibold mb-6 flex-wrap">
            <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-[#00a650]"></span> DISPONÍVEL
            </div>
            <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-[#f1c40f]"></span> EM RESERVA
            </div>
            <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-[#8e44ad]"></span> PAGO</div>
            <div class="flex items-center gap-1"><span
                    class="w-3 h-3 border-2 border-[#8e44ad] rounded-full bg-white"></span> SELECIONADO</div>
        </div>

        <!-- Grade de Números -->
        <div id="grid-container" class="grid-numbers">
            <!-- Injetado via JS -->
            <div class="text-center w-full py-10 font-bold text-gray-400 col-span-5 md:col-span-10">Carregando
                números...</div>
        </div>
    </main>

    <!-- Footer Bar -->
    <div class="fixed bottom-0 w-full left-0 bg-white border-t border-gray-200 p-3 shadow-lg z-40 transform translate-y-full transition-transform duration-300"
        id="bottom-bar">
        <div class="max-w-4xl mx-auto flex justify-between items-center h-full gap-4 px-2">
            <div class="flex flex-col flex-1 pl-2">
                <span class="text-xs font-bold text-gray-500 uppercase flex items-center gap-1">
                    <span id="selected-count">0</span> Selecionados
                </span>
                <span class="text-sm font-black text-[#2c3e50]" id="selected-total">Total: R$ 0,00</span>
            </div>
            <button id="btn-open-reserve-modal"
                class="bg-[#8e44ad] text-white font-bold py-3 px-6 rounded-lg shadow uppercase text-sm w-1/2 flex-none hover:bg-[#7b3699] transition-colors">
                Reservar Agora
            </button>
        </div>
    </div>

    <!-- Modais Overlay -->
    <div id="modal-overlay"
        class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden opacity-0 transition-opacity duration-300 backdrop-blur-sm">
    </div>

    <!-- Modal Reservar -->
    <div id="modal-reserve"
        class="modal-box bg-white fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[90%] max-w-sm rounded-[1.5rem] p-6 z-50 flex-col gap-4 hidden overflow-hidden shadow-2xl">
        <div class="flex justify-between items-center mb-4 text-[#00a650]">
            <h3 class="font-bold text-lg text-[#00a650] uppercase w-full text-center">Reservar Meus Números</h3>
        </div>

        <div class="mb-5 text-center bg-gray-50 rounded-lg p-3 border border-gray-100">
            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Resumo da sua sorte</p>
            <div id="modal-selected-nums" class="flex flex-wrap justify-center gap-1 mb-2"></div>
            <p class="font-black text-2xl text-[#2c3e50]" id="modal-total-value">R$ 0,00</p>
        </div>

        <div class="flex flex-col gap-3">
            <div>
                <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Nome Completo</label>
                <input type="text" id="input-name"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-[#00a650] focus:border-[#00a650] outline-none"
                    placeholder="Nome e Sobrenome" required>
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Seu WhatsApp</label>
                <input type="tel" id="input-whatsapp"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-[#00a650] focus:border-[#00a650] outline-none"
                    placeholder="(00) 00000-0000" required>
            </div>
        </div>

        <button id="btn-submit-reservation"
            class="w-full bg-[#00a650] text-white font-bold py-4 rounded-xl shadow uppercase text-sm mt-5 hover:bg-[#009b4a] transition-colors relative overflow-hidden group">
            Prosseguir para Pagamento
        </button>

        <button id="btn-close-reserve-modal"
            class="w-full text-gray-400 font-semibold py-2 uppercase text-[10px] mt-2 underline hover:text-gray-600 transition-colors">
            Desistir e Liberar Números
        </button>
    </div>
    <!-- Modal Escolha Pagamento -->
    <div id="modal-payment-method"
        class="modal-box bg-white fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[90%] max-w-sm rounded-[1.5rem] p-6 text-center z-50 flex-col gap-4 hidden shadow-2xl">
        <h3 class="font-bold text-lg text-[#2c3e50] uppercase mb-1">Como deseja pagar?</h3>
        <p class="text-[10px] text-gray-400 font-bold uppercase mb-4">Escolha a melhor opção para você</p>

        <div class="flex flex-col gap-3">
            <button id="btn-pay-pix" class="flex items-center gap-4 bg-gray-50 hover:bg-green-50 p-4 rounded-2xl border border-gray-100 hover:border-green-200 transition-all text-left group">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor"><path d="M22.25 12l-10-10-10 10 10 10 10-10zm-10-8.5l8.5 8.5-8.5 8.5-8.5-8.5 8.5-8.5z"/></svg>
                </div>
                <div>
                    <span class="block font-black text-gray-800 uppercase text-sm">Pix</span>
                    <span class="block text-[10px] text-gray-500 font-bold">Aprovação Imediata</span>
                </div>
            </button>

            <button id="btn-pay-card" class="flex items-center gap-4 bg-gray-50 hover:bg-indigo-50 p-4 rounded-2xl border border-gray-100 hover:border-indigo-200 transition-all text-left group">
                <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div>
                    <span class="block font-black text-gray-800 uppercase text-sm">Cartão de Crédito</span>
                    <span class="block text-[10px] text-gray-500 font-bold italic">Em até 12x</span>
                </div>
            </button>
        </div>

        <button onclick="hideModals()" class="text-xs text-gray-400 font-bold mt-4 underline uppercase">Voltar</button>
    </div>

    <!-- Modal Cartão de Crédito (Formulário) -->
    <div id="modal-card" class="modal-box bg-white fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[95%] max-w-md rounded-[2rem] p-6 z-50 flex-col gap-4 hidden overflow-hidden shadow-2xl">
        <h3 class="font-bold text-lg text-indigo-700 uppercase w-full text-center mb-2">Dados do Cartão</h3>
        
        <div class="bg-red-50 text-red-600 font-bold p-2 text-sm rounded-lg border border-red-100 flex items-center justify-center gap-2 mb-2">
            <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span id="countdown-card">00:00</span>
        </div>

        <p class="text-[10px] font-bold text-gray-500 mb-2 px-2 tracking-wide uppercase text-center">
            Seus números estão reservados! Conclua o pagamento antes do tempo acabar.
        </p>

        <!-- MP Brick Container or Custom Form -->
        <div id="paymentCardBrick_container"></div>

        <button onclick="hideModals()" class="w-full text-gray-400 font-semibold py-2 uppercase text-[10px] mt-2 underline hover:text-gray-600 transition-colors">
            Cancelar e Voltar
        </button>
    </div>

    <!-- Modal PIX -->
    <div id="modal-pix"
        class="modal-box bg-white fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[90%] max-w-sm rounded-[1.5rem] p-6 text-center z-50 flex-col gap-4 hidden shadow-2xl">
        <h3 class="font-bold text-lg text-[#00a650] uppercase mb-2">Pagamento PIX</h3>

        <div
            class="bg-red-50 text-red-600 font-bold p-3 rounded-lg border border-red-100 flex items-center justify-center gap-2 mb-4">
            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span id="countdown">05:00</span>
        </div>

        <p class="text-xs font-bold text-gray-500 mb-4 px-2 tracking-wide uppercase">
            Quase lá! Seu número está reservado. Complete o pagamento para garantir sua participação.
        </p>

        <div class="flex justify-center mb-4">
            <img id="pix-qrcode-img" src="" alt="QR Code PIX"
                class="w-48 h-48 border border-gray-200 rounded p-1 object-cover">
        </div>

        <div class="relative w-full">
            <input type="text" id="pix-copiacola-input" readonly
                class="text-xs w-full bg-gray-100 border border-gray-200 rounded-lg p-3 font-mono text-gray-600 pr-12 focus:outline-none focus:ring-1 focus:ring-gray-300"
                value="">
        </div>

        <button id="btn-copy-pix"
            class="w-full bg-[#00a650] text-white font-bold py-4 rounded-xl shadow uppercase text-sm mt-4 inline-flex items-center justify-center gap-2 hover:bg-[#009b4a]">
            Copiar Código PIX
        </button>

        <p class="text-xs text-gray-400 mt-4 italic">O pagamento é confirmado automaticamente pelo sistema. Aguarde
            nesta tela.</p>
    </div>

    <!-- Modal Expired -->
    <div id="modal-expired"
        class="modal-box bg-white fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[90%] max-w-sm rounded-[1.5rem] p-8 text-center z-50 flex-col gap-4 hidden shadow-2xl">
        <div
            class="w-20 h-20 bg-red-50 rounded-full mx-auto flex items-center justify-center mb-2 shadow-inner border border-red-100">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-black text-[#2c3e50] tracking-tight">Tempo Esgotado</h2>
        <p class="text-sm font-medium text-gray-500 mb-2">Seus números voltaram para disponíveis.</p>
        <button onclick="hideModals(); window.location.reload();"
            class="w-full bg-[#2c3e50] hover:bg-gray-800 text-white font-black py-4 rounded-xl shadow uppercase text-sm transition-colors mt-2">
            Entendido
        </button>
    </div>

    <!-- Modal Success -->
    <div id="modal-success"
        class="modal-box bg-white fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[90%] max-w-xs rounded-2xl p-6 text-center z-50 flex-col gap-4 hidden shadow-2xl">
        <div class="w-16 h-16 bg-[#00a650] rounded-full mx-auto flex items-center justify-center mb-2 shadow-inner">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h3 class="font-extrabold text-[#2c3e50] text-xl">Números Confirmados</h3>
        <p class="text-xs text-gray-500 font-medium">Parabéns! Seus números foram pagos com sucesso. Boa sorte no
            sorteio!</p>

        <a id="btn-group-vip" href="#" target="_blank"
            class="hidden bg-[#25D366] text-white font-bold text-xs py-3 px-6 rounded-lg w-full mt-4 hover:bg-[#128C7E] uppercase shadow flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M12.031 21.41a10.985 10.985 0 0 1-5.6-1.53l-6.22 1.63 1.66-6.07a10.992 10.992 0 1 1 10.16 5.97zm0-19.14a8.77 8.77 0 1 0 8.77 8.77 8.78 8.78 0 0 0-8.77-8.77zm4.8 12c-.22-.11-1.3-.64-1.5-.71-.2-.07-.35-.11-.5.11s-.57.71-.7.86-.26.16-.48.05a6.044 6.044 0 0 1-1.78-1.09 6.64 6.64 0 0 1-1.23-1.53c-.11-.2-.01-.31.1-.42.1-.1.22-.26.33-.4.11-.14.15-.22.22-.38.07-.15.03-.3-.02-.42-.05-.11-.5-.1.22-.68.21s-.33.27-.33.32a2.02 2.02 0 0 0 .61 1.41 5.925 5.925 0 0 0 1.94 1.34 13.4 13.4 0 0 0 2.44.82 2.924 2.924 0 0 0 1.34 0 2.053 2.053 0 0 0 .54-1.77 1.68 1.68 0 0 0-.25-.43z">
                </path>
            </svg>
            Entrar no Grupo VIP
        </a>

        <button id="btn-close-success"
            class="bg-[#8e44ad] text-white font-bold text-xs py-3 px-6 rounded-lg w-full mt-2 hover:bg-[#7b3699] uppercase shadow">OK</button>
    </div>

    <!-- Modal Alerta Personalizado -->
    <div id="modal-alert"
        class="modal-box bg-white fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[90%] max-w-xs rounded-3xl p-8 text-center z-[100] flex-col gap-4 hidden shadow-2xl">
        <div id="alert-icon-error"
            class="w-16 h-16 bg-red-50 text-red-500 rounded-full mx-auto flex items-center justify-center mb-2 shadow-inner border border-red-100">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        <div id="alert-icon-info"
            class="hidden w-16 h-16 bg-blue-50 text-blue-500 rounded-full mx-auto flex items-center justify-center mb-2 shadow-inner border border-blue-100">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 id="alert-title" class="font-extrabold text-[#2c3e50] text-xl">Atenção</h3>
        <p id="alert-message" class="text-sm text-gray-500 font-medium mt-2 leading-relaxed">Mensagem de alerta.</p>

        <button id="btn-close-alert"
            class="w-full bg-[#2c3e50] hover:bg-gray-800 text-white font-black py-4 rounded-2xl shadow uppercase text-sm transition-colors mt-6">
            Entendido
        </button>
    </div>

    <script src="frontend/js/app.js?v=<?= time() ?>"></script>
    <script>
        function shareRaffle() {
            const title = "<?php echo str_replace('"', '\"', $title); ?>";
            let msg = "🚨 *NOVA RIFA LANÇADA!* 🚨\n\n🎟️ *<?php echo str_replace('"', '\"', $rifa['nome'] ?? 'Rifa'); ?>*\n💰 Apenas R$ <?php echo number_format($rifa['preco_numero'] ?? 0, 2, ',', '.'); ?> por número!";
            
            const premios = [
                "<?php echo str_replace('"', '\"', $rifa['premio1']); ?>",
                "<?php echo str_replace('"', '\"', $rifa['premio2']); ?>",
                "<?php echo str_replace('"', '\"', $rifa['premio3']); ?>",
                "<?php echo str_replace('"', '\"', $rifa['premio4']); ?>",
                "<?php echo str_replace('"', '\"', $rifa['premio5']); ?>"
            ];

            let premioStr = "";
            let medalhas = ["🏆", "🥈", "🥉", "🏅", "🎖️"];
            
            premios.forEach((p, index) => {
                if(p && p.trim() !== "") {
                    premioStr += `\n${medalhas[index]} *${index + 1}º:* ${p.trim()}`;
                }
            });

            if(premioStr) {
                msg += "\n\n🎁 *PRÊMIOS:*" + premioStr;
            }

            msg += "\n\n👇 *PARTICIPE AGORA:* \n" + window.location.href;

            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: msg,
                    url: window.location.href
                }).catch(console.error);
            } else {
                const waLink = `https://api.whatsapp.com/send?text=${encodeURIComponent(msg)}`;
                window.open(waLink, '_blank');
            }
        }
    </script>
</body>

</html>
