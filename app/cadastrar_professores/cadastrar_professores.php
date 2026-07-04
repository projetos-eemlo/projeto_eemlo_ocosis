<?php
header('Content-Type: application/json');

$host = '127.0.0.1';
$dbname = 'ocosis'; 
$user = 'root'; 
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha na conexão: ' . $e->getMessage()]);
    exit;
}

$acao = isset($_POST['acao']) ? $_POST['acao'] : '';

switch ($acao) {
    case 'listar_professores':
        try {
            // Busca os professores já cadastrados para mostrar no ecrã principal (id_tipo_func = 6)
            $sql = "SELECT nome_func AS nome, masp FROM funcionarios WHERE id_tipo_func = 6 ORDER BY nome_func ASC";
            $stmt = $pdo->query($sql);
            $professores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $professores]);
        } catch (PDOException $e) {
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao buscar professores: ' . $e->getMessage()]);
        }
        break;

    case 'upload_csv':
        if (isset($_FILES['arquivo_csv'])) {
            $file = $_FILES['arquivo_csv'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['sucesso' => false, 'erro' => 'Erro no upload.']);
                exit;
            }

            $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (strtolower($extensao) !== 'csv') {
                echo json_encode(['sucesso' => false, 'erro' => 'Envie um ficheiro .csv válido.']);
                exit;
            }

            $professores = [];
            if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) >= 2) {
                        $maspBruto = trim($data[0]);
                        $nome = trim($data[1]);
                        
                        // Garante que o MASP será lido limpo, quer venha com ou sem hífen no CSV
                        $maspLimpo = preg_replace('/\D/', '', $maspBruto);

                        if (strlen($maspLimpo) === 8 && !empty($nome)) {
                            $professores[] = [
                                'masp' => $maspLimpo,
                                'nome' => $nome
                            ];
                        }
                    }
                }
                fclose($handle);
            }
            
            if (count($professores) > 0) {
                echo json_encode(['sucesso' => true, 'dados' => $professores]);
            } else {
                echo json_encode(['sucesso' => false, 'erro' => 'Nenhum professor válido encontrado. (A estrutura do CSV deve ser: MASP, Nome)']);
            }
        } else {
            echo json_encode(['sucesso' => false, 'erro' => 'Nenhum ficheiro recebido.']);
        }
        break;

    case 'salvar_professores_csv':
        $professores_json = isset($_POST['professores']) ? $_POST['professores'] : '[]';
        $professores_selecionados = json_decode($professores_json, true);

        if (empty($professores_selecionados)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Nenhum professor para guardar.']);
            exit;
        }

        $sucessos = 0;
        $erros = [];

        $sql = "INSERT INTO funcionarios (id_tipo_func, masp, nome_func, senha_hash, cargo_funcionario) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($professores_selecionados as $prof) {
            $masp = $prof['masp'];
            $nome = $prof['nome'];
            
            // As suas regras automatizadas:
            $id_tipo_func = 6; 
            $cargo = 'Professor';
            $senha_hash = password_hash($masp, PASSWORD_DEFAULT);

            try {
                $stmt->execute([$id_tipo_func, $masp, $nome, $senha_hash, $cargo]);
                $sucessos++;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $erros[] = "MASP $masp já pertence a outro registo.";
                } else {
                    $erros[] = "Erro no MASP $masp: Falha de banco de dados.";
                }
            }
        }

        if ($sucessos > 0 && count($erros) == 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => "$sucessos professor(es) registado(s) com sucesso!"]);
        } else if ($sucessos > 0 && count($erros) > 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => "$sucessos registado(s). Problemas ignorados: " . implode(" | ", $erros)]);
        } else {
            echo json_encode(['sucesso' => false, 'erro' => "Falha no registo: " . implode(" | ", $erros)]);
        }
        break;

    case 'excluir_professores':
        $masps_json = isset($_POST['masps']) ? $_POST['masps'] : '[]';
        $masps = json_decode($masps_json, true);

        if (empty($masps)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Nenhum professor selecionado.']);
            exit;
        }

        try {
            $placeholders = implode(',', array_fill(0, count($masps), '?'));
            $sql = "DELETE FROM funcionarios WHERE masp IN ($placeholders) AND id_tipo_func = 6";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($masps);

            echo json_encode(['sucesso' => true, 'mensagem' => count($masps) . ' professor(es) removido(s) do sistema!']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                 echo json_encode(['sucesso' => false, 'erro' => 'Não pode excluir professores que já estejam vinculados a disciplinas ou ocorrências.']);
            } else {
                 echo json_encode(['sucesso' => false, 'erro' => 'Erro ao excluir do banco de dados.']);
            }
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida.']);
        break;
}
?>