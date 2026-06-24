<?php
session_start();
require 'Connection.php';


/*if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['cargo_funcionario'] !== 'Direcao'){}
header("Location: login.php");
  exit;
*/


$erro ='';
$sucesso='';



if ($_SERVER['REQUEST_METHOD'] == 'POST'){
     $nome = $_POST['nome'] ?? '';
     $cargo = $_POST['cargo'] ?? '';
     $senha = $_POST['password'] ?? '';
     $email = $_POST['email'] ?? '';
     

     if (empty($nome) || empty($cargo) || empty($senha) || empty($email)){

        $erro="Preencha todos os campos";
     } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $erro = "O email está invalido";
     } else {


     $stmt = $conn->prepare("SELECT id_funcionario FROM funcionarios WHERE email_funcionario=?");
     $stmt->bind_param("s", $email);
     $stmt->execute();
     $result = $stmt->get_result();

if ($result->num_rows > 0){
   $erro = "Esse email já está cadastrado no sistema";
}else { 
   $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
   $stmt = $conn->prepare("INSERT INTO funcionarios (nome_funcionario, email_funcionario, senha_hash, cargo_funcionario) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $nome, $email, $hashSenha, $cargo);
      if ($stmt->execute()) {
        $sucesso = "Funcionário cadastrado com sucesso!";
        $_POST = []; // Limpa os campos
      } else {
        $erro = "Erro ao cadastrar funcionário: " . $conn->error;
      }

}
}
}



if (!empty($erro)) {
  echo "<script>alert('$erro'); window.history.back();</script>";
}
if (!empty($sucesso)) {
  echo "<script>alert('$sucesso'); window.location.href = 'cadastro.html';</script>";
}
?>