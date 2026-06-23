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
     

     if (empty($nome) || empty($cargo) || empty($senha)) || empty($email){

        $erro="Preencha todos os campos";
     } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $erro = "O email está invalido";
     } else {


     $stmt = $conn->prepare("SELECT id_funcionario FROM funcionarios WHERE email_funcionario=?")
     }
       


}


?>