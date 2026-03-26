<?php
require_once '../config.php';

header('Content-Type: application/json');

// Proteção da API
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$u_id = $_SESSION['usuario_id'];
$r_id = (int)($_POST['id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$subtitulo = trim($_POST['subtitulo'] ?? '');
$valor_numero = (float)($_POST['valor_numero'] ?? 0);
$total_numeros = (int)($_POST['total_numeros'] ?? 0);

// Validações
if ($r_id <= 0 || empty($titulo) || $valor_numero <= 0 || $total_numeros <= 0) {
    echo json_encode(['error' => 'Preencha todos os campos obrigatórios corretamente']);
    exit;
}

// Verificar se a rifa pertence ao usuário e se pode ser editada (geralmente só se não estiver ativa ou com reservas)
// Mas para simplificar vamos deixar o criador editar se for dele.
$stmt = $pdo->prepare("SELECT usuario_id, imagem_url, status FROM rifas WHERE id = ?");
$stmt->execute([$r_id]);
$rifa = $stmt->fetch();

if (!$rifa || ($rifa['usuario_id'] != $u_id && $_SESSION['usuario_role'] !== 'admin')) {
    echo json_encode(['error' => 'Rifa não encontrada ou sem permissão']);
    exit;
}

// Se a rifa já estiver ativa, talvez devamos proibir mudar total_numeros ou valor_numero
// Mas vamos seguir o pedido do usuário por enquanto.

// Processamento de Imagem (Opcional na edição)
$imagem_url = $rifa['imagem_url'];
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['imagem']['tmp_name'];
    $fileName = $_FILES['imagem']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
    if (in_array($fileExtension, $allowedfileExtensions)) {
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = '../../uploads/'; 
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $imagem_url = 'uploads/' . $newFileName;
        }
    }
}

try {
    $stmt = $pdo->prepare("UPDATE rifas SET titulo = ?, subtitulo = ?, valor_numero = ?, total_numeros = ?, imagem_url = ? WHERE id = ?");
    $stmt->execute([$titulo, $subtitulo, $valor_numero, $total_numeros, $imagem_url, $r_id]);
    
    echo json_encode(['success' => true, 'id' => $r_id]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao atualizar rifa: ' . $e->getMessage()]);
}
