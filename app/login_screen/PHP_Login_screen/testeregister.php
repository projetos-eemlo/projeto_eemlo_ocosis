<?php
session_start();

// É importante que o Connection.php tenha o mysqli configurado para lançar exceções:
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require 'Connect.php';

/*
// BLOCO CORRIGIDO PARA O FUTURO:
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['cargo_funcionario'] !== 'Direcao') {
    header("Location: login.php");
    exit;
}
*/

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
        $erro = "O e-mail está inválido";
    } else {
        try {
            // Criptografa a senha antes de enviar para o banco
            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

            $sql = $pdo->prepare("INSERT INTO funcionarios (nome_funcionario, email_funcionario, senha_hash, id_tipo_func, cargo_funcionario) VALUES (?, ?, ?, ?, ?)");
            
            // Dica: Se $id_tipo_func for um número inteiro no banco, os tipos deveriam ser "sssis" em vez de "sssss". 
            // "s" = string, "i" = integer.
            $sql->execute([$nome, $email, $hashSenha, $id_tipo_func, $nome_cargo]);
            
            $sucesso = "Cadastro realizado com sucesso!";
            
        } catch (PDOException $e) {
            // 1062 é o código de erro padrão do MySQL para 'Duplicate entry' (Entrada duplicada)
            if ($e->getCode() == 1062) { 
                $erro = "Este funcionário/e-mail já está cadastrado no sistema.";
            } else {
                $erro = "Erro ao cadastrar: " . $e->getMessage();
            }
        }
    }
}

// Em vez de dar echo no meio do catch, passamos para as variáveis e disparamos aqui embaixo:
if (!empty($erro)) {
    echo "<script>alert('$erro'); window.history.back();</script>";
}
if (!empty($sucesso)) {
    echo "<script>alert('$sucesso'); window.location.href = 'cadastro.html';</script>";
}
?>