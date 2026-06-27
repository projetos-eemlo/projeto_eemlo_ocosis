<?php
header('Content-Type: application/json');
require 'conexao.php'; 

try {
    // Busca todas as turmas cadastradas em ordem alfabética
    $stmt = $pdo->query("SELECT id_turma, desc_turma FROM turma ORDER BY desc_turma ASC");
    $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['sucesso' => true, 'dados' => $turmas]);

} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao buscar turmas: ' . $e->getMessage()]);
}
?>