<?php
date_default_timezone_set('America/Sao_Paulo');
$db_host = 'localhost';

if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1' || $_SERVER['HTTP_HOST'] == 'localhost') {
    // Local (XAMPP)
    $db_name = 'top_sorte';
    $db_user = 'root';
    $db_pass = '';
} else {
    // Servidor (Produção)
    $db_name = 'u422005024_riffas';
    $db_user = 'u422005024_riffas';
    $db_pass = 'S3creta@';
}

try {
    // Connect to mysql server first to create db if needed
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    $pdo->exec("USE `$db_name`");
} catch(PDOException $e) {
    die(json_encode(['error' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]));
}
