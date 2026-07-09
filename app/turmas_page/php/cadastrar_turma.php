<?php
header('Content-Type: application/json');

// Puxa a conexão que acabamos de criar na MESMA pasta
require 'conexao.php'; 

// Recebe os dados do JavaScript
$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhum dado recebido.']);
    exit;
}

// Limpa os dados recebidos
$desc_turma = trim($dados['descTurma']);
$ano_letivo = trim($dados['anoLetivo']);
$semestre_letivo = trim($dados['semestreLetivo']);
$turno = trim($dados['turno']);
// $capacidade = trim($dados['capacidade']); // Descomente caso tenha criado a coluna no banco
// $status = trim($dados['status']);         // Descomente caso tenha criado a coluna no banco

try {
    // Verifica se a turma já existe (Evita duplicidade)
    $stmt_check = $pdo->prepare("SELECT id_turma FROM turma WHERE desc_turma = :desc_turma AND ano_letivo = :ano_letivo");
    $stmt_check->execute([':desc_turma' => $desc_turma, ':ano_letivo' => $ano_letivo]);
    
    if ($stmt_check->rowCount() > 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Esta turma já está cadastrada para este ano letivo!']);
        exit;
    }

    // Insere a nova turma no banco
    $stmt_insert = $pdo->prepare("INSERT INTO turma (desc_turma, turno, ano_letivo, semestre_letivo, trimestre_letivo) 
                                  VALUES (:desc_turma, :turno, :ano_letivo, :semestre_letivo, '1')");
    
    $stmt_insert->execute([
        ':desc_turma' => $desc_turma,
        ':turno' => $turno,
        ':ano_letivo' => $ano_letivo,
        ':semestre_letivo' => $semestre_letivo
    ]);

    echo json_encode(['sucesso' => true, 'mensagem' => 'Turma registrada com sucesso!']);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]);
}
?>