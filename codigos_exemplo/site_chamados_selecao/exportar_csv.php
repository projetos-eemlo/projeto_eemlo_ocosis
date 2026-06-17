<?php
require 'conexao.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=denuncias.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Código', 'Tipo', 'Local', 'Descrição', 'Status', 'Resposta', 'Data']);

$result = $conn->query("SELECT codigo, tipo, local, descricao, status, resposta, data_envio FROM denuncias");

while ($row = $result->fetch_assoc()) {
  fputcsv($output, $row);
}

fclose($output);
?>
