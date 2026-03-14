const API_URL = 'backend/api';

let state = {
    numeros: [], // Do servidor
    selecionados: new Set(),
    preco: 0,
    reserva: null,
    pollingTimer: null,
    paymentPollingTimer: null,
    countdownTimer: null
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
            btn.className = 'num-btn shadow-sm font-black transition-all';
            btn.dataset.num = num.numero;
            btn.textContent = num.numero;
            
            // Interaction
            btn.addEventListener('click', () => toggleSelection(num.numero, num.status));
            els.grid.appendChild(btn);
        } else {
            // Get existing
            btn = els.grid.querySelector(`[data-num="${num.numero}"]`);
        }

        if(!btn) return;

        // Limpa classes anteriores
        btn.classList.remove('disponivel', 'reservado', 'pago', 'selecionado');

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
    
    const total = (qtde * state.preco);
    els.selectedTotal.textContent = `Total: ${total.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'})}`;

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
    
    const total = state.selecionados.size * state.preco;
    els.modalTotalValue.textContent = total.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
    
    openModal(els.modalReserve);
});

els.btnCloseReserve.addEventListener('click', () => {
    hideModals();
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
            alert('Tempo expirado. Seus números retornaram para disponíveis.');
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
                setTimeout(() => openModal(els.modalSuccess), 350);
            } else if (data.status === 'expirado') {
                clearInterval(state.paymentPollingTimer);
                clearInterval(state.countdownTimer);
                hideModals();
                alert('Sua reserva expirou.');
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
