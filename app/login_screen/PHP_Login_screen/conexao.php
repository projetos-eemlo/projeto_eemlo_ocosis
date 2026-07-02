<?php
$conexao = new mysqli("localhost", "root", "", "ocosis");

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
?>