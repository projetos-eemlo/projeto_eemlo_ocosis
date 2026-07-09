<?php
header('Content-Type: application/json');
require 'conexao.php'; 

try {
    // Busca todos os alunos, juntando com a tabela turma para pegar o nome da turma
    // Também conta quantas ocorrências aquele aluno tem (simulação baseada na sua estrutura)
    $sql = "SELECT a.id_aluno, a.num_simade, a.nome_aluno, t.desc_turma,
            (SELECT COUNT(*) FROM ocorrencias o WHERE o.id_aluno = a.id_aluno) as total_ocorrencias
            FROM alunos a
            LEFT JOIN turma t ON a.id_turma = t.id_turma
            ORDER BY a.nome_aluno ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['sucesso' => true, 'dados' => $alunos]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar alunos: ' . $e->getMessage()]);
}
?>