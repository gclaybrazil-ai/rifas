/**
 * Virtual Assistant JS
 * Handles the chatbot UI and logic based on admin settings.
 */

(function() {
    // Configuration relative to current location
    const CONFIG_API = 'backend/api/admin.php'; // This might need adjustment based on page location
    
    // We need to know if we are in admin or frontend to adjust API path
    const is_admin = window.location.pathname.includes('/admin/');
    const api_path = is_admin ? '../backend/api/public.php' : 'backend/api/public.php';

    // Since we don't have a public.php action for assistant yet, I'll create one or use a new endpoint.
    // Let's create a small public API for assistant config.
    
    const style = `
        #assistant-container {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #assistant-trigger {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8e44ad, #d32f2f);
            box-shadow: 0 4px 15px rgba(142, 68, 173, 0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: white;
            overflow: hidden;
        }

        #assistant-trigger:hover {
            transform: scale(1.1);
        }

        #assistant-window {
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: assistantIn 0.3s ease-out;
            border: 1px solid rgba(0,0,0,0.05);
        }

        @keyframes assistantIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #assistant-header {
            background: linear-gradient(90deg, #8e44ad, #d32f2f);
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        #assistant-header .bot-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #assistant-header .bot-avatar {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8e44ad;
            font-size: 20px;
            overflow: hidden;
        }

        #assistant-header .bot-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #assistant-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }

        #assistant-header p {
            margin: 0;
            font-size: 10px;
            font-weight: bold;
            opacity: 0.9;
            text-transform: uppercase;
        }

        #assistant-header .close-btn {
            cursor: pointer;
            padding: 5px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        #assistant-header .close-btn:hover {
            opacity: 1;
        }

        #assistant-body {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f9f9ff;
            display: flex;
            flex-direction: column;
            gap: 15px;
            scrollbar-width: thin;
        }

        #assistant-body::-webkit-scrollbar { width: 4px; }
        #assistant-body::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 4px; }

        .chat-msg {
            max-width: 85%;
            padding: 12px 16px;
            border-radius: 15px;
            font-size: 13px;
            line-height: 1.5;
            position: relative;
        }

        .chat-msg.bot {
            align-self: flex-start;
            background: white;
            color: #444;
            border: 1px solid #eee;
            border-bottom-left-radius: 2px;
        }

        .chat-msg.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #8e44ad, #d32f2f);
            color: white;
            border-bottom-right-radius: 2px;
            font-weight: 600;
        }

        #assistant-quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 15px 15px;
            background: #f9f9ff;
        }

        .quick-reply {
            background: white;
            border: 1.5px solid #e0cffc;
            color: #8e44ad;
            font-weight: bold;
            font-size: 11px;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .quick-reply:hover {
            background: #f3ebff;
            border-color: #8e44ad;
        }

        #assistant-footer {
            padding: 15px;
            background: white;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        #assistant-input {
            flex: 1;
            border: 1.5px solid #e0cffc;
            border-radius: 25px;
            padding: 10px 15px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }

        #assistant-input:focus {
            border-color: #8e44ad;
        }

        #assistant-send {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8e44ad, #d32f2f);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }

        #assistant-send:hover {
            transform: rotate(10deg) scale(1.1);
        }

        .whatsapp-btn {
            background: #25d366;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 800;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
            text-transform: uppercase;
            align-self: center;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
            align-items: center;
            height: 10px;
            padding-left: 5px;
        }

        .dot {
            width: 4px;
            height: 4px;
            background: #8e44ad;
            border-radius: 50%;
            animation: dotWave 1.3s infinite ease-in-out;
        }

        .dot:nth-child(2) { animation-delay: 0.1s; }
        .dot:nth-child(3) { animation-delay: 0.2s; }

        @keyframes dotWave {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-4px); }
        }

        @media (max-width: 480px) {
            #assistant-window {
                width: calc(100vw - 40px);
                height: 70vh;
                max-height: 500px;
                bottom: 85px;
            }
        }
    `;

    class VirtualAssistant {
        constructor() {
            this.config = {
                enabled: false,
                name: 'Assistente Virtual',
                attendant: 'Atendente',
                whatsapp: ''
            };
            this.isOpen = false;
            this.container = null;
            this.init();
        }

        async init() {
            try {
                // Fetch config from new public api endpoint
                const res = await fetch(api_path + '?action=get_assistant_config');
                const data = await res.json();
                if(data.enabled === '0') return;

                this.config = {
                    enabled: data.enabled === '1',
                    name: data.name || 'Assistente Virtual',
                    attendant: data.attendant || 'Atendente',
                    whatsapp: data.whatsapp || '',
                    welcome_message: data.welcome_message || '',
                    messages: data.messages || []
                };

                this.injectStyles();
                this.render();
                this.setupEventListeners();
            } catch(e) {
                console.error("Assistant init failed:", e);
            }
        }

        injectStyles() {
            const head = document.head || document.getElementsByTagName('head')[0];
            const styleElement = document.createElement('style');
            styleElement.innerHTML = style;
            head.appendChild(styleElement);
        }

        render() {
            let quickRepliesHtml = '';
            this.config.messages.forEach(m => {
                quickRepliesHtml += `<button class="quick-reply" data-id="${m.id}">${m.pergunta}</button>`;
            });

            const html = `
                <div id="assistant-container">
                    <div id="assistant-window">
                        <div id="assistant-header">
                            <div class="bot-info">
                                <div class="bot-avatar">
                                    <img src="frontend/png/assistente-virtual.png" alt="Avatar">
                                </div>
                                <div>
                                    <h3 id="bot-name-display">${this.config.name}</h3>
                                    <p>Online • Responde na hora!</p>
                                </div>
                            </div>
                            <div class="close-btn" id="assistant-close-x">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="12"></line></svg>
                            </div>
                        </div>
                        <div id="assistant-body">
                            <!-- Messages -->
                        </div>
                        <div id="assistant-quick-replies" style="display: none;">
                            ${quickRepliesHtml}
                        </div>
                        <div id="assistant-footer">
                            <input type="text" id="assistant-input" placeholder="Digite sua pergunta...">
                            <button id="assistant-send">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transform: rotate(45deg); margin-left: -3px; margin-top: 2px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            </button>
                        </div>
                    </div>
                    <div id="assistant-trigger">
                        <img src="frontend/png/assistente-virtual.png" id="trigger-icon-chat" style="width: 100%; height: 100%; object-fit: cover;">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="trigger-icon-close" style="display: none;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </div>
                </div>
            `;
            const div = document.createElement('div');
            div.innerHTML = html;
            document.body.appendChild(div);
            this.container = div;

            this.showWelcome();
        }

        setupEventListeners() {
            const trigger = document.getElementById('assistant-trigger');
            const windowEl = document.getElementById('assistant-window');
            const closeX = document.getElementById('assistant-close-x');
            const sendBtn = document.getElementById('assistant-send');
            const input = document.getElementById('assistant-input');
            const chatIcon = document.getElementById('trigger-icon-chat');
            const closeIcon = document.getElementById('trigger-icon-close');

            const toggle = () => {
                this.isOpen = !this.isOpen;
                windowEl.style.display = this.isOpen ? 'flex' : 'none';
                chatIcon.style.display = this.isOpen ? 'none' : 'block';
                closeIcon.style.display = this.isOpen ? 'block' : 'none';
                if(this.isOpen) {
                    input.focus();
                    const body = document.getElementById('assistant-body');
                    body.scrollTop = body.scrollHeight;
                }
            };

            trigger.addEventListener('click', toggle);
            closeX.addEventListener('click', toggle);

            sendBtn.addEventListener('click', () => this.handleSend());
            input.addEventListener('keypress', (e) => {
                if(e.key === 'Enter') this.handleSend();
            });

            // Handle quick replies dynamically using delegation
            document.getElementById('assistant-quick-replies').addEventListener('click', (e) => {
                const btn = e.target.closest('.quick-reply');
                if(btn) {
                    this.handleQuickReply(btn.textContent, btn.getAttribute('data-id'));
                }
            });
        }

        async showWelcome() {
            let msg = this.config.welcome_message;
            if(!msg) {
                msg = `Olá! 👋 Sou o assistente da ${this.config.name.replace('Assistente ', '')}!<br><br>Estou aqui para te ajudar com qualquer dúvida sobre o sorteio. Como posso te ajudar hoje?`;
            }
            await this.addMessage(msg, 'bot');
            document.getElementById('assistant-quick-replies').style.display = 'flex';
        }

        async addMessage(text, side) {
            const body = document.getElementById('assistant-body');
            
            if(side === 'bot') {
                const typing = document.createElement('div');
                typing.className = 'chat-msg bot typing';
                typing.innerHTML = '<div class="typing-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>';
                body.appendChild(typing);
                body.scrollTop = body.scrollHeight;
                await new Promise(r => setTimeout(r, 800));
                typing.remove();
            }

            const msg = document.createElement('div');
            msg.className = `chat-msg ${side}`;
            msg.innerHTML = text;
            body.appendChild(msg);
            body.scrollTop = body.scrollHeight;
        }

        handleSend() {
            const input = document.getElementById('assistant-input');
            const text = input.value.trim();
            if(!text) return;

            input.value = '';
            this.addMessage(text, 'user');
            
            // Basic generic response
            setTimeout(() => {
                this.addMessage("Legal! Para um atendimento mais rápido, recomendo escolher uma das opções abaixo ou falar diretamente com nosso atendente. 😊", 'bot');
                document.getElementById('assistant-quick-replies').style.display = 'flex';
            }, 1000);
        }

        async handleQuickReply(label, id) {
            document.getElementById('assistant-quick-replies').style.display = 'none';
            await this.addMessage(label, 'user');

            const msgData = this.config.messages.find(m => m.id == id);
            if(!msgData) return;

            let response = msgData.resposta;
            let showAttendantBtn = label.toLowerCase().includes('atendente');

            await this.addMessage(response, 'bot');
            
            if(showAttendantBtn) {
                const body = document.getElementById('assistant-body');
                const link = `https://wa.me/${this.config.whatsapp}?text=Olá,%20gostaria%20de%20ajuda%20sobre%20o%20sorteio!`;
                const btn = document.createElement('a');
                btn.href = link;
                btn.target = "_blank";
                btn.className = "whatsapp-btn";
                btn.innerHTML = `📲 Falar com ${this.config.attendant}`;
                body.appendChild(btn);
                body.scrollTop = body.scrollHeight;
            }

            document.getElementById('assistant-quick-replies').style.display = 'flex';
        }
    }

    // Auto-init
    document.addEventListener('DOMContentLoaded', () => {
        new VirtualAssistant();
    });
})();
