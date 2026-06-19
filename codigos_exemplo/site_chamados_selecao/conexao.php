<?php
// conexao.php
date_default_timezone_set('America/Sao_Paulo');

$host = 'localhost';
$user = 'root';
$password = ' ';
$database = 'sistema_ocorrencia';
$port = 3306;

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Define charset para evitar problemas com acentuação
$conn->set_charset('utf8mb4');
