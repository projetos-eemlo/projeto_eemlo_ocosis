<?php
header('Content-Type: application/json');
require 'conexao.php'; 

$simade = $_GET['simade'] ?? '';

if (empty($simade)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'SIMADE não informado.']);
    exit;
}

try {
    // 1. Busca os dados do Aluno
    $stmtAluno = $pdo->prepare("
        SELECT a.id_aluno, a.nome_aluno, a.num_simade, 
               DATE_FORMAT(a.dt_nascimento, '%d/%m/%Y') as dt_nascimento, 
               t.desc_turma
        FROM alunos a
        LEFT JOIN turma t ON a.id_turma = t.id_turma
        WHERE a.num_simade = :simade
    ");
    $stmtAluno->execute([':simade' => $simade]);
    $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Aluno não encontrado.']);
        exit;
    }

    // 2. Busca o Histórico de Ocorrências reais do DB
    $stmtOcorrencias = $pdo->prepare("
        SELECT o.id_ocorrencia, 
               DATE_FORMAT(o.data_ocorrencia, '%d/%m/%Y') as data_formatada, 
               o.horario, 
               d.desc_disciplina, 
               f.nome_funcionario, 
               toco.desc_ocorrencia as tipo_infracao,
               o.desc_ocorrencia as observacao
        FROM ocorrencias o
        LEFT JOIN disciplinas d ON o.id_disciplina = d.id_disciplina
        LEFT JOIN funcionarios f ON o.id_funcionario = f.id_funcionario
        LEFT JOIN tipo_ocorrencia toco ON o.id_tipo_ocorrencia = toco.id_tipo_ocorrencia
        WHERE o.id_aluno = :id_aluno
        ORDER BY o.data_ocorrencia DESC, o.horario DESC
    ");
    $stmtOcorrencias->execute([':id_aluno' => $aluno['id_aluno']]);
    $ocorrencias = $stmtOcorrencias->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'sucesso' => true, 
        'aluno' => $aluno, 
        'ocorrencias' => $ocorrencias
    ]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro no banco: ' . $e->getMessage()]);
}
?>