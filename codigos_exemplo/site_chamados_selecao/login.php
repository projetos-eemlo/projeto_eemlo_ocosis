<?php
session_start();
require 'config.php';
//require 'conexao.php';

//require 'config.php';

// Se o usuário já estiver logado, redireciona para o painel
if (isset($_SESSION['usuario'])) {
  header("Location: painel.php");
  exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['usuario'] ?? '';  // Aqui o campo 'usuario' é o email enviado do form
  $senha = $_POST['senha'] ?? '';

  $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res && $res->num_rows === 1) {
    $dados = $res->fetch_assoc();
    if (password_verify($senha, $dados['senha'])) {
      $_SESSION['usuario'] = [
        'id' => $dados['id'],
        'nome' => $dados['nome'],
        'nivel' => $dados['nivel']
      ];
      header("Location: painel.php");
      exit;
    } else {
      $erro = "Senha incorreta.";
    }
  } else {
    $erro = "Usuário não encontrado.";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Administrativo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-5">
  <h2 class="text-center mb-4">Área Administrativa</h2>

  <?php if (!empty($erro)): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST" class="row justify-content-center">
    <div class="col-md-6">
      <div class="mb-3">
        <label for="usuario" class="form-label">Email</label>
        <input type="email" class="form-control" id="usuario" name="usuario" required>
      </div>
      <div class="mb-3">
        <label for="senha" class="form-label">Senha</label>
        <input type="password" class="form-control" id="senha" name="senha" required>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
      </div>
    </div>
  </form>

  <div class="text-center mt-4">
    <a href="index.php" class="btn btn-outline-secondary">⬅ Voltar para Página Inicial</a>
  </div>
</div>
</body>
</html>
