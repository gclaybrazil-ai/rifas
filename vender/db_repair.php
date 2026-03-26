<?php
require_once 'backend/config.php';
// Consertar TODAS que ficaram em branco devido ao erro de ENUM
$pdo->exec("UPDATE rifas SET status = 'ativa' WHERE status = '' OR status IS NULL");
// Garantir que a de ID 3 (a do teste real) está OK
$pdo->exec("UPDATE rifas SET status = 'ativa' WHERE id = 3");
echo "DB REPAIRED: Raffles set to 'ativa'";
