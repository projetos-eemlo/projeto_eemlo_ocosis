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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $masp_com_mascara = trim($_POST['masp'] ?? '');
    $senha_digitada = $_POST['senha'] ?? '';

    // LIMPEZA: Remove o traço da tentativa de login
    $masp_limpo = str_replace('-', '', $masp_com_mascara);

    if (!empty($masp_limpo) && !empty($senha_digitada)) {
        
        $sql = "SELECT * FROM funcionarios WHERE masp = :masp LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['masp' => $masp_limpo]);
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($funcionario && password_verify($senha_digitada, $funcionario['senha_hash'])) {
            
            $_SESSION['funcionario_id'] = $funcionario['id_funcionario']; 
            $_SESSION['funcionario_masp'] = $funcionario['masp']; 
            $_SESSION['cargo_funcionario'] = $funcionario['cargo_funcionario']; 
            
            // Redireciona para o Painel
            header("Location: ../cadastro.html");
            exit;
 
        } else {
            header("Location: ../login.html?erro=1");
            exit;
        }
    } else {
        header("Location: ../login.html?erro=1");
        exit;
    }
    
}
?>