// 2. Busca o Histórico de Ocorrências reais do DB (Ajustado para sua estrutura real)
    $stmtOcorrencias = $pdo->prepare("
        SELECT o.id_ocorrencia, 
               DATE_FORMAT(o.data_ocorrencia, '%d/%m/%Y') as data_formatada, 
               o.horario, 
               o.disciplina, 
               f.cargo_funcionario as nome_funcionario, 
               ti.desc_infracao as tipo_infracao,
               o.desc_ocorrencia as observation
        FROM ocorrencias o
        LEFT JOIN funcionarios f ON o.id_funcionario = f.id_funcionario
        LEFT JOIN tipo_infracao ti ON o.id_tipo_infracao = ti.id_tipo_infracao
        WHERE o.id_aluno = :id_aluno
        ORDER BY o.data_ocorrencia DESC, o.horario DESC
    ");