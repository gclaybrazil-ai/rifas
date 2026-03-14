const API_URL = 'backend/api';

let state = {
    numeros: [], // Do servidor
    selecionados: new Set(),
    preco: 0,
    reserva: null,
    pollingTimer: null,
    paymentPollingTimer: null,
    countdownTimer: null,
    groupVip: '',
    repassarTaxa: false
};

// DOM Elements
const els = {
    grid: document.getElementById('grid-container'),
    selectedCount: document.getElementById('selected-count'),
    selectedTotal: document.getElementById('selected-total'),
    bottomBar: document.getElementById('bottom-bar'),
    
    overlay: document.getElementById('modal-overlay'),
    
    // Reserve Modal
    modalReserve: document.getElementById('modal-reserve'),
    btnOpenReserve: document.getElementById('btn-open-reserve-modal'),
    btnCloseReserve: document.getElementById('btn-close-reserve-modal'),
    btnSubmitReservation: document.getElementById('btn-submit-reservation'),
    inputName: document.getElementById('input-name'),
    inputWhatsapp: document.getElementById('input-whatsapp'),
    modalSelectedNums: document.getElementById('modal-selected-nums'),
    modalTotalValue: document.getElementById('modal-total-value'),
    
    // PIX Modal
    modalPix: document.getElementById('modal-pix'),
    pixQr: document.getElementById('pix-qrcode-img'),
    pixCopiaCola: document.getElementById('pix-copiacola-input'),
    btnCopyPix: document.getElementById('btn-copy-pix'),
    countdown: document.getElementById('countdown'),

    // Success Modal
    modalSuccess: document.getElementById('modal-success'),
    btnCloseSuccess: document.getElementById('btn-close-success')
};

async function fetchRifa() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const rifaId = urlParams.get('id') || '';
        const res = await fetch(`${API_URL}/get_rifa.php?id=${rifaId}`);
        const data = await res.json();
        
        if (data.error) throw new Error(data.error);
        
        state.preco = parseFloat(data.rifa.preco_numero);
        
        if(data.rifa.sorteio_por) {
            const badge = document.getElementById('badge-sorteio');
            if(badge) badge.textContent = `SORTEIO POR ${data.rifa.sorteio_por.toUpperCase()}`;
        }

        if(data.group_vip) {
            state.groupVip = data.group_vip;
        }
        
        state.repassarTaxa = data.repassar_taxa === '1';
        
        updateGrid(data.numeros);
        updateBottomBar();
        
    } catch (err) {
        console.error("Erro ao carregar rifa:", err);
    }
}

function updateGrid(numerosDoServidor) {
    if (els.grid.innerHTML.includes('Carregando')) {
        els.grid.innerHTML = '';
    }

    // Se é a primeira vez, cria os botões, se não, apenas atualiza classes
    const forceCreate = els.grid.children.length === 0;

    numerosDoServidor.forEach(num => {
        let btn;
        if (forceCreate) {
            btn = document.createElement('div');
            btn.className = 'num-btn shadow-sm font-black transition-all min-w-0 relative group hover:z-[60]';
            btn.dataset.num = num.numero;
            
            // Interaction
            btn.addEventListener('click', () => toggleSelection(num.numero, num.status));
            els.grid.appendChild(btn);
        } else {
            // Get existing
            btn = els.grid.querySelector(`[data-num="${num.numero}"]`);
        }

        if(!btn) return;

        // Limpa classes anteriores
        btn.classList.remove('disponivel', 'reservado', 'pago', 'selecionado', 'flex-col');

        let contentHtml = `<span class="leading-none">${num.numero}</span>`;
        if (num.status === 'pago' && num.comprador) {
            const primeiroNomeCompleto = num.comprador.split(' ')[0].toUpperCase();
            const primeiroNomeCurto = primeiroNomeCompleto.substring(0, 8);
            contentHtml += `<span class="text-[8px] sm:text-[9px] font-bold mt-1 opacity-90 overflow-hidden text-ellipsis whitespace-nowrap w-full px-0.5 text-center leading-none tracking-tighter uppercase block">${primeiroNomeCurto}</span>`;
            
            // Efeito Lupa (Tooltip)
            contentHtml += `
            <div class="absolute -top-12 left-1/2 transform -translate-x-1/2 bg-[#2c3e50] text-white px-4 py-2 rounded-xl shadow-2xl opacity-0 group-hover:opacity-100 pointer-events-none scale-50 group-hover:scale-110 transition-all duration-300 flex flex-col items-center">
                <span class="text-[9px] text-gray-300 font-bold uppercase mb-0.5 tracking-wider leading-none whitespace-nowrap">Número ${num.numero}</span>
                <span class="font-black text-[13px] whitespace-nowrap leading-none">${primeiroNomeCompleto}</span>
                <div class="absolute -bottom-1.5 left-1/2 transform -translate-x-1/2 w-3 h-3 bg-[#2c3e50] rotate-45 rounded-sm"></div>
            </div>`;
            
            btn.classList.add('flex-col');
        }
        
        btn.innerHTML = contentHtml;

        // Se o número foi selecionado pelo USUARIO ATUAL localmente, e continua disponível no server
        if (state.selecionados.has(num.numero) && num.status === 'disponivel') {
            btn.classList.add('selecionado');
        } else {
            // Se o servidor diz que não tá disponível, tira localmente
            if(state.selecionados.has(num.numero) && num.status !== 'disponivel') {
                state.selecionados.delete(num.numero);
                updateBottomBar();
            }
            btn.classList.add(num.status); // 'disponivel', 'reservado', 'pago'
        }
    });
}

function toggleSelection(numero, serverStatus) {
    // Só pode selecionar se estiver disponível
    if (serverStatus !== 'disponivel' && !state.selecionados.has(numero)) {
        return; // Não faz nada se clicou em um reservado/pago
    }

    if (state.selecionados.has(numero)) {
        state.selecionados.delete(numero);
    } else {
        if(state.selecionados.size >= 20) {
            alert('Você pode selecionar no máximo 20 números.');
            return;
        }
        state.selecionados.add(numero);
    }
    
    // Atualiza apenas botão visualmente para resposta imediata
    const btn = els.grid.querySelector(`[data-num="${numero}"]`);
    if(btn) {
        if(state.selecionados.has(numero)) {
            btn.classList.remove('disponivel');
            btn.classList.add('selecionado');
        } else {
            btn.classList.remove('selecionado');
            btn.classList.add('disponivel');
        }
    }
    
    updateBottomBar();
}

function updateBottomBar() {
    const qtde = state.selecionados.size;
    els.selectedCount.textContent = qtde;
    
    
    let total = (qtde * state.preco);
    let taxaHtml = '';
    
    if (state.repassarTaxa) {
        let originalTotal = total;
        // Cálculo para cobrir 1.19% da Efí
        total = originalTotal / (1 - 0.0119);
        let valorDaTaxa = total - originalTotal;
        
        taxaHtml = `<span class="text-[9px] block opacity-80">+ Taxa Transação: ${valorDaTaxa.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'})}</span>`;
    }

    els.selectedTotal.innerHTML = `<div>Total: ${total.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'})}${taxaHtml}</div>`;

    if (qtde > 0) {
        els.bottomBar.classList.remove('translate-y-full');
    } else {
        els.bottomBar.classList.add('translate-y-full');
    }
}


/* --- MODALS LOGIC --- */
function openModal(modalEl) {
    els.overlay.classList.remove('hidden');
    modalEl.classList.remove('hidden');
    
    // trigger animation
    setTimeout(() => {
        els.overlay.classList.remove('opacity-0');
        modalEl.classList.add('show');
    }, 10);
}

function hideModals() {
    els.overlay.classList.add('opacity-0');
    document.querySelectorAll('.modal-box').forEach(m => m.classList.remove('show'));
    
    setTimeout(() => {
        els.overlay.classList.add('hidden');
        document.querySelectorAll('.modal-box').forEach(m => m.classList.add('hidden'));
    }, 300);
}

els.btnOpenReserve.addEventListener('click', () => {
    // Fill modal info
    els.modalSelectedNums.innerHTML = '';
    const arr = Array.from(state.selecionados).sort();
    arr.forEach(n => {
        const span = document.createElement('span');
        span.className = 'modal-chip';
        span.textContent = n;
        els.modalSelectedNums.appendChild(span);
    });
    
    let total = state.selecionados.size * state.preco;
    if (state.repassarTaxa) {
        let originalTotal = total;
        total = originalTotal / (1 - 0.0119);
    }
    els.modalTotalValue.textContent = total.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
    
    openModal(els.modalReserve);
});

els.btnCloseReserve.addEventListener('click', () => {
    hideModals();
});

// WHATSAPP MASK
els.inputWhatsapp.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11); // Max 11 digits

    if (value.length > 2) {
        value = `(${value.slice(0, 2)}) ` + value.slice(2);
    }
    // Applies dash correctly depending on 10 or 11 digits
    if (value.length > 10) { 
        value = value.slice(0, 10) + '-' + value.slice(10);
    } else if (value.length > 9) { 
        value = value.slice(0, 9) + '-' + value.slice(9);
    }
    
    e.target.value = value;
});

// RESERVAR E ABRIR PIX
els.btnSubmitReservation.addEventListener('click', async () => {
    const nome = els.inputName.value.trim();
    const whatsapp = els.inputWhatsapp.value.trim();
    const arr = Array.from(state.selecionados);

    if(!nome || !whatsapp) {
        alert('Preencha seu nome e whatsapp!');
        return;
    }

    const partesNome = nome.split(/\s+/);
    if(partesNome.length < 2 || partesNome[1].length < 2) {
        alert('Por favor, informe seu NOME e SOBRENOME para continuar.');
        return;
    }
    
    // Exigir DDD + Número válido (10 ou 11 digitos totais sem mascara)
    const whatsNum = whatsapp.replace(/\D/g, '');
    if(whatsNum.length < 10) {
        alert('Por favor, informe um WhatsApp válido com DDD.');
        return;
    }

    if(arr.length === 0) {
        alert('Selecione ao menos um número.');
        hideModals();
        return;
    }

    els.btnSubmitReservation.innerHTML = 'Aguarde...';
    els.btnSubmitReservation.disabled = true;

    try {
        const urlParams = new URLSearchParams(window.location.search);
        const rifaId = urlParams.get('id') || '';

        const res = await fetch(`${API_URL}/reserve.php`, {
            method: 'POST',
            body: JSON.stringify({
                rifa_id: rifaId,
                nome,
                whatsapp,
                numeros: arr
            }),
            headers: {'Content-Type': 'application/json'}
        });
        
        const data = await res.json();
        if(data.error) {
            alert(data.error);
            els.btnSubmitReservation.innerHTML = 'Prosseguir para Pagamento';
            els.btnSubmitReservation.disabled = false;
            fetchRifa(); // Refresh to see taken numbers
            hideModals();
            return;
        }

        // Sucesso
        state.reserva = data;
        
        // Limpa grid e botões (pois agora viraram 'reservado' do server)
        state.selecionados.clear();
        updateBottomBar();
        
        // Setup PIX
        els.pixQr.src = data.pix_qrcode;
        els.pixCopiaCola.value = data.pix_copiacola;
        
        hideModals();
        setTimeout(() => openModal(els.modalPix), 350);
        
        // Iniciar timer
        startCountdown(data.expire_in);
        // Iniciar Polling PIX
        startPaymentPolling(data.reserva_id);

    } catch(err) {
        console.error(err);
        alert('Erro ao comunicar com o servidor.');
        els.btnSubmitReservation.innerHTML = 'Prosseguir para Pagamento';
        els.btnSubmitReservation.disabled = false;
    }
});


els.btnCopyPix.addEventListener('click', () => {
    els.pixCopiaCola.select();
    document.execCommand('copy');
    const originalText = els.btnCopyPix.innerHTML;
    els.btnCopyPix.innerHTML = '✓ Copiado!';
    setTimeout(() => {
        els.btnCopyPix.innerHTML = originalText;
    }, 2000);
});

/* --- TIMERS E POLLING --- */

function startCountdown(seconds) {
    if(state.countdownTimer) clearInterval(state.countdownTimer);
    
    let time = seconds;
    els.countdown.textContent = formatTime(time);
    
    state.countdownTimer = setInterval(() => {
        time--;
        if(time <= 0) {
            clearInterval(state.countdownTimer);
            clearInterval(state.paymentPollingTimer);
            els.countdown.textContent = '00:00';
            hideModals();
            setTimeout(() => openModal(document.getElementById('modal-expired')), 350);
            fetchRifa(); // update grid immediately
        } else {
            els.countdown.textContent = formatTime(time);
        }
    }, 1000);
}

function formatTime(s) {
    const m = Math.floor(s / 60);
    const secs = s % 60;
    return `${m.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

function startPaymentPolling(reserva_id) {
    if(state.paymentPollingTimer) clearInterval(state.paymentPollingTimer);
    
    state.paymentPollingTimer = setInterval(async () => {
        try {
            const res = await fetch(`${API_URL}/check_payment.php?id=${reserva_id}`);
            const data = await res.json();
            
            if(data.status === 'pago') {
                // Pago!
                clearInterval(state.paymentPollingTimer);
                clearInterval(state.countdownTimer);
                fetchRifa(); // Get purple buttons
                
                hideModals();
                
                // VIP Button logic
                const btnVip = document.getElementById('btn-group-vip');
                if(btnVip && state.groupVip) {
                    btnVip.href = state.groupVip;
                    btnVip.classList.remove('hidden');
                    btnVip.classList.add('flex');
                }

                setTimeout(() => openModal(els.modalSuccess), 350);
            } else if (data.status === 'expirado') {
                clearInterval(state.paymentPollingTimer);
                clearInterval(state.countdownTimer);
                hideModals();
                setTimeout(() => openModal(document.getElementById('modal-expired')), 350);
                fetchRifa();
            }
        } catch(e) {
            console.log(e);
        }
    }, 3000); // Check every 3 seconds
}

els.btnCloseSuccess.addEventListener('click', () => {
    hideModals();
});

// START
fetchRifa();
// Smart polling background: every 5 seconds check grid updates
setInterval(fetchRifa, 5000);
