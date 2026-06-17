<?php
require 'conexao.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;

$html = '<h2>Relatório de Denúncias</h2>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">
<thead>
<tr>
  <th>Código</th><th>Tipo</th><th>Local</th><th>Status</th><th>Resposta</th><th>Data</th>
</tr>
</thead><tbody>';

$result = $conn->query("SELECT codigo, tipo, local, status, resposta, data_envio FROM denuncias");
while ($row = $result->fetch_assoc()) {
  $html .= "<tr>
              <td>{$row['codigo']}</td>
              <td>{$row['tipo']}</td>
              <td>{$row['local']}</td>
              <td>" . ($row['status'] ?: 'Pendente') . "</td>
              <td>" . ($row['resposta'] ?: '-') . "</td>
              <td>{$row['data_envio']}</td>
            </tr>";
}
$html .= '</tbody></table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('relatorio_denuncias.pdf', ['Attachment' => false]);
?>
