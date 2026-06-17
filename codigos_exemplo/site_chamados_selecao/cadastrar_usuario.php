<?php
session_start();
require 'conexao.php';

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['nivel'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome = $_POST['nome'] ?? '';
  $email = $_POST['email'] ?? '';
  $senha = $_POST['senha'] ?? '';
  $nivel = $_POST['nivel'] ?? '';

  // Validações básicas
  if (empty($nome) || empty($email) || empty($senha) || empty($nivel)) {
    $erro = "Preencha todos os campos.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erro = "Email inválido.";
  } elseif (!in_array($nivel, ['admin', 'moderador'])) {
    $erro = "Nível inválido.";
  } else {
    // Verifica se email já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
      $erro = "Email já cadastrado.";
    } else {
      // Insere novo usuário com senha hash
      $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, nivel) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $nome, $email, $hashSenha, $nivel);
      if ($stmt->execute()) {
        $sucesso = "Usuário cadastrado com sucesso!";
        // Limpa os campos do formulário
        $_POST = [];
      } else {
        $erro = "Erro ao cadastrar usuário: " . $conn->error;
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cadastrar Usuário</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="painel.php">Administração</a>
    <div>
      <a href="usuarios.php" class="btn btn-outline-light me-2">⬅ Voltar</a>
      <a href="logout.php" class="btn btn-danger">Sair</a>
    </div>
  </div>
</nav>

<div class="container py-5">
  <h2 class="mb-4 text-center">Cadastrar Novo Usuário</h2>

  <?php if ($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php elseif ($sucesso): ?>
    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
  <?php endif; ?>

  <form method="POST" class="mx-auto" style="max-width: 500px;">
    <div class="mb-3">
      <label for="nome" class="form-label">Nome</label>
      <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required />
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email (Usuário)</label>
      <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
    </div>
    <div class="mb-3">
      <label for="senha" class="form-label">Senha</label>
      <input type="password" id="senha" name="senha" class="form-control" required />
    </div>
    <div class="mb-3">
      <label for="nivel" class="form-label">Nível</label>
      <select id="nivel" name="nivel" class="form-select" required>
        <option value="">Selecione...</option>
        <option value="admin" <?= (($_POST['nivel'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
        <option value="moderador" <?= (($_POST['nivel'] ?? '') === 'moderador') ? 'selected' : '' ?>>Moderador</option>
      </select>
    </div>
    <div class="d-grid">
      <button type="submit" class="btn btn-success">Cadastrar</button>
    </div>
  </form>
</div>

<footer class="container text-muted small mt-5 text-center">
  <hr>
  <em>
    Desenvolvido por Venicio Monteiro de Oliveira • MASP 1592154<br>
    <a href="mailto:venicio.oliveira@educacao.mg.gov.br">venicio.oliveira@educacao.mg.gov.br</a><br>
    Contato: (71) 99910-3812 ou (31) 9200-60347<br>
    <strong>Contate o desenvolvedor para suporte ou melhorias</strong>
  </em>
</footer>

</body>
</html>
