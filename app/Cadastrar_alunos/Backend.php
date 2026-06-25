<?php
header('Content-Type: application/json');

// ==========================================
// CONEXÃO COM O BANCO DE DADOS (PDO)
// ==========================================
$host = 'localhost';
$dbname = 'sistema_ocorrencia';
$user = 'root'; // Usuário padrão do WAMP
$pass = '';     // Senha padrão do WAMP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Configura o PDO para lançar exceções em caso de erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha na conexão com o banco: ' . $e->getMessage()]);
    exit;
}

$acao = isset($_POST['acao']) ? $_POST['acao'] : '';

switch ($acao) {
    
    // ==========================================
    // BUSCAR TURMAS PARA O SELECT E MODAL
    // ==========================================
    case 'listar_turmas':
        try {
            // Busca as turmas com todos os detalhes necessários, incluindo trimestre
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
    // UPLOAD E EXTRAÇÃO DE DADOS DO CSV
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
                    // Verifica se a linha tem as 3 colunas exigidas
                    if (count($data) >= 3) {
                        // Converte a data do formato BR (DD/MM/YYYY) para SQL (YYYY-MM-DD)
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
            echo json_encode(['sucesso' => false, 'erro' => 'Nenhum arquivo recebido pelo servidor.']);
        }
        break;

    // ==========================================
    // SALVAR ALUNOS NO BANCO DE DADOS
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

        // Prepara a query de inserção
        $sql = "INSERT INTO alunos (id_turma, nome_aluno, num_simade, dt_nascimento) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // Percorre o array de alunos enviados pelo JS
        foreach ($alunos_selecionados as $aluno) {
            try {
                $stmt->execute([$id_turma, $aluno['nome'], $aluno['simade'], $aluno['nascimento']]);
                $sucessos++;
            } catch (PDOException $e) {
                // Código 23000 = Violação de Constraint (UNIQUE KEY do SIMADE)
                if ($e->getCode() == 23000) {
                    $erros_simade[] = $aluno['nome'] . " (SIMADE já cadastrado)";
                } else {
                    $erros_simade[] = "Erro crítico em: " . $aluno['nome'];
                }
            }
        }

        // Retornos personalizados baseados no resultado da operação
        if ($sucessos > 0 && count($erros_simade) == 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => "$sucessos aluno(s) cadastrado(s) com sucesso na turma!"]);
        } else if ($sucessos > 0 && count($erros_simade) > 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => "$sucessos aluno(s) salvo(s). Falha nos seguintes: " . implode(", ", $erros_simade)]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => "Nenhum aluno foi salvo. Motivos: " . implode(", ", $erros_simade)]);
        }
        break;

    // ==========================================
    // DEFAULT
    // ==========================================
    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida ou não especificada.']);
        break;
}
?>