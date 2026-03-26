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
$titulo = trim($_POST['titulo'] ?? '');
$subtitulo = trim($_POST['subtitulo'] ?? '');
$valor_numero = (float)($_POST['valor_numero'] ?? 0);
$total_numeros = (int)($_POST['total_numeros'] ?? 0);

// Validações
if (empty($titulo) || $valor_numero <= 0 || $total_numeros <= 0) {
    echo json_encode(['error' => 'Preencha todos os campos obrigatórios corretamente']);
    exit;
}

// Processamento de Imagem
$imagem_url = '';
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['imagem']['tmp_name'];
    $fileName = $_FILES['imagem']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
    if (in_array($fileExtension, $allowedfileExtensions)) {
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = '../../uploads/'; // Relativo à API (vender/uploads/)
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $imagem_url = 'uploads/' . $newFileName;
        } else {
            echo json_encode(['error' => 'Erro ao mover o arquivo de imagem']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Extensão de imagem não permitida (jpg, png, webp)']);
        exit;
    }
} else {
    echo json_encode(['error' => 'A imagem da rifa é obrigatória']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO rifas (usuario_id, titulo, subtitulo, valor_numero, total_numeros, imagem_url, status) VALUES (?, ?, ?, ?, ?, ?, 'pendente_ativacao')");
    $stmt->execute([$u_id, $titulo, $subtitulo, $valor_numero, $total_numeros, $imagem_url]);
    $lastId = $pdo->lastInsertId();
    
    echo json_encode(['success' => true, 'id' => $lastId]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao salvar rifa no banco SaaS: ' . $e->getMessage()]);
}
