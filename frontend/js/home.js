document.addEventListener('DOMContentLoaded', async () => {
    const listEl = document.getElementById('campaign-list');
    const finalizadasListEl = document.getElementById('finalizadas-list');
    const finalizadasSection = document.getElementById('rifas-finalizadas');
    
    try {
        const res = await fetch('backend/api/get_rifas.php');
        const data = await res.json();
        
        if(data.success) {
            listEl.innerHTML = ''; // Limpa loader
            
            // Renderiza as Ativas
            if(data.ativas && data.ativas.length > 0) {
                data.ativas.forEach(rifa => renderCard(rifa, listEl, false));
            } else {
                // Layout Vazio "Aguarde"
                listEl.innerHTML = `
                <div class="bg-white rounded-[2rem] p-8 mt-4 mb-4 shadow-sm border border-gray-100 flex flex-col items-center text-center">
                    <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/Hourglass%20Not%20Done.png" alt="Ampulheta" class="w-16 h-16 mb-4 drop-shadow-sm filter">
                    <h3 class="text-xl font-black text-[#001b33] mb-2 tracking-tight">Aguarde, novas rifas em breve.</h3>
                    <p class="text-sm text-gray-500 font-medium">Estamos preparando novidades incríveis para você. Fique ligado!</p>
                </div>
                `;
            }

            // Renderiza Finalizadas
            if (data.finalizadas && data.finalizadas.length > 0) {
                finalizadasSection.classList.remove('hidden');
                data.finalizadas.forEach(rifa => renderCard(rifa, finalizadasListEl, true));
            }

            // Aplicar link de suporte no menu
            if(data.link_suporte) {
                const navAjuda = document.getElementById('link-ajuda-nav');
                if(navAjuda) {
                    navAjuda.href = data.link_suporte;
                    navAjuda.target = "_blank";
                }
            }
        }

        // Fetch Ganhadores
        const resG = await fetch('backend/api/get_publicacoes.php?limit=2');
        const dataG = await resG.json();
        if(dataG.data && dataG.data.length > 0) {
            const gSection = document.getElementById('ganhadores-section');
            const gContainer = document.getElementById('ganhadores-container');
            
            let html = '';
            dataG.data.forEach(p => {
                const imgUrl = p.imagem_url ? p.imagem_url : 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQcbkK3z3Q93lZ3q71_gK_3y313hT38qf7VjA&usqp=CAU';
                html += `
                    <a href="ganhadores.html" class="flex items-center gap-4 bg-white rounded-[2rem] p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow group overflow-hidden">
                        <img src="${imgUrl}" alt="Ganhador" class="w-16 h-16 rounded-full object-cover border-2 border-[#00a650] shadow transform transition-transform duration-300 group-hover:scale-125 z-10 origin-center relative">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800">${p.nome_ganhador} <span class="bg-yellow-100 text-[#2c3e50] font-black text-[10px] uppercase px-2 py-0.5 rounded-full ml-1 whitespace-nowrap">${p.numero_premiado}</span></h3>
                            <p class="text-xs text-gray-500 font-medium line-clamp-2 mt-0.5">${p.premio_descricao}</p>
                        </div>
                    </a>
                `;
            });
            
            gContainer.innerHTML = html;
            gSection.classList.remove('hidden');
        }

    } catch(err) {
        listEl.innerHTML = '<div class="p-4 bg-red-50 text-red-500 rounded-xl text-center text-sm font-bold">Erro ao carregar sorteios.</div>';
        console.error(err);
    }

    function renderCard(rifa, container, isFinalized) {
        const perc = rifa.percentual || 0;
        
        const priceNum = parseFloat(rifa.preco_numero);
        const strReais = priceNum.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        const [reais, centavos] = strReais.split(',');

        let colorPulse = isFinalized ? 'bg-gray-600' : (rifa.tag.includes('AO VIVO') ? 'animate-pulse bg-red-600' : 'bg-blue-600');
        let cardLink = isFinalized ? '#' : `rifa.html?id=${rifa.id}`;
        let pointerClass = isFinalized ? 'cursor-default' : 'cursor-pointer active:scale-95 group hover:shadow-2xl';

        let buttonArea = isFinalized ? `
            <div class="bg-gray-800 text-white font-black py-4 rounded-xl shadow-lg w-full text-sm uppercase tracking-widest flex justify-center items-center gap-2 relative overflow-hidden ring-2 ring-white/20">
                <span class="relative z-10 flex items-center gap-1">Rifa Encerrada</span>
            </div>
        ` : `
            <div class="bg-white text-[#e74c3c] rounded-xl py-1 px-3 shadow inline-flex items-end justify-center gap-0.5 -rotate-3 mb-4 w-max border-2 border-red-100 filter drop-shadow-md">
                <span class="text-[10px] font-black pb-1 mr-1">R$</span>
                <span class="text-3xl font-black tracking-tighter">${reais}</span>
                <span class="text-sm font-black pb-1">,${centavos}</span>
            </div>
            <button class="bg-[#00a650] text-white font-black py-4 rounded-xl shadow-lg w-full text-sm uppercase tracking-widest flex justify-center items-center gap-2 relative overflow-hidden active:bg-[#009647]">
                <span class="relative z-10 flex items-center gap-1">
                    Garanta Já o Seu!
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </span>
            </button>
        `;

        let progress = isFinalized ? '' : `
            <div class="w-full bg-black/40 rounded-full h-1.5 mt-5 border border-white/20 overflow-hidden shadow-inner relative">
                <div class="bg-gradient-to-r from-yellow-400 to-[#f1c40f] h-full rounded-full transition-all duration-1000 ease-out" style="width: ${perc}%"></div>
            </div>
            <p class="text-center text-[10px] text-gray-300 mt-1.5 font-bold tracking-wide">${perc}% VENDIDO</p>
        `;

        const cardHTML = `
        <a href="${cardLink}" class="block relative w-full h-[28rem] rounded-[2rem] overflow-hidden shadow-xl shadow-gray-200 bg-black border-4 border-white transition-transform ${pointerClass}">
            <img src="${rifa.imagem_url}" class="absolute inset-0 w-full h-full object-cover opacity-70 ${isFinalized ? 'grayscale' : 'group-hover:opacity-100 transition-opacity duration-300'}" alt="${rifa.nome}">
            <div class="absolute inset-0 card-image-gradient"></div>
            
            <!-- Top Tags -->
            <div class="absolute top-4 left-4 z-10 flex flex-col items-start gap-2">
                <span class="${colorPulse} text-white justify-center text-[10px] font-black uppercase px-3 py-1.5 rounded-full shadow tracking-wider flex items-center gap-1">
                    ${rifa.tag}
                </span>
            </div>
        
            ${!isFinalized ? `
            <div class="absolute top-1/3 left-1/2 -translate-x-1/2 z-10 opacity-80 justify-center gap-1 filter drop-shadow hidden group-hover:flex">
                <div class="bg-white/90 text-[#e74c3c] font-black rounded-lg px-4 py-2 flex items-center shadow-lg -rotate-12 transform scale-125">
                    <span class="text-3xl">+</span> <span class="text-xs ml-1 uppercase leading-tight font-black">Chances<br>de ganhar</span>
                </div>
            </div>` : ''}

            <!-- Content Bottom -->
            <div class="absolute bottom-6 left-0 right-0 px-6 z-10 flex flex-col">
                <ul class="mb-3 text-[#f1c40f] text-sm md:text-base font-black uppercase tracking-wide space-y-1 filter drop-shadow-lg text-shadow">
                    ${rifa.premio1 ? `<li>🏆 1º ${rifa.premio1}</li>` : ''}
                    ${rifa.premio2 ? `<li>🥈 2º ${rifa.premio2}</li>` : ''}
                    ${rifa.premio3 ? `<li>🥉 3º ${rifa.premio3}</li>` : ''}
                    ${rifa.premio4 ? `<li>🎖️ 4º ${rifa.premio4}</li>` : ''}
                    ${rifa.premio5 ? `<li>🏅 5º ${rifa.premio5}</li>` : ''}
                </ul>
                
                <h3 class="text-white text-3xl md:text-4xl font-black mb-4 text-shadow leading-[1.1] uppercase tracking-tighter">#${rifa.id}</h3>
                
                ${buttonArea}
                ${progress}
            </div>
        </a>
        `;
        
        container.insertAdjacentHTML('beforeend', cardHTML);
    }
});
