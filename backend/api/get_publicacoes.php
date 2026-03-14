<?php
header('Content-Type: application/json');
require_once '../config.php';

try {
    // Pegar o limite se passado (ex: 1 para a home)
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
    
    $query = "SELECT * FROM publicacoes_ganhadores ORDER BY data_publicacao DESC";
    if($limit > 0) {
        $query .= " LIMIT " . $limit;
    }
    
    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['data' => $data]);
} catch(PDOException $e) {
    echo json_encode(['data' => []]);
}
?>
