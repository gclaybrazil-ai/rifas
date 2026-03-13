# Top Sorte - Sistema de Rifas Online Completo

## Estrutura do Projeto
Este projeto foi construído utilizando as melhores práticas para que seja leve, escalável e funcione perfeitamente em ambientes modernos e clássicos de hospedagem PHP.

```text
/clone130326
├── admin/
│   └── index.html             # Painel administrativo
├── backend/
│   ├── config.php             # Configuração do banco de dados (PDO)
│   ├── init_db.php            # Script de inicialização automática das tabelas e DB
│   └── api/
│       ├── admin.php          # Endpoints do admin (Estatísticas, Sorteio, Reset)
│       ├── check_payment.php  # Verificador rápido de status para o Polling Front
│       ├── get_rifa.php       # Retorna grade 0 ao 99 e dados (+limpeza auto de expirados)
│       ├── pix_webhook.php    # Recebedor de webhook do provedor PIX
│       └── reserve.php        # Lógica central: seleciona números (FOR UPDATE) e gera PIX
├── frontend/
│   ├── css/
│   │   └── style.css          # Regras visuais puras (com Tailwind CDN na main)
│   └── js/
│       └── app.js             # Client-side (Poling, regras modais, timer 5min)
└── index.html                 # Página Principal de venda Mobile First e fluída
```

## Como Instalar e Rodar Localmente (XAMPP)
1. Certifique-se de que o **Apache** e **MySQL** estejam rodando no seu XAMPP.
2. Acesse no seu navegador a URL:
   `http://localhost/clone130326/backend/init_db.php`
   *(Este script inicializará o banco de dados `top_sorte`, criará as tabelas e inserirá as opções iniciais.)*
3. Após aparecer a mensagem de sucesso, acesse o sistema através de:
   `http://localhost/clone130326/index.html`

## Como realizar testes
- **Front:** Você pode selecionar até 20 números, preencher seu nome/whatsapp e reservar. Um timer de 5 minutos começará.
- **Pagamento Simulado manual:** Deixe em uma guia o Modal PIX aberto esperando o pagamento... em uma segunda guia abra `http://localhost/clone130326/admin/index.html` e "Marque Pago". Na hora que você clicar lá, a guia do cliente fechará o contador e mostrará sucesso (via Polling Inteligente).
- **Expiração:** Deixe o contador de 5 min chegar a zero. Os números ficarão verdes e seu "pedido" sumirá.

## Integração PIX na Prática
O arquivo `backend/api/reserve.php` gera atualmente um TXID e Code DUMMY.
Você deve trocar esta parte usando o SDK/API da MercadoPago / PagBank.
No arquivo `backend/api/pix_webhook.php` você irá configurar a URL do Webhook do seu provedor.

## Instruções de Deploy para Produção (VPS / cPanel)
1. Faça o upload de todo o conteúdo da pasta `clone130326` para a pasta `/public_html` (ou subdomínio/diretório que for usar).
2. Abra o arquivo `backend/config.php` e insira as credenciais do seu banco de dados de produção (Host, User, Password).
3. Via navegador, acesse `seu-dominio.com.br/backend/init_db.php` **UMA ÚNICA VEZ** para que sejam criadas as tabelas.
4. Após criado e configurado, delete o arquivo `init_db.php` do servidor por motivos de segurança.
5. Acesse seu painel em `seu-dominio.com.br/admin/index.html`.

**Tecnologias utilizadas:**
Front: Vanilla JS, TailwindCSS, Fetch API Intersecting.
Back: PHP 8+, PDO MySQL, REST API com WebHooks Pattern.
