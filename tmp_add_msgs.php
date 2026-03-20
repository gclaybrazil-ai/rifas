<?php
require_once 'c:/xampp/htdocs/clone130326/backend/config.php';

$newMsgs = [
    ['🛡️ O site é seguro?', 'Com certeza! 🔒 Utilizamos criptografia SSL para proteger seus dados e todas as confirmações de pagamento são processadas diretamente via PIX com segurança bancária. Além disso, você recebe seu comprovante de reserva na hora!', '🛡️'],
    ['🏆 Como vejo quem ganhou?', 'É super transparente! ✨ Você pode acessar a aba **"Ganhadores"** no menu principal do site para ver o histórico completo de todos os sorteios realizados e os respectivos vencedores.', '🏆'],
    ['📅 Quando acontece o sorteio?', 'Nossos sorteios são baseados na **Loteria Federal**. 🏦 Assim que 100% dos números de uma rifa forem vendidos e pagos, a data oficial do sorteio é divulgada no site e nas nossas redes sociais!', '📅'],
    ['🧧 Como vejo meus números comprados?', 'Basta clicar no botão **"Meus Pedidos"** e digitar o seu número de WhatsApp. 📱 Você verá instantaneamente todas as suas reservas e os números de sorte atribuídos a você.', '🧧'],
    ['👥 Posso ser um afiliado?', 'Sim! 💰 Temos um programa de **Afiliados**. Se você deseja ganhar comissões indicando nossas rifas, clique no botão de suporte para falar com o administrador e solicitar sua ativação.', '👥']
];

foreach ($newMsgs as $m) {
    $stmt = $pdo->prepare("INSERT INTO assistant_messages (pergunta, resposta, icone) VALUES (?, ?, ?)");
    $stmt->execute($m);
}

echo "Novas perguntas incluídas com sucesso!";
?>
