document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    if(!id) {
        alert("ID de Reserva Inválido");
        window.location.href = 'index.php';
        return;
    }

    const loader = document.getElementById('checkout-loader');
    const view = document.getElementById('checkout-view');
    const success = document.getElementById('checkout-success');

    try {
        const res = await fetch('backend/api/get_reserva.php?id=' + encodeURIComponent(id));
        const json = await res.json();

        loader.classList.add('hidden');

        if(json.success) {
            const data = json.data;
            
            if(data.status === 'pago') {
                document.getElementById('txt-sucesso-numeros').innerText = `Seus números: ${data.numeros}`;
                
                if(data.group_vip) {
                    const btnVip = document.getElementById('btn-group-vip-checkout');
                    if(btnVip) {
                        btnVip.href = data.group_vip;
                        btnVip.classList.remove('hidden');
                        btnVip.classList.add('flex');
                    }
                }

                success.classList.remove('hidden');
                success.classList.add('flex');
            } else if(data.status === 'expirado') {
                const modalExp = document.getElementById('modal-expired');
                modalExp.classList.remove('hidden');
                setTimeout(() => {
                    modalExp.classList.remove('opacity-0');
                    modalExp.querySelector('div').classList.remove('scale-95');
                    modalExp.querySelector('div').classList.add('scale-100');
                }, 10);
            } else {
                // Pendente -> Show Pix Data
                document.getElementById('lbl-reserva').innerText = '#' + data.id;
                
                const val = parseFloat(data.valor_total).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                
                document.getElementById('rifa-details').innerHTML = `
                    <div class="flex-1">
                        <h4 class="font-black text-[#2c3e50] text-sm">${data.nome}</h4>
                        <p class="text-xs text-gray-500 font-bold mb-1">Rifa #${data.rifa_id}</p>
                        <p class="text-xs text-[#00a650] font-black underline mb-2 tracking-wide">${val}</p>
                        <p class="text-[10px] sm:text-xs font-bold bg-white text-gray-500 border border-gray-200 px-2 py-1 rounded inline-block mb-1">Nros: ${data.numeros}</p>
                        <p class="text-[10px] text-gray-400 italic block leading-tight">Dica: Se você não preencheu os dados do seu cartão a tempo, pague o Pix gerado abaixo para não perder sua reserva!</p>
                    </div>
                `;

                // Set PIX data
                document.getElementById('pix-qrcode').src = data.pix_qrcode ? data.pix_qrcode : 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRz-INg-kP4DPR6m1zC8Prt8e2P3x7wG152tA&usqp=CAU';
                document.getElementById('pix-copiacola').value = data.pix_copiacola ? data.pix_copiacola : 'Chave não gerada/Encontrada';

                view.classList.remove('hidden');
                view.classList.add('flex');

                // Countdown Timer Logic
                let timeRemaining = parseInt(data.remaining_seconds);
                const countDisplay = document.getElementById('checkout-countdown');
                
                if (timeRemaining > 0) {
                    const countTimer = setInterval(() => {
                        if(timeRemaining <= 0) {
                            clearInterval(countTimer);
                            countDisplay.innerText = '00:00';
                            
                            const modalExp = document.getElementById('modal-expired');
                            modalExp.classList.remove('hidden');
                            
                            // trigger animation
                            setTimeout(() => {
                                modalExp.classList.remove('opacity-0');
                                modalExp.querySelector('div').classList.remove('scale-95');
                                modalExp.querySelector('div').classList.add('scale-100');
                            }, 10);
                            
                            return;
                        }
                        timeRemaining--;
                        let m = Math.floor(timeRemaining / 60).toString().padStart(2, '0');
                        let s = (timeRemaining % 60).toString().padStart(2, '0');
                        countDisplay.innerText = `${m}:${s}`;
                    }, 1000);
                } else {
                    countDisplay.innerText = '00:00';
                }

                // Polling Check Payment
                setInterval(() => {
                    fetch('backend/api/check_payment.php?id=' + id)
                        .then(res => res.json())
                        .then(r => {
                            if(r.status === 'pago') {
                                view.classList.add('hidden');
                                view.classList.remove('flex');
                                document.getElementById('txt-sucesso-numeros').innerText = `O pagamento da reserva #${id} foi efetivado com sucesso. Boa sorte!`;
                                
                                if(r.group_vip) {
                                    const btnVip = document.getElementById('btn-group-vip-checkout');
                                    if(btnVip) {
                                        btnVip.href = r.group_vip;
                                        btnVip.classList.remove('hidden');
                                        btnVip.classList.add('flex');
                                    }
                                }

                                success.classList.remove('hidden');
                                success.classList.add('flex');
                            } else if (r.status === 'expirado') {
                                view.classList.add('hidden');
                                view.classList.remove('flex');
                                const modalExp = document.getElementById('modal-expired');
                                modalExp.classList.remove('hidden');
                                setTimeout(() => {
                                    modalExp.classList.remove('opacity-0');
                                    modalExp.querySelector('div').classList.remove('scale-95');
                                    modalExp.querySelector('div').classList.add('scale-100');
                                }, 10);
                            }
                        });
                }, 3000);

                // --- CARD INITIALIZATION ---
                if (data.card_active === '1' && data.gateway === 'mercadopago' && data.mp_public_key) {
                    const tabs = document.getElementById('payment-tabs');
                    const tabPix = document.getElementById('tab-pix');
                    const tabCard = document.getElementById('tab-card');
                    const contentPix = document.getElementById('content-pix');
                    const contentCard = document.getElementById('content-card');

                    tabs.classList.remove('hidden');

                    const setTab = (type) => {
                        if (type === 'pix') {
                            tabPix.className = 'flex-1 bg-[#00a650] text-white py-2 rounded-lg font-bold text-xs shadow-sm uppercase transition-all';
                            tabCard.className = 'flex-1 bg-gray-100 text-gray-400 py-2 rounded-lg font-bold text-xs border border-gray-200 uppercase transition-all hover:bg-gray-200';
                            contentPix.classList.remove('hidden');
                            contentCard.classList.add('hidden');
                        } else {
                            tabCard.className = 'flex-1 bg-indigo-600 text-white py-2 rounded-lg font-bold text-xs shadow-sm uppercase transition-all';
                            tabPix.className = 'flex-1 bg-gray-100 text-gray-400 py-2 rounded-lg font-bold text-xs border border-gray-200 uppercase transition-all hover:bg-gray-200';
                            contentCard.classList.remove('hidden');
                            contentPix.classList.add('hidden');
                        }
                    };

                    tabPix.addEventListener('click', () => setTab('pix'));
                    tabCard.addEventListener('click', () => setTab('card'));

                    const preferMethod = localStorage.getItem(`checkout_method_${id}`) || 'pix';
                    setTab(preferMethod);

                    // Initialize MP
                    const mp = new MercadoPago(data.mp_public_key);
                    const bricksBuilder = mp.bricks();
                    
                    const renderCardBrick = async () => {
                        const settings = {
                            initialization: { amount: Number(data.valor_total) },
                            customization: { visual: { style: { theme: 'default' } } },
                            callbacks: {
                                onReady: () => console.log('Checkout Card Ready'),
                                onSubmit: (formData) => {
                                    return new Promise((resolve, reject) => {
                                        fetch('backend/api/pay_card.php', {
                                            method: 'POST',
                                            body: JSON.stringify({ reserva_id: id, card_data: formData }),
                                            headers: { 'Content-Type': 'application/json' }
                                        }).then(r => r.json()).then(result => {
                                            if (result.error) {
                                                showAlert(result.error);
                                                reject();
                                            } else {
                                                resolve();
                                            }
                                        }).catch(() => reject());
                                    });
                                },
                                onError: (e) => console.error(e)
                            }
                        };
                        window.cardBrickController = await bricksBuilder.create('cardPayment', 'paymentCardBrick_container', settings);
                    };
                    renderCardBrick();
                }
            }
        } else {
            alert(json.error || "Reserva não encontrada");
            window.location.href = 'index.php';
        }
    } catch(err) {
        alert("Erro na conexão");
        console.error(err);
        window.location.href = 'index.php';
    }

    // copy btn
    const btnCopy = document.getElementById('btn-copy');
    if(btnCopy) {
        btnCopy.addEventListener('click', () => {
            const ipt = document.getElementById('pix-copiacola');
            ipt.select();
            ipt.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(ipt.value).then(() => {
                btnCopy.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> COPIADO';
                btnCopy.classList.replace('bg-[#2c3e50]', 'bg-[#00a650]');
                setTimeout(() => {
                    btnCopy.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg> COPIAR';
                    btnCopy.classList.replace('bg-[#00a650]', 'bg-[#2c3e50]');
                }, 2000);
            });
        });
    }

    function showAlert(message, title = 'Atenção', type = 'error') {
        const modal = document.getElementById('modal-alert');
        const alertTitle = document.getElementById('alert-title');
        const alertMsg = document.getElementById('alert-message');
        const iconError = document.getElementById('alert-icon-error');
        const iconInfo = document.getElementById('alert-icon-info');

        if (!modal) {
            alert(message);
            return;
        }

        alertTitle.textContent = title;
        alertMsg.textContent = message;

        if (type === 'error') {
            iconError.classList.remove('hidden');
            iconInfo.classList.add('hidden');
        } else {
            iconError.classList.add('hidden');
            iconInfo.classList.remove('hidden');
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('div').classList.remove('scale-95');
            modal.querySelector('div').classList.add('scale-100');
        }, 10);
    }
});
