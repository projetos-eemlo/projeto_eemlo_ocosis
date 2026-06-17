<?php
session_start();
require 'conexao.php';

$email = $_POST['email'];
$senha = $_POST['senha'];

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
  if (password_verify($senha, $user['senha'])) {
    $_SESSION['usuario'] = $user;
    header("Location: painel.php");
  } else {
    echo "Senha incorreta. <a href='login.php'>Voltar</a>";
  }
} else {
  echo "Usuário não encontrado. <a href='login.php'>Voltar</a>";
}
?>
