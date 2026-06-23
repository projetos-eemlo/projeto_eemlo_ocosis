<?php
// Define que o retorno será sempre em JSON para o JavaScript
header('Content-Type: application/json');

// Pega a 'acao' que o JavaScript enviou para saber o que deve ser feito
$acao = isset($_POST['acao']) ? $_POST['acao'] : '';

switch ($acao) {
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
                // Lê linha por linha separada por vírgula
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) >= 3) {
                        $alunos[] = [
                            'nome' => trim($data[0]),
                            'simade' => trim($data[1]),
                            'nascimento' => trim($data[2])
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

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação inválida ou não especificada.']);
        break;
}
?>