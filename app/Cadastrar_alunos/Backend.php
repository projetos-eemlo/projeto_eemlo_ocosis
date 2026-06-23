<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'sistema_ocorrencia';
$user = 'root'; 
$pass = '';     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha na conexão com o banco: ' . $e->getMessage()]);
    exit;
}

$acao = isset($_POST['acao']) ? $_POST['acao'] : '';

switch ($acao) {
    
    // ==========================================
    // BUSCAR TURMAS (Agora trazendo dados completos)
    // ==========================================
    case 'listar_turmas':
        try {
            $sql = "SELECT id_turma, desc_turma, turno, ano_letivo, semestre_letivo 
                    FROM turma 
                    ORDER BY desc_turma ASC, ano_letivo DESC, semestre_letivo DESC";
            $stmt = $pdo->query($sql);
            $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $turmas]);
        } catch (PDOException $e) {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao buscar turmas.']);
        }
        break;

    // ==========================================
    // UPLOAD E LEITURA DO CSV
    // ==========================================
    case 'upload_csv':
        if (isset($_FILES['arquivo_csv'])) {
            $file = $_FILES['arquivo_csv'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['sucesso' => false, 'erro' => 'Erro no upload.']);
                exit;
            }

            if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
                echo json_encode(['sucesso' => false, 'erro' => 'Envie um arquivo .csv']);
                exit;
            }

            $alunos = [];
            if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) >= 3) {
                        $dataBR = trim($data[2]);
                        $dataSQL = implode('-', array_reverse(explode('/', $dataBR)));

                        $alunos[] = [
                            'nome' => trim($data[0]),
                            'simade' => trim($data[1]),
                            'nascimento' => $dataSQL 
                        ];
                    }
                }
                fclose($handle);
            }
            echo json_encode(['sucesso' => true, 'dados' => $alunos]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Nenhum arquivo.']);
        }
        break;

    // ==========================================
    // SALVAR ALUNOS SELECIONADOS NO BANCO
    // ==========================================
    case 'salvar_alunos_csv':
        $id_turma = isset($_POST['id_turma']) ? (int)$_POST['id_turma'] : 0;
        $alunos_json = isset($_POST['alunos']) ? $_POST['alunos'] : '[]';
        $alunos_selecionados = json_decode($alunos_json, true);

        if ($id_turma === 0 || empty($alunos_selecionados)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Turma inválida ou nenhum aluno selecionado.']);
            exit;
        }

        $sucessos = 0;
        $erros_simade = [];

        $sql = "INSERT INTO alunos (id_turma, nome_aluno, num_simade, dt_nascimento) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($alunos_selecionados as $aluno) {
            try {
                $stmt->execute([$id_turma, $aluno['nome'], $aluno['simade'], $aluno['nascimento']]);
                $sucessos++;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $erros_simade[] = $aluno['nome'] . " (SIMADE duplicado)";
                } else {
                    $erros_simade[] = "Erro em: " . $aluno['nome'];
                }
            }
        }

        if ($sucessos > 0 && count($erros_simade) == 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => "$sucessos aluno(s) cadastrado(s) com sucesso!"]);
        } else if ($sucessos > 0 && count($erros_simade) > 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => "$sucessos salvo(s). Falhas: " . implode(", ", $erros_simade)]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => "Nenhum aluno foi salvo. Erros: " . implode(", ", $erros_simade)]);
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida.']);
        break;
}
?>