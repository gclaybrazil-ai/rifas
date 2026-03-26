<?php
require_once 'backend/config.php';

$reserva_id = $_GET['reserva_id'] ?? null;
if (!$reserva_id) {
    die("Reserva não encontrada.");
}

// 1. Fetch Reservation Info
$stmt = $pdo->prepare("SELECT res.*, r.titulo, r.valor_numero, u.whatsapp as criador_whatsapp, u.nome as criador_nome
                       FROM reservas res 
                       JOIN rifas r ON res.rifa_id = r.id 
                       JOIN usuarios u ON r.usuario_id = u.id 
                       WHERE res.id = ?");
$stmt->execute([$reserva_id]);
$reserva = $stmt->fetch();

if (!$reserva) {
    die("Reserva inválida.");
}

// 2. Fetch Selected Numbers
$stmt = $pdo->prepare("SELECT numero FROM numeros WHERE reserva_id = ?");
$stmt->execute([$reserva_id]);
$numeros = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Format numbers
$numeros_str = implode(', ', array_map(function($n) use ($reserva) {
    return str_pad($n, strlen($reserva['total_numeros']-1), "0", STR_PAD_LEFT);
}, $numeros));

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-gray-800 antialiased min-h-screen flex flex-col">

    <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 py-6 text-center">
        <a href="index.php" class="text-2xl font-black italic tracking-tighter text-[#00a650]">
            $UPER<span style="color: #2c3e50;">$ORTE</span>
        </a>
    </header>

    <main class="flex-grow max-w-2xl mx-auto px-6 py-12 w-full">
        
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-green-50 text-green-500 rounded-full mb-6 border border-green-100">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h1 class="text-3xl font-black text-[#2c3e50] tracking-tighter uppercase italic">Pedido Reservado!</h1>
            <p class="text-gray-500 font-medium italic">Efetue o pagamento via PIX para garantir sua participação.</p>
        </div>

        <!-- Order Summary Card -->
        <div class="bg-white rounded-[3rem] shadow-2xl border border-gray-100 overflow-hidden mb-8">
            <div class="p-8 lg:p-12">
                <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-10 pb-10 border-b border-gray-100">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Campanha</p>
                        <h2 class="text-xl font-black text-gray-800 uppercase italic"><?php echo $reserva['titulo']; ?></h2>
                        <p class="text-[10px] font-medium text-gray-400 mt-1 uppercase italic tracking-widest">Organizado por <?php echo $reserva['criador_nome']; ?></p>
                    </div>
                    <div class="text-left md:text-right">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total a Pagar</p>
                        <p class="text-3xl font-black text-[#00a650]">R$ <?php echo number_format($reserva['valor_total'], 2, ',', '.'); ?></p>
                    </div>
                </div>

                <div class="space-y-6 mb-10">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Seus Números (<?php echo count($numeros); ?>)</p>
                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-6 flex flex-wrap gap-2">
                             <?php foreach($numeros as $n): ?>
                                <span class="bg-white border border-gray-100 px-4 py-2 rounded-xl text-[10px] font-black text-gray-600 shadow-sm"><?php echo str_pad($n, 2, "0", STR_PAD_LEFT); ?></span>
                             <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- QR Code & Instructions -->
                <div class="bg-gray-900 rounded-[2.5rem] p-8 lg:p-12 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-tr from-green-500/20 to-transparent opacity-50"></div>
                    <div class="relative z-10">
                        <h3 class="text-white text-[10px] font-black uppercase tracking-widest mb-8">Pagamento via PIX</h3>
                        
                        <!-- Mockup QR -->
                        <div class="w-48 h-48 bg-white p-4 rounded-3xl mx-auto mb-8 shadow-2xl">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=SUPERSORTE_RESERVA_<?php echo $reserva_id; ?>" 
                                 class="w-full h-full" alt="QR Code PIX">
                        </div>

                        <p class="text-white/60 text-[9px] font-black uppercase tracking-widest mb-4">Ou use o Copia e Cola:</p>
                        <div class="bg-white/10 border border-white/20 rounded-2xl p-4 flex items-center justify-between gap-4 group hover:border-white/40 transition-all">
                            <input type="text" readonly value="00020101021226580014br.gov.bcb.pix0136..." 
                                   class="bg-transparent text-white text-[10px] font-black w-full focus:outline-none">
                            <button class="bg-white text-gray-900 px-6 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-green-500 hover:text-white transition-all">Copiar</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-8 bg-gray-50 text-center border-t border-gray-100">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 italic">Confirmou o pagamento? O sistema liberará seus números automaticamente após a validação.</p>
                <div class="flex justify-center gap-4">
                     <a href="https://wa.me/<?php echo $reserva['criador_whatsapp']; ?>?text=Ol%C3%A1%2C+acabei+de+fazer+o+pagamento+da+reserva+%23<?php echo $reserva_id; ?>+na+rifa+<?php echo urlencode($reserva['titulo']); ?>" 
                        class="flex items-center gap-3 bg-[#25D366] text-white px-8 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl hover:bg-[#128C7E] transition-all transform hover:-translate-y-1">
                        <svg class="w-5 h-5 shadow-sm" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.417-.003 6.557-5.338 11.892-11.893 11.892-1.997-.001-3.951-.5-5.688-1.448l-6.305 1.652zm6.599-3.352l.3.179c1.472.873 3.166 1.334 4.89 1.335 5.432 0 9.851-4.42 9.851-9.854 0-2.628-1.023-5.099-2.88-6.958s-4.329-2.881-6.957-2.881c-5.433 0-9.851 4.417-9.851 9.853 0 1.831.503 3.618 1.458 5.174l.192.313-1.11 4.053 4.147-1.087zM17.446 14.12c-.3-.15-1.771-.874-2.045-.974-.275-.1-.475-.15-.675.15-.2.3-.775.974-.95 1.174-.175.2-.35.225-.65.075-.3-.15-1.265-.467-2.41-1.487-.89-.794-1.49-1.774-1.665-2.074-.175-.3-.018-.462.13-.611.134-.133.3-.349.45-.524.15-.175.2-.3.3-.5s.05-.375-.025-.525c-.075-.15-.675-1.625-.925-2.225-.25-.6-.503-.513-.675-.522l-.574-.01c-.2 0-.525.075-.8.375s-1.05 1.025-1.05 2.5 1.075 2.9 1.225 3.1c.15.2 2.11 3.22 5.11 4.52.714.309 1.27.494 1.703.631.714.227 1.365.195 1.88.118.573-.085 1.771-.724 2.021-1.424.25-.7.25-1.3.175-1.425-.075-.125-.275-.2-.575-.35z"></path></svg>
                        Mandar Comprovante
                     </a>
                </div>
            </div>
        </div>

    </main>

</body>
</html>
