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

            // Pega os dados exatos do banco para passar para o HTML
            $masp_limpo = $funcionario['masp'];
            $cargo_nome = $funcionario['cargo_funcionario'];

            // ECOA O SCRIPT QUE SALVA NO NAVEGADOR E REDIRECIONA ENTRANDO NA PASTA DELE
            echo "<script>
                localStorage.setItem('masp_logado', '$masp_limpo');
                localStorage.setItem('cargo_logado', '$cargo_nome');
                
                // Caminho corrigido recuando duas pastas e entrando em MANTER_OCORRENCIAS
                window.location.href = '../../MANTER_OCORRENCIAS/index.html';
            </script>";
            exit;

            // Redireciona para o Painel
            header("Location: ../login.html");
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