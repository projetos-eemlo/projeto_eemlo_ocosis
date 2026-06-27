<?php 
$host = 'localhost'; 
$dbname = 'minha_loja';
 $user = 'root'; 
 $pass = 'senha_segura'; 


 try { $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); 

 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

 echo "Conexão estabelecida com sucesso!"; }

  catch (PDOException $e) 
  
  { die("Erro de conexão: " . $e->getMessage()); } 
  
?> 