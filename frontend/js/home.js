document.addEventListener('DOMContentLoaded', async () => {
    const listEl = document.getElementById('campaign-list');
    const finalizadasListEl = document.getElementById('finalizadas-list');
    const finalizadasSection = document.getElementById('rifas-finalizadas');

    try {
        const urlParams = new URLSearchParams(window.location.search);
        // Capturar ou Limpar referência de afiliado
        const ref = urlParams.get('ref');
        if (ref) {
            localStorage.setItem('rifa_ref', ref);
        } else {
            // Se entrou sem link de indicação, remove a memória do afiliado anterior para compras "limpas"
            localStorage.removeItem('rifa_ref');
        }

        const res = await fetch('backend/api/get_rifas.php');
        const data = await res.json();

        if (data.maintenance) {
            window.location.href = 'manutencao.php';
            return;
        }

        if (data.success) {
            listEl.innerHTML = ''; // Limpa loader

            // Renderiza as Ativas
            if (data.ativas && data.ativas.length > 0) {
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
            if (data.link_suporte) {
                const navAjuda = document.getElementById('link-ajuda-nav');
                if (navAjuda) {
                    navAjuda.href = data.link_suporte;
                    navAjuda.target = "_blank";
                }
            }

            // Exibir Popup se Ativo
            if (data.popup) {
                const modal = document.getElementById('modal-popup');
                const pTitle = document.getElementById('popup-title');
                const pContent = document.getElementById('popup-content');
                const pBtn = document.getElementById('btn-close-popup');
                const pMedia = document.getElementById('popup-media-container');
                const pImg = document.getElementById('popup-image');
                const pVideo = document.getElementById('popup-video-container');
                const pIframe = document.getElementById('popup-video-iframe');

                pTitle.innerText = data.popup.title;
                pContent.innerHTML = data.popup.content;
                pBtn.innerText = data.popup.button;

                // Handle Media
                let hasMedia = false;
                if (data.popup.video) {
                    pIframe.src = data.popup.video;
                    pVideo.classList.remove('hidden');
                    pImg.classList.add('hidden');
                    hasMedia = true;
                } else if (data.popup.image) {
                    pImg.src = data.popup.image;
                    pImg.style.objectFit = data.popup.image_fit || 'cover';
                    pImg.style.objectPosition = data.popup.image_position || 'center';
                    pImg.classList.remove('hidden');
                    pVideo.classList.add('hidden');
                    hasMedia = true;
                }

                if (hasMedia) pMedia.classList.remove('hidden');

                // Show Modal
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.add('opacity-100');
                    modal.querySelector('div').classList.remove('scale-95');
                }, 100);

                pBtn.onclick = () => {
                    const link = data.popup.link;
                    modal.classList.remove('opacity-100');
                    modal.querySelector('div').classList.add('scale-95');

                    // STOP VIDEO
                    pIframe.src = '';

                    setTimeout(() => {
                        modal.classList.add('hidden');
                        if (link) window.location.href = link;
                    }, 500);
                };
            }
        }

        // --- Fetch Ganhadores (Isolated to prevent breaking the main list) ---
        try {
            const resG = await fetch('backend/api/get_publicacoes.php?limit=2');
            const dataG = await resG.json();
            if (dataG.data && dataG.data.length > 0) {
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

                if (gContainer && gSection) {
                    gContainer.innerHTML = html;
                    gSection.classList.remove('hidden');
                }
            }
        } catch (eG) {
            console.warn("Ganhadores error:", eG);
        }

    } catch (err) {
        listEl.innerHTML = '<div class="p-4 bg-red-50 text-red-500 rounded-xl text-center text-sm font-bold">Erro ao carregar sorteios.</div>';
        console.error(err);
    }
    function renderCard(rifa, container, isFinalized) {
        const priceNum = parseFloat(rifa.preco_numero);
        const strPrice = priceNum.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

        let cardLink = isFinalized ? '#' : `rifa.php?id=${rifa.id}`;
        let pointerClass = isFinalized ? 'cursor-default opacity-80' : 'cursor-pointer active:scale-95 hover:shadow-xl';

        // Badge Logic Simple for Active, Gray for Finalized
        let cleanTag = rifa.tag ? rifa.tag.replace(/[^\w\s]/gi, '').trim() : '';
        let isLive = !isFinalized && cleanTag.toLowerCase().includes('ao vivo');
        let badgeClass = isFinalized ? 'bg-gray-600' : (isLive ? 'bg-blue-600 animate-pulse' : 'bg-blue-600');
        let badgeIcon = isLive ? '🔥 ' : '';

        let statusBadge = rifa.tag ? `
            <div class="absolute top-3 left-3 z-10">
                <span class="${badgeClass} text-white text-[10px] font-black uppercase px-3 py-1.5 rounded-full shadow tracking-wider flex items-center gap-1">
                    ${badgeIcon}${cleanTag}
                </span>
            </div>
        ` : '';

        let buttonArea = isFinalized ? `
            <div class="w-full bg-[#1e293b] text-white font-black py-4 rounded-2xl shadow-lg flex justify-center items-center gap-2 text-xs uppercase tracking-widest opacity-90">
                Rifa Encerrada
            </div>
        ` : `
            <button class="w-full bg-[#00a650] text-white font-black py-4 rounded-2xl shadow-lg flex justify-center items-center gap-2 text-xs uppercase tracking-widest hover:bg-[#009647] transition-all active:scale-[0.98]">
                <span class="flex items-center gap-1">
                    Garanta Já o Seu!
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </span>
            </button>
        `;

        const cardHTML = `
        <div onclick="window.location.href='${cardLink}'" class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm border border-gray-100 p-3 transition-all ${pointerClass}">
            <div class="relative aspect-square rounded-[2rem] overflow-hidden mb-5">
                <img src="${rifa.imagem_url}" class="w-full h-full object-cover ${isFinalized ? 'grayscale opacity-60' : ''}" alt="${rifa.nome}">
                ${isFinalized ? '<div class="absolute inset-0 card-image-gradient"></div>' : ''}
                ${statusBadge}
                
                <div class="absolute bottom-3 right-3 bg-[#00a650] text-white text-[15px] font-black px-3 py-1.5 rounded-lg shadow-md z-10">
                    #${rifa.id}
                </div>
            </div>

            <div class="px-2 pb-3 text-center">
                <h3 class="text-[#001b33] text-lg font-black uppercase tracking-tight mb-1 truncate px-2">${rifa.nome}</h3>
                <p class="text-blue-600 text-sm font-bold mb-5">${strPrice} por número</p>
                
                ${buttonArea}
            </div>
        </div>
        `;

        container.insertAdjacentHTML('beforeend', cardHTML);
    }

    // --- Lógica PWA (Suggest Install) ---
    let deferredPrompt;
    const pwaModal = document.getElementById('modal-pwa');
    const btnAndroid = document.getElementById('btn-install-android');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (!window.matchMedia('(display-mode: standalone)').matches && !localStorage.getItem('pwa_dismissed')) {
            showPWAModal('android');
        }
    });

    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isStandalone = window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches;

    if (isIOS && !isStandalone && !localStorage.getItem('pwa_dismissed')) {
        showPWAModal('ios');
    }

    function showPWAModal(platform) {
        setTimeout(() => {
            if (!pwaModal) return;
            pwaModal.classList.remove('hidden');
            const platEl = document.getElementById(`pwa-${platform}`);
            if (platEl) platEl.classList.remove('hidden');
            
            setTimeout(() => {
                pwaModal.classList.add('flex');
                pwaModal.classList.add('opacity-100');
                pwaModal.querySelector('div').classList.remove('scale-95');
            }, 50);
        }, 4000); 
    }

    const btnClosePwa = document.getElementById('btn-close-pwa');
    if (btnClosePwa) {
        btnClosePwa.onclick = () => {
            closePWAModal();
            localStorage.setItem('pwa_dismissed', 'true');
        };
    }

    if (pwaModal) {
        pwaModal.onclick = (e) => {
            if (e.target === pwaModal) closePWAModal();
        };
    }

    function closePWAModal() {
        if (!pwaModal) return;
        pwaModal.classList.remove('opacity-100');
        pwaModal.querySelector('div').classList.add('scale-95');
        setTimeout(() => {
            pwaModal.classList.add('hidden');
            pwaModal.classList.remove('flex');
        }, 500);
    }

    if (btnAndroid) {
        btnAndroid.onclick = async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') localStorage.setItem('pwa_dismissed', 'true');
                deferredPrompt = null;
                closePWAModal();
            }
        };
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('sw.js').catch(err => console.log("SW error", err));
        });
    }

    // --- Cookie Consent Logic ---
    const cookieBanner = document.getElementById('cookie-banner');
    const btnAccept = document.getElementById('btn-accept-cookies');

    if (cookieBanner && !localStorage.getItem('cookies_accepted')) {
        setTimeout(() => {
            cookieBanner.classList.remove('hidden');
            setTimeout(() => {
                cookieBanner.classList.remove('translate-y-20');
            }, 100);
        }, 2000);
    }

    if (btnAccept) {
        btnAccept.onclick = () => {
            cookieBanner.classList.add('translate-y-20');
            localStorage.setItem('cookies_accepted', 'true');
            setTimeout(() => {
                cookieBanner.classList.add('hidden');
            }, 700);
        };
    }
});

function showDevInfo() {
    const modal = document.getElementById('modal-popup');
    const pTitle = document.getElementById('popup-title');
    const pContent = document.getElementById('popup-content');
    const pBtn = document.getElementById('btn-close-popup');
    const pMedia = document.getElementById('popup-media-container');

    pTitle.innerText = 'Em breve!';
    pContent.innerHTML = 'Esta funcionalidade está em desenvolvimento e será liberada em breve. Fique ligado!';
    pBtn.innerText = 'Entendido';
    pMedia.classList.add('hidden');

    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('opacity-100');
        modal.querySelector('div').classList.remove('scale-95');
    }, 100);

    pBtn.onclick = () => {
        modal.classList.remove('opacity-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 500);
    };
}
window.showDevInfo = showDevInfo;
