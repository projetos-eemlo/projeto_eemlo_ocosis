<?php
session_start();
require 'conexao.php';

// Verifica se usuário está logado e é admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['nivel'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// Pega o ID do usuário a ser editado
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
  header("Location: usuarios.php");
  exit;
}

$erro = '';
$sucesso = '';

// Busca dados atuais do usuário
$stmt = $conn->prepare("SELECT id, nome, email, nivel FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
  header("Location: usuarios.php");
  exit;
}

// Processa o POST para atualizar dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome = trim($_POST['nome'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $nivel = $_POST['nivel'] ?? '';
  $senha = $_POST['senha'] ?? '';

  // Validação básica
  if ($nome === '' || $email === '' || !in_array($nivel, ['admin', 'moderador'])) {
    $erro = "Preencha todos os campos obrigatórios corretamente.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erro = "Email inválido.";
  } else {
    // Verifica se email já existe para outro usuário
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
      $erro = "Este email já está cadastrado para outro usuário.";
    } else {
      // Monta SQL para atualizar
      if ($senha !== '') {
        // Atualiza senha com hash
        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, nivel = ?, senha = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nome, $email, $nivel, $hashSenha, $id);
      } else {
        // Não atualiza senha
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, nivel = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nome, $email, $nivel, $id);
      }

      if ($stmt->execute()) {
        $sucesso = "Usuário atualizado com sucesso.";
        // Atualiza variável $usuario para mostrar no formulário
        $usuario['nome'] = $nome;
        $usuario['email'] = $email;
        $usuario['nivel'] = $nivel;
      } else {
        $erro = "Erro ao atualizar usuário: " . $conn->error;
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
  <title>Editar Usuário</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="usuarios.php">← Voltar para Usuários</a>
    <div>
      <a href="logout.php" class="btn btn-danger">Sair</a>
    </div>
  </div>
</nav>

<div class="container py-5">
  <h2 class="mb-4">Editar Usuário</h2>

  <?php if ($erro): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
  <?php elseif ($sucesso): ?>
    <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
  <?php endif; ?>

  <form method="POST" class="mx-auto" style="max-width: 600px;">
    <div class="mb-3">
      <label for="nome" class="form-label">Nome *</label>
      <input type="text" id="nome" name="nome" class="form-control" required
             value="<?= htmlspecialchars($usuario['nome']) ?>">
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email (Usuário) *</label>
      <input type="email" id="email" name="email" class="form-control" required
             value="<?= htmlspecialchars($usuario['email']) ?>">
    </div>

    <div class="mb-3">
      <label for="nivel" class="form-label">Nível *</label>
      <select id="nivel" name="nivel" class="form-select" required>
        <option value="admin" <?= $usuario['nivel'] === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="moderador" <?= $usuario['nivel'] === 'moderador' ? 'selected' : '' ?>>Moderador</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="senha" class="form-label">Senha (deixe em branco para manter a atual)</label>
      <input type="password" id="senha" name="senha" class="form-control" autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
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
