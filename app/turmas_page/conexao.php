<?php
$host = 'localhost';
$dbname = 'ocosis';
$user = 'root'; // Seu usuário do banco
$pass = '';     // Sua senha do banco (se tiver)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Configura o PDO para mostrar os erros caso algo dê errado no SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Se der erro na conexão, devolve um JSON para o JavaScript ler
    die(json_encode(['sucesso' => false, 'mensagem' => 'Erro na conexão com o banco: ' . $e->getMessage()]));
}
?>