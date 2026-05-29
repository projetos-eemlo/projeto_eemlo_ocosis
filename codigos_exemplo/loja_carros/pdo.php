<?php
//$pdo = new PDO('mysql:host=localhost;port=3306;dbname=misc', 'fred', 'zap');
// See the "errors" folder for details...

//$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$host = 'localhost';
$dbname = 'misc';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

 } catch (PDOException $e) {
     die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>
