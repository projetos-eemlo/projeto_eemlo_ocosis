<?php
session_start();

require 'Connect.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
     $nome = $_POST['nome'] ?? '';
     $senha = $_POST['senha'] ?? '';
     $email = $_POST['email'] ?? '';
     
     
     $id_tipo_func = 1; 
     $nome_cargo = 'Direção';

     if (empty($nome) || empty($senha) || empty($email)){
        $erro = "Preencha todos os campos";
     } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $erro = "O email está inválido";
     } else {
      
        try {
    $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

    // O SQL espera: nome, email, senha, cargo
    $sql = "INSERT INTO funcionarios (nome_funcionario, email_funcionario, senha_hash, cargo_funcionario) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    // CORREÇÃO: Use exatamente os nomes das variáveis criadas acima
    $stmt->execute([$nome, $email, $hashSenha, $nome_cargo]); 
    
    $sucesso = "Cadastro realizado com sucesso!";
    $_POST = [];
            
        } catch (PDOException $e) {
            
            if ($e->getCode() == 23000) { 
                $erro = "Este e-mail já está cadastrado no sistema.";
            } else {
                $erro = "Erro ao cadastrar: " . $e->getMessage();
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