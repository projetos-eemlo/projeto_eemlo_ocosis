<?php
include("conexao.php");

if (isset($_POST['usuario']) && isset($_POST['senha'])) {
    
    $email = $conexao->real_escape_string($_POST['usuario']);
    $senha_digitada = $_POST['senha']; 

    $sql_code = "SELECT * FROM funcionarios WHERE email_funcionario = '$email'";
    $sql_query = $conexao->query($sql_code);

    if ($sql_query && $sql_query->num_rows == 1) {
        
        $funcionario = $sql_query->fetch_assoc();
        

        if (password_verify($senha_digitada, $funcionario['senha_hash'])) {
            // Se acertou a senha, devolve apenas a palavra "sucesso"
            echo "sucesso";
        } else {
            echo "E-mail ou senha incorretos.";
        }
        
    } else {
        echo "E-mail ou senha incorretos.";
    }
} else {
    echo "Preencha todos os campos.";
}
?>