<?php
$host = 'localhost';
$dbname = 'ocosis';
$user = 'root'; // Altere se o seu usuário do MySQL for diferente
$pass = '';     // Altere se tiver senha no seu banco local

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['sucesso' => false, 'mensagem' => 'Erro na conexão com o banco: ' . $e->getMessage()]));
}
?>