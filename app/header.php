<?php
// Configura o caminho base para encontrar os arquivos dependendo de qual pasta estamos
$base = isset($base_path) ? $base_path : '../';
// Descobre qual é a página atual para acender o botão certo
$pagina = isset($pagina_atual) ? $pagina_atual : '';

// Simulação de pendentes (depois você puxa isso do banco)
$totalPendentesGlobal = isset($totalPendentesGlobal) ? $totalPendentesGlobal : 5; 
?>

<link rel="stylesheet" href="<?= $base ?>CSSNAV/Nav.css">

<nav class="unified-navbar">
    <div class="nav-left">
    <div class="logo-box">
        <img src="<?= $base ?>../assets/logo.png" alt="Logo E.E. Maria de Lourdes de Oliveira" class="logo-img">
    </div>
        <a href="<?= $base ?>Manter_Ocorrencias/index.html" class="nav-link <?= ($pagina == 'nova_ocorrencia') ? 'active' : '' ?>">Nova Ocorrência</a>
        
        <a href="<?= $base ?>turmas_page/turmas.php" class="nav-link <?= ($pagina == 'turmas') ? 'active' : '' ?>">Pesquisa e Turmas</a>
        
        <a href="<?= $base ?>Cadastrar_alunos/Cadastrar_alunos.html" class="nav-link <?= ($pagina == 'cadastrar_alunos') ? 'active' : '' ?>">Cadastrar Alunos</a>
        
        
        <a href="<?= $base ?>visualizar_relatorio/pendentes.php" class="nav-link <?= ($pagina == 'pendentes') ? 'active' : '' ?>">
            Ocorrências Pendentes <span class="unified-badge"><?= $totalPendentesGlobal ?></span>
        </a>
    </div>
    
    <div class="nav-right">
        <form method="POST" action="<?= $base ?>login_screen/PHP_Login_screen/logout.php" style="margin:0;">
            <button type="submit" class="unified-btn-sair">Sair</button>
        </form>
    </div>
</nav>