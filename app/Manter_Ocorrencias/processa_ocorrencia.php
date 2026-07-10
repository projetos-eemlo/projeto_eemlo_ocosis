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

// Verifica se o funcionário está devidamente autenticado
if(!isset($_SESSION['funcionario_masp'])) {
    echo "<script>alert('Você precisa fazer login primeiro!'); window.location.href = '../login_screen/login.html';</script>";
    exit; 
}

// Busca o id_funcionario baseado no MASP da sessão
$stmtFunc = $pdo->prepare("SELECT id_funcionario FROM funcionarios WHERE masp = ?");
$stmtFunc->execute([$_SESSION['funcionario_masp']]);
$funcionario = $stmtFunc->fetch(PDO::FETCH_ASSOC);

if (!$funcionario) {
    die("Erro: Funcionário não encontrado no sistema.");
}
$id_funcionario_logado = $funcionario['id_funcionario'];

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Coleta e limpa os dados do Aluno
    $nome_aluno       = htmlspecialchars(trim($_POST['nome_aluno']));
    $simade           = htmlspecialchars(trim($_POST['simade']));
    $data_nascimento  = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null;
    
    // Coleta os dados da Ocorrência
    $data_ocorrencia  = !empty($_POST['data_ocorrencia']) ? $_POST['data_ocorrencia'] : date('Y-m-d');
    $horario          = $_POST['horario_ocorrencia']; 
    $id_turma         = intval($_POST['turma']); // Recebe o ID numérico vindo do HTML
    $disciplina       = htmlspecialchars(trim($_POST['materia'])); 
    $desc_ocorrencia  = htmlspecialchars(trim($_POST['descricao'])); 
    
    // Processa os checkboxes de infrações
    $infracoes_array = isset($_POST['infracoes']) ? $_POST['infracoes'] : [];
    $outro_tipo      = htmlspecialchars(trim($_POST['outro_tipo'] ?? ''));
    
    if (!empty($outro_tipo)) {
        $infracoes_array[] = "Outros: " . $outro_tipo;
    }
    
    $infracoes_string = implode(", ", $infracoes_array);

    // Validação de campos obrigatórios
    if (empty($nome_aluno) || empty($simade) || empty($data_ocorrencia) || empty($id_turma)) {
        die("Por favor, preencha todos os campos obrigatórios.");
    }

    try {
        $pdo->beginTransaction();

        // PASSO 1: Garantir que o aluno exista na tabela de alunos (Ajustado com colunas reais)
        $stmtAlunoCheck = $pdo->prepare("SELECT id_aluno FROM alunos WHERE num_simade = ?");
        $stmtAlunoCheck->execute([$simade]);
        $alunoExistente = $stmtAlunoCheck->fetch(PDO::FETCH_ASSOC);

        if ($alunoExistente) {
            $id_aluno = $alunoExistente['id_aluno'];
        } else {
            // Inserção usando os nomes exatos: nome_aluno, num_simade, dt_nascimento
            $stmtInsertAluno = $pdo->prepare("INSERT INTO alunos (nome_aluno, num_simade, dt_nascimento, id_turma) VALUES (?, ?, ?, ?)");
            $stmtInsertAluno->execute([$nome_aluno, $simade, $data_nascimento, $id_turma]);
            $id_aluno = $pdo->lastInsertId();
        }

        // PASSO 2: Inserir na tabela ocorrencias
        $sql = "INSERT INTO ocorrencias 
                (id_aluno, id_funcionario, id_turma, id_tipo_infracao, data_ocorrencia, horario, disciplina, desc_ocorrencia, data_registro_sistema) 
                VALUES 
                (:id_aluno, :id_funcionario, :id_turma, :id_tipo_infracao, :data_ocorrencia, :horario, :disciplina, :desc_ocorrencia, NOW())";
        
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':id_aluno', $id_aluno, PDO::PARAM_INT);
        $stmt->bindParam(':id_funcionario', $id_funcionario_logado, PDO::PARAM_INT);
        $stmt->bindParam(':id_turma', $id_turma, PDO::PARAM_INT);
        $stmt->bindParam(':id_tipo_infracao', $infracoes_string); 
        $stmt->bindParam(':data_ocorrencia', $data_ocorrencia);
        $stmt->bindParam(':horario', $horario);
        $stmt->bindParam(':disciplina', $disciplina);
        $stmt->bindParam(':desc_ocorrencia', $desc_ocorrencia);

        $stmt->execute();
        $pdo->commit();

        echo "<script>
                alert('Ocorrência salva com sucesso!');
                window.location.href = 'index.html';
              </script>";

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Erro ao salvar no banco de dados: " . $e->getMessage();
    }

} else {
    header("Location: index.html");
    exit;
}
?>