[]<?php
include("conexao.php");




$exibirErro = false;
$mensagemErro = "";
$loginSucesso = false;








if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['usuario']) && isset($_POST['senha'])) {
       
        $email = $conexao->real_escape_string($_POST['usuario']);
        $senha_digitada = $_POST['senha'];




        $sql_code = "SELECT * FROM funcionarios WHERE email_funcionario = '$email'";
        $sql_query = $conexao->query($sql_code);




        if ($sql_query && $sql_query->num_rows == 1) {
            $funcionario = $sql_query->fetch_assoc();
           
            if (password_verify($senha_digitada, $funcionario['senha_hash'])) {
                $loginSucesso = true;
     
            } else {
                $exibirErro = true;
                $mensagemErro = "E-mail ou senha incorretos.";
            }
        } else {
            $exibirErro = true;
            $mensagemErro = "E-mail ou senha incorretos.";
        }
    } else {
        $exibirErro = true;
        $mensagemErro = "Preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso ao Sistema</title>
    <link rel="stylesheet" href="CSS_login_screen/login.css">
    <style>
 
        #error-msg {
            display: <?php echo $exibirErro ? 'block' : 'none'; ?>;
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>




     <nav class="navbar">
    <a href="#">Login/ Cadastro</a>
    <a href="cadastro.html">Cadastro Usuário</a>
    <a href="#">Nova Ocorrência</a>
    <a href="#">Pesquise e Turmas</a>
    <a href="#">Perfil do Aluno</a>
</nav>




    <main class="container">
        <div class="login-card login-container">
            <h2>1. Acesso ao Sistema</h2>
            <hr class="divider">
           
            <h3 class="subtitle">Entrar</h3>
            <hr class="divider">




            <?php if ($loginSucesso): ?>
                <script>
                    alert('Login efetuado com sucesso!');
                   
                </script>
            <?php endif; ?>




            <form id="loginForm" method="POST" action="">
                <div class="input-group">
                    <label for="email">E-mail ou Matrícula:</label>
                    <input type="text" id="email" name="usuario" placeholder="Digite seu e-mail" value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>" required>
                </div>




                <div class="input-group">
                    <label for="password">Senha:</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="senha" placeholder="Digite sua senha" required>
                        <button type="button" class="btn-show-password" onclick="togglePassword()">Mostrar</button>
                    </div>
                </div>




                <div class="error-message" id="error-msg">
                    <?php echo $mensagemErro; ?>
                </div>




               
                <div class="actions button-group">
                    <button type="submit" class="btn-entrar">Entrar</button>
                   <a href="cadastro.html" class="btn-cadastro" style="text-decoration: none; display: inline-block; text-align: center;">Solicitar Cadastro</a>
                </div>
            </form>
        </div>
    </main>




    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.btn-show-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = 'Ocultar';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = 'Mostrar';
            }
        }
    </script>
    //oi
</body>
</html>


