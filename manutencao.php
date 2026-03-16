<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenção - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao.png">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #2d1b4e 0%, #0f0a1e 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .floating {
            animation: floating 4s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 overflow-hidden">
    <!-- Background Decor -->
    <div class="fixed top-[-10%] right-[-10%] w-[40vw] h-[40vw] bg-indigo-600/20 blur-[120px] rounded-full"></div>
    <div class="fixed bottom-[-10%] left-[-10%] w-[30vw] h-[30vw] bg-purple-600/20 blur-[100px] rounded-full"></div>

    <div class="max-w-2xl w-full text-center relative z-10">
        <!-- Logo -->
        <div class="mb-12 flex justify-center">
            <div class="relative">
                <div class="absolute inset-0 bg-indigo-500 blur-2xl opacity-30 animate-pulse"></div>
                <h1 class="text-4xl font-black text-white tracking-tighter relative group">
                    <span class="text-indigo-400">$</span>UPER<span class="text-indigo-400">$</span>ORTE
                </h1>
            </div>
        </div>

        <!-- Illustration Holder -->
        <div class="relative mb-10 floating">
            <img src="frontend/png/maintenance_illustration_1773625003308.png" alt="Manutenção" class="mx-auto w-64 h-64 object-contain drop-shadow-[0_0_30px_rgba(99,102,241,0.4)] rounded-3xl">
        </div>

        <!-- content -->
        <div class="glass rounded-[2.5rem] p-10 md:p-14 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 shimmer opacity-50"></div>
            
            <span class="inline-block px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-[10px] font-bold uppercase tracking-[0.2em] mb-6">
                System Update in Progress
            </span>

            <h2 class="text-3xl md:text-5xl font-black text-white mb-6 leading-tight">
                Estamos ajustando a sua <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">Próxima Sorte</span>
            </h2>

            <p class="text-gray-400 text-lg md:text-xl font-light mb-10 max-w-lg mx-auto">
                No momento, estamos realizando melhorias rápidas no sistema para garantir que sua experiência seja impecável. 
            </p>

            <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                <div class="w-full md:w-auto px-8 py-4 bg-white text-gray-900 font-bold rounded-2xl shadow-xl hover:scale-105 transition-transform cursor-default">
                    Voltamos em breve
                </div>
                <a href="https://wa.me/5521981577453" target="_blank" class="w-full md:w-auto px-8 py-4 glass text-white font-bold rounded-2xl hover:bg-white/10 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Suporte WhatsApp
                </a>
            </div>
        </div>

        <p class="mt-12 text-gray-500 text-sm font-medium">
            Copyright &copy; 2026 $uper$orte. Todos os direitos reservados.
        </p>
    </div>

    <!-- Interactive Mouse Orb -->
    <div id="orb" class="fixed top-0 left-0 w-96 h-96 bg-indigo-500/10 blur-[100px] rounded-full pointer-events-none transition-transform duration-700 ease-out"></div>

    <script>
        const orb = document.getElementById('orb');
        document.addEventListener('mousemove', (e) => {
            const x = e.clientX - 192;
            const y = e.clientY - 192;
            orb.style.transform = `translate(${x}px, ${y}px)`;
        });

        // Check maintenance status every 5 seconds
        async function checkStatus() {
            try {
                const res = await fetch('backend/api/get_rifas.php');
                const data = await res.json();
                if (!data.maintenance) {
                    window.location.href = 'index.html';
                }
            } catch (e) {}
        }
        setInterval(checkStatus, 5000);
    </script>
</body>
</html>
