<?php require_once 'backend/config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - $UPER$ORTE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="frontend/png/cifrao_premium.png">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased pb-20">
    <header class="bg-white shadow sticky top-0 z-40">
        <div class="max-w-md md:max-w-2xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-xl font-black italic tracking-tighter" style="color: #00a650;">$UPER<span style="color: #2c3e50;">$ORTE</span></h1>
            <a href="index.php" class="text-xs font-bold text-gray-500 bg-gray-100 px-4 py-2 rounded-full hover:bg-gray-200 transition-colors uppercase tracking-wider">Voltar</a>
        </div>
    </header>

    <main class="max-w-md md:max-w-2xl mx-auto px-4 mt-8">
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-6">
                 <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center shadow-inner border border-blue-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                 </div>
                 <h2 class="text-2xl font-black text-[#2c3e50] tracking-tight">Privacidade</h2>
            </div>
            
            <div class="space-y-6 text-sm text-gray-600 leading-relaxed font-medium">
                <section>
                    <h3 class="text-gray-800 font-bold uppercase text-[11px] tracking-widest mb-2">1. Coleta de Informações</h3>
                    <p>Coletamos apenas as informações essenciais para identificação e contato dos participantes (Nome Completo) e para o processamento de pagamentos (Chave PIX e telefone WhatsApp). Não coletamos dados desnecessários ou sensíveis sem sua expressa autorização.</p>
                </section>

                <section>
                    <h3 class="text-gray-800 font-bold uppercase text-[11px] tracking-widest mb-2">2. Finalidade dos Dados</h3>
                    <p>Seus dados são utilizados exclusivamente para identificá-lo como o comprador legítimo da cota e para entrar em contato caso você seja o ganhador de um sorteio. Não vendemos suas informações para terceiros.</p>
                </section>

                <section>
                    <h3 class="text-gray-800 font-bold uppercase text-[11px] tracking-widest mb-2">3. Segurança dos Dados</h3>
                    <p>Utilizamos protocolos de conexão segura (SSL) e criptografia para proteger os dados trafegados entre seu navegador e nossos servidores. Os pagamentos são processados por gateways certificados e seguros.</p>
                </section>

                <section>
                    <h3 class="text-gray-800 font-bold uppercase text-[11px] tracking-widest mb-2">4. Compartilhamento de Informações</h3>
                    <p>Seus dados de pagamento são compartilhados apenas com a instituição financeira processadora do PIX. Em caso de ganhador, o nome e o número premiado podem ser divulgados publicamente na Galeria de Ganhadores para garantir a transparência do sorteio.</p>
                </section>

                <section>
                    <h3 class="text-gray-800 font-bold uppercase text-[11px] tracking-widest mb-2">5. Seus Direitos</h3>
                    <p>Você tem o direito de solicitar a exclusão de seus dados do nosso banco de dados a qualquer momento, desde que não haja obrigações legais de manutenção desses registros vinculados a pagamentos processados.</p>
                </section>

                <section>
                    <h3 class="text-gray-800 font-bold uppercase text-[11px] tracking-widest mb-2">6. Alterações</h3>
                    <p>Podemos atualizar esta política ocasionalmente para refletir mudanças em nossos processos ou por exigências legais. Recomendamos a leitura periódica deste documento.</p>
                </section>
            </div>

            <div class="mt-10 pt-8 border-t border-gray-100 text-center">
                <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest underline italic">Políticas válidas para 2026</p>
            </div>
        </div>
    </main>

    <footer class="mt-10 mb-10 text-center px-4">
        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">© 2026 $uper$orte - Sua privacidade é nossa prioridade</p>
    </footer>
</body>
</html>
