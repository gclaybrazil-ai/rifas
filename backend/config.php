<?php
date_default_timezone_set('America/Sao_Paulo');
$db_host = 'localhost';
$db_name = 'top_sorte';
$db_user = 'root'; // default XAMPP
$db_pass = '';     // default XAMPP

try {
    // Connect to mysql server first to create db if needed
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    $pdo->exec("USE `$db_name`");
} catch(PDOException $e) {
    die(json_encode(['error' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]));
}
?>
