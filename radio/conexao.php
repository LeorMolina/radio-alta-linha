<?php
$host = '127.0.0.1';
$port = '3307';
$dbname = 'radio_varejo';
$username = 'root';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    global $pdo; 
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log("Erro de Conexão DB: " . $e->getMessage());
    die("Erro Crítico: Não foi possível conectar ao banco de dados.");
}