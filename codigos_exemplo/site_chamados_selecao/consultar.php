<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Consultar chamado Escuta SB</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-center">Consultar Chamado</h2>
  <p class="text-center">Digite o código que você recebeu após enviar a denúncia.</p>

  <form method="GET" action="verificar_codigo.php" class="row justify-content-center">
    <div class="col-md-6">
      <input type="text" name="codigo" class="form-control form-control-lg text-center" placeholder="Ex: ABC123052025" required />
    </div>
    <div class="col-12 text-center mt-3">
      <button type="submit" class="btn btn-primary btn-lg">Consultar</button>
    </div>
  </form>

  <div class="text-center mt-4">
    <a href="index.html" class="btn btn-outline-secondary">⬅ Voltar ao Início</a>
  </div>
</div>
</body>
</html>

