<?php
//session_start();

//require ('Connection.php');


/*if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['cargo_funcionario'] !== 'Direcao'){}
header("Location: login.php");
  exit;
*/

/*
$erro ='';
$sucesso='';



if ($_SERVER['REQUEST_METHOD'] == 'POST'){
     $nome = $_POST['nome'] ?? '';
     $senha = $_POST['senha'] ?? '';
     $email = $_POST['email'] ?? '';
     $id_tipo_func = 1;
     $nome_cargo = 'Direção';

     if (empty($nome) || empty($senha) || empty($email)){

        $erro="Preencha todos os campos";
     } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $erro = "O email está invalido";
     } else {
      
try {
$hashSenha = password_hash($senha, PASSWORD_DEFAULT);


    $stmt = $conn->prepare("INSERT INTO funcionarios (email_funcionario, id_tipo_func, nome_funcionario, senha_hash ) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssis", $nome, $email, $hashSenha, $id_tipo_func, $nome_cargo);
    $stmt->execute();
    
    // Redireciona para evitar duplicidade caso o usuário aperte F5 (Refresh)
    $sucesso = "Cadastro realizado com sucesso!";
    $_POST = []; // Esse comando limpa os campos do formulário
    
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 23000) { // Código de violação de chave única/estrangeira
        echo "Este e-mail já está cadastrado no sistema.";
    } else {
        echo "Erro ao cadastrar: " . $e->getMessage();
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