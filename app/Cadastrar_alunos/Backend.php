<?php
header('Content-Type: application/json');

// ==========================================
// CONEXÃO COM O BANCO DE DADOS (PDO)
// ==========================================
$host = 'localhost';
$dbname = 'ocosis'; // O nome do seu banco de dados
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
    // BUSCAR TURMAS
    // ==========================================
    case 'listar_turmas':
        try {
            $sql = "SELECT id_turma, desc_turma, turno, ano_letivo, semestre_letivo, trimestre_letivo 
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
    // UPLOAD E TRATAMENTO DO CSV
    // ==========================================
    case 'upload_csv':
        if (isset($_FILES['arquivo_csv'])) {
            $file = $_FILES['arquivo_csv'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['sucesso' => false, 'erro' => 'Erro ao fazer o upload do arquivo.']);
                exit;
            }

            $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (strtolower($extensao) !== 'csv') {
                echo json_encode(['sucesso' => false, 'erro' => 'Formato inválido. Envie um arquivo .csv']);
                exit;
            }

            $alunos = [];
            if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) >= 3) {
                        
                        $dataCrua = trim($data[2]);
                        $dataSQL = $dataCrua; 

                        // TRADUTOR DE DATAS: Garante o formato YYYY-MM-DD para o MySQL
                        if (strpos($dataCrua, '/') !== false) {
                            $partes = explode('/', $dataCrua);
                            if (strlen($partes[0]) == 4) {
                                $dataSQL = $partes[0] . '-' . $partes[1] . '-' . $partes[2];
                            } else if (strlen($partes[2]) == 4) {
                                $dataSQL = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
                            }
                        } else if (strpos($dataCrua, '-') !== false) {
                            $partes = explode('-', $dataCrua);
                            if (strlen($partes[2]) == 4) {
                                $dataSQL = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
                            }
                        }

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
            echo json_encode(['sucesso' => false, 'erro' => 'Nenhum arquivo recebido pelo servidor.']);
        }
        break;

    // ==========================================
    // SALVAR ALUNOS NO BANCO DE DADOS (CORREÇÃO DE CONSTRAINTS)
    // ==========================================
    case 'salvar_alunos_csv':
        $id_turma = isset($_POST['id_turma']) ? (int)$_POST['id_turma'] : 0;
        $alunos_json = isset($_POST['alunos']) ? $_POST['alunos'] : '[]';
        $alunos_selecionados = json_decode($alunos_json, true);

        if ($id_turma === 0 || empty($alunos_selecionados)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Operação cancelada: Nenhuma turma válida foi identificada.']);
            exit;
        }

        $sucessos = 0;
        $erros_detalhados = [];

        $sql = "INSERT INTO alunos (id_turma, nome_aluno, num_simade, dt_nascimento) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($alunos_selecionados as $aluno) {
            $nomeAluno = isset($aluno['nome']) ? $aluno['nome'] : '';
            $numSimade = isset($aluno['simade']) ? $aluno['simade'] : '';
            $dtNascimento = isset($aluno['nascimento']) ? $aluno['nascimento'] : '';

            try {
                $stmt->execute([$id_turma, $nomeAluno, $numSimade, $dtNascimento]);
                $sucessos++;
            } catch (PDOException $e) {
                // Rastreia o erro exato sem deduzir respostas falsas
                if ($e->getCode() == 23000) {
                    if (strpos($e->getMessage(), 'foreign key') !== false || strpos($e->getMessage(), 'Constraint') !== false) {
                        $erros_detalhados[] = "A turma com ID $id_turma não foi encontrada no banco de dados.";
                    } else {
                        $erros_detalhados[] = $nomeAluno . " (O número de SIMADE $numSimade já pertence a outro registo ativo).";
                    }
                } else {
                    $erros_detalhados[] = "Falha crítica no banco: " . $e->getMessage();
                }
            }
        }

        if ($sucessos > 0 && count($erros_detalhados) == 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => "$sucessos aluno(s) salvo(s) com sucesso!"]);
        } else if ($sucessos > 0 && count($erros_detalhados) > 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => "$sucessos salvo(s). Problemas encontrados: " . implode(", ", $erros_detalhados)]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => "Falha na gravação. Motivo: " . implode(", ", $erros_detalhados)]);
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida.']);
        break;
}
?>