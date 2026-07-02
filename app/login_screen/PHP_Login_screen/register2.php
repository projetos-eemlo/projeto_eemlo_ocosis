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

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $masp_com_mascara = trim($_POST['masp'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $id_tipo_func = $_POST['id_tipo_func'] ?? '';

    // LIMPEZA: Tira o traço para gravar apenas os 8 números no banco!
    $masp_limpo = str_replace('-', '', $masp_com_mascara);

    if (empty($masp_limpo) || empty($senha) || empty($id_tipo_func)){
        echo "<script>alert('Preencha todos os campos.'); window.history.back();</script>";
        exit;
    } else if (strlen($masp_limpo) !== 8) {
        echo "<script>alert('O MASP deve conter exatamente 8 dígitos válidos.'); window.history.back();</script>";
        exit;
    } else {
        try {

            $stmtCargo = $pdo->prepare("SELECT desc_funcionario FROM tipo_func WHERE id_tipo_func = ?");
            $stmtCargo->execute([$id_tipo_func]);
            $cargoResultado = $stmtCargo->fetch(PDO::FETCH_ASSOC);

if ($cargoResultado) {
                $cargo_nome = $cargoResultado['desc_funcionario'];
            } else {
                echo "<script>alert('Cargo inválido.'); window.history.back();</script>";
                exit;
            }

            $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

            // Gravando o $masp_limpo (8 caracteres) e preservando a regra do seu VARCHAR(8)
            $sql = "INSERT INTO funcionarios (id_tipo_func, masp, senha_hash, cargo_funcionario) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_tipo_func, $masp_limpo, $hashSenha, $cargo_nome]); 
    
            echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = '../login.html';</script>";
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                echo "<script>alert('Este MASP já está cadastrado no sistema.'); window.history.back();</script>";
            } else {
                echo "<script>alert('Erro ao cadastrar: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
            }
        }
    }
}
?>