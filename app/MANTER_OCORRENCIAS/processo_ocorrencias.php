<?php

$host     = "localhost";
$usuario  = "root";      
$senha    = "";          
$banco    = "sistema_escolar";


try {
    $conexao = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);
   
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    
    $nome_aluno       = htmlspecialchars(trim($_POST['nome_aluno']));
    $simade           = htmlspecialchars(trim($_POST['simade']));
    $data_nascimento  = $_POST['data_nascimento'];
    $data_ocorrencia  = $_POST['data_ocorrencia'];
    $horario_ocorrencia = $_POST['horario_ocorrencia'];
    $turma            = $_POST['turma'];
    $materia          = htmlspecialchars(trim($_POST['materia']));
    $professor        = htmlspecialchars(trim($_POST['professor']));
    $descricao        = htmlspecialchars(trim($_POST['descricao']));
    
    
    $infracoes_array = isset($_POST['infracoes']) ? $_POST['infracoes'] : [];
    $outro_tipo      = htmlspecialchars(trim($_POST['outro_tipo']));
    
    if (!empty($outro_tipo)) {
        $infracoes_array[] = "Outros: " . $outro_tipo;
    }
    
    $infracoes_string = implode(", ", $infracoes_array);

    if (empty($nome_aluno) || empty($simade) || empty($data_ocorrencia) || empty($turma)) {
        die("Por favor, preencha todos os campos obrigatórios.");
    }

    try {
        $sql = "INSERT INTO ocorrencias 
                (nome_aluno, simade, data_nascimento, data_ocorrencia, horario_ocorrencia, turma, materia, professor, infracoes, descricao) 
                VALUES 
                (:nome_aluno, :simade, :data_nascimento, :data_ocorrencia, :horario_ocorrencia, :turma, :materia, :professor, :infracoes, :descricao)";
        
        $stmt = $conexao->prepare($sql);

        $stmt->bindParam(':nome_aluno', $nome_aluno);
        $stmt->bindParam(':simade', $simade);
        $stmt->bindParam(':data_nascimento', $data_nascimento);
        $stmt->bindParam(':data_ocorrencia', $data_ocorrencia);
        $stmt->bindParam(':horario_ocorrencia', $horario_ocorrencia);
        $stmt->bindParam(':turma', $turma);
        $stmt->bindParam(':materia', $materia);
        $stmt->bindParam(':professor', $professor);
        $stmt->bindParam(':infracoes', $infracoes_string);
        $stmt->bindParam(':descricao', $descricao);

        $stmt->execute();

        echo "<h2>Ocorrência salva no banco de dados com sucesso!</h2>";
        echo "<br><a href='index.html'>Voltar para o formulário</a>";

    } catch (PDOException $e) {
        echo "Erro ao salvar no banco de dados: " . $e->getMessage();
    }

} else {
    header("Location: index.html");
    exit;
}
?>