<?php
session_start();
$host = '127.0.0.1';
$dbname = 'ocosisteste'; 
$user = 'root'; 
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage()); 
}

// CONSERTO AQUI: Mudado de 'masp_logado' para 'funcionario_masp'
if(!isset($_SESSION['funcionario_masp'])) {
    echo "<script>alert('Você precisa fazer login primeiro!'); window.location.href = '../login_screen/login.html';</script>";
    exit; 
}

// Se chegou até aqui, é porque está logado! Ajustado os nomes abaixo também:
$masp_do_usuario = $_SESSION['funcionario_masp'];
$cargo_do_usuario = $_SESSION['cargo_funcionario'];


// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Coleta e limpa os dados
    $nome_aluno       = htmlspecialchars(trim($_POST['nome_aluno']));
    $simade           = htmlspecialchars(trim($_POST['simade']));
    $data_nascimento  = $_POST['data_nascimento'];
    $data_ocorrencia  = $_POST['data_ocorrencia'];
    $horario_ocorrencia = $_POST['horario_ocorrencia'];
    $turma            = $_POST['turma'];
    $materia          = htmlspecialchars(trim($_POST['materia']));
    $professor        = htmlspecialchars(trim($_POST['professor']));
    $descricao        = htmlspecialchars(trim($_POST['descricao']));
    
    // Processa os checkboxes de infrações
    $infracoes_array = isset($_POST['infracoes']) ? $_POST['infracoes'] : [];
    $outro_tipo      = htmlspecialchars(trim($_POST['outro_tipo']));
    
    if (!empty($outro_tipo)) {
        $infracoes_array[] = "Outros: " . $outro_tipo;
    }
    
    // Transforma o array de infrações em uma única string separada por vírgulas para o banco
    $infracoes_string = implode(", ", $infracoes_array);

    // Validação simples de campos obrigatórios
    if (empty($nome_aluno) || empty($simade) || empty($data_ocorrencia) || empty($turma)) {
        die("Por favor, preencha todos os campos obrigatórios.");
    }

    try {
        // 2. Prepara o comando SQL (PreparedStatement evita Injeção de SQL)
        $sql = "INSERT INTO ocorrencias 
                (nome_aluno, simade, data_nascimento, data_ocorrencia, horario_ocorrencia, turma, materia, professor, infracoes, descricao) 
                VALUES 
                (:nome_aluno, :simade, :data_nascimento, :data_ocorrencia, :horario_ocorrencia, :turma, :materia, :professor, :infracoes, :descricao)";
        
        $stmt = $pdo->prepare($sql);

        // 3. Vincula os valores aos parâmetros do SQL
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

        // 4. Executa o comando
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