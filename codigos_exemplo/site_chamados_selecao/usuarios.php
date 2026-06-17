<?php
session_start();
require 'conexao.php';

$msg_erro = $_SESSION['msg_erro'] ?? '';
$msg_sucesso = $_SESSION['msg_sucesso'] ?? '';
unset($_SESSION['msg_erro'], $_SESSION['msg_sucesso']);


// Verifica se o usuário está logado e se tem nível de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['nivel'] !== 'admin') {
  header("Location: login.php");
  exit;
}

// Busca todos os usuários cadastrados
$sql = "SELECT id, nome, email, nivel FROM usuarios ORDER BY nome ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gerenciar Usuários</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="painel.php">Administração</a>
    <div>
      <a href="painel.php" class="btn btn-outline-light me-2">⬅ Voltar</a>
      <a href="logout.php" class="btn btn-danger">Sair</a>
    </div>
  </div>
</nav>

<div class="container py-5">
  <h2 class="mb-4 text-center">Usuários do Sistema</h2>

  <div class="text-end mb-3">
    <a href="cadastrar_usuario.php" class="btn btn-success">➕ Cadastrar Novo Usuário</a>
  </div>

  <!-- Tabela de usuários -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Email (Usuário)</th>
          <th>Nível</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($u = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($u['id']) ?></td>
              <td><?= htmlspecialchars($u['nome']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['nivel']) ?></td>
              <td>
                <a href="editar_usuario.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary">✏️ Editar</a>

                <?php if ($u['id'] != $_SESSION['usuario']['id']): // evita excluir o próprio admin ?>
                  <a href="excluir_usuario.php?id=<?= $u['id'] ?>" 
                     class="btn btn-sm btn-outline-danger" 
                     onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                    🗑️ Excluir
                  </a>
                <?php else: ?>
                  <button class="btn btn-sm btn-outline-secondary" disabled title="Não pode excluir seu próprio usuário">🗑️ Excluir</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center text-muted">Nenhum usuário cadastrado.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
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
