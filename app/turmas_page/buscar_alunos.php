<?php
header('Content-Type: application/json');

// Puxa a conexão que criamos na mesma pasta
require 'conexao.php'; 

// Recebe o nome da turma via método GET
$turmaSelecionada = $_GET['turma'] ?? '';

if (empty($turmaSelecionada)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhuma turma informada.']);
    exit;
}

try {
    // Faz um INNER JOIN para pegar os alunos que têm o id_turma igual ao da desc_turma pesquisada
    $sql = "SELECT a.nome_aluno 
            FROM alunos a
            INNER JOIN turma t ON a.id_turma = t.id_turma
            WHERE t.desc_turma = :turma
            ORDER BY a.nome_aluno ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':turma' => $turmaSelecionada]);
    
    // Puxa todos os resultados
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['sucesso' => true, 'dados' => $alunos]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar alunos: ' . $e->getMessage()]);
}
?>