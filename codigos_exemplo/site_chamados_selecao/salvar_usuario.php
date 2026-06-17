<?php
require 'conexao.php';

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';
$nivel = $_POST['nivel'] ?? 'moderador';

if (empty($nome) || empty($email) || empty($senha)) {
  echo "Todos os campos são obrigatórios.";
  exit;
}

$senha_hash = password_hash($senha, PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, nivel) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nome, $email, $senha_hash, $nivel);

if ($stmt->execute()) {
  echo "<p>Usuário cadastrado com sucesso!</p>";
  echo "<p><a href='login.php'>Ir para Login</a> | <a href='painel.php'>Painel</a></p>";
} else {
  echo "<p>Erro ao cadastrar: " . $conn->error . "</p>";
}
?>
