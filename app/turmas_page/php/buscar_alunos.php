<?php
header('Content-Type: application/json');
require 'conexao.php'; 

// Recebe o ID da turma via método GET
$idTurma = isset($_GET['id_turma']) ? intval($_GET['id_turma']) : 0;

if ($idTurma <= 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID da turma inválido ou não informado.']);
    exit;
}

try {
    // Busca os alunos filtrando pelo ID numérico da turma
    $sql = "SELECT a.id_aluno, a.num_simade, a.nome_aluno 
            FROM alunos a
            WHERE a.id_turma = :id_turma
            ORDER BY a.nome_aluno ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_turma' => $idTurma]);
    
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retorna no padrão que o seu turmas.js espera (data.dados)
    echo json_encode(['sucesso' => true, 'dados' => $alunos]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar alunos: ' . $e->getMessage()]);
}
?>