<?php
$host = '127.0.0.1';
$dbname = 'sistema_ocorrencia'; 
$user = 'root'; 
$pass = '';

try {
    // Criando a conexão PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage()); 
}
?>