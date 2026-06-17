<?php
$diretorio = 'uploads/';

if (!is_dir($diretorio)) {
    echo "<p>A pasta <strong>uploads/</strong> não existe.</p>";
    exit;
}

$arquivos = scandir($diretorio);
$arquivos = array_diff($arquivos, ['.', '..']); // Remove . e ..

echo "<h2>Arquivos na pasta 'uploads/'</h2>";

if (empty($arquivos)) {
    echo "<p>Nenhum arquivo encontrado.</p>";
} else {
    echo "<ul>";
    foreach ($arquivos as $arquivo) {
        $caminho = $diretorio . $arquivo;
        echo "<li><a href='$caminho' target='_blank'>" . htmlspecialchars($arquivo) . "</a></li>";
    }
    echo "</ul>";
}
?>
