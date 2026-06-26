<?php
/**
 * OCOSIS - Visualização do Relatório do Aluno (Dinamizado)
 * Responsável: Última Etapa do Projeto — Visualizar Relatório
 */

// 1. CONFIGURAÇÃO DA CONEXÃO COM O BANCO DE DADOS
// Se o seu grupo já tiver um arquivo central de conexão (ex: conexao.php),
// comente as linhas abaixo e use apenas: include_once("conexao.php");
$db_host = "localhost";
$db_name = "ocosis_db"; // Substitua pelo nome real do banco de dados do grupo
$db_user = "root";
$db_pass = "";

// 2. CAPTURA O ID DO ALUNO PELA URL (Ex: perfil.php?id=5)
// Se não vier nenhum ID válido, o sistema interrompe para não quebrar a tela
$aluno_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($aluno_id === 0) {
    die("<div style='padding:20px; font-family:sans-serif;'><h3>Erro: Nenhum ID de aluno foi especificado para gerar o relatório.</h3><a href='pendentes.php'>Voltar para Pendentes</a></div>");
}

// Flag usada só para mostrar o aviso de "modo de demonstração" na tela
$modoDemonstracao = false;

try {
    // 3. TENTATIVA DE CONEXÃO REAL + BUSCA DOS DADOS NO BANCO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3a. DADOS CADASTRAIS DO ALUNO
    $sqlAluno = $pdo->prepare("SELECT id, nome, simade, DATE_FORMAT(data_nascimento, '%d/%m/%Y') AS nascimento, turma_atual FROM alunos WHERE id = :id");
    $sqlAluno->execute(['id' => $aluno_id]);
    $aluno = $sqlAluno->fetch(PDO::FETCH_ASSOC);

    if (!$aluno) {
        die("<div style='padding:20px; font-family:sans-serif;'><h3>Erro: Aluno não encontrado no banco de dados.</h3><a href='pendentes.php'>Voltar</a></div>");
    }

    // 3b. MÉTRICAS PARA O CARD "RESUMO DO ANO LETIVO" (Cálculos Automáticos)
    $sqlTotal = $pdo->prepare("SELECT COUNT(*) as total FROM ocorrencias WHERE aluno_id = :id");
    $sqlTotal->execute(['id' => $aluno_id]);
    $totalOcorrencias = $sqlTotal->fetch(PDO::FETCH_ASSOC)['total'];

    $sqlPendentes = $pdo->prepare("SELECT COUNT(*) as total FROM ocorrencias WHERE aluno_id = :id AND status = 'pendente'");
    $sqlPendentes->execute(['id' => $aluno_id]);
    $totalPendentes = $sqlPendentes->fetch(PDO::FETCH_ASSOC)['total'];

    $sqlReincidente = $pdo->prepare("SELECT infracoes_texto, COUNT(*) as qtd FROM ocorrencias WHERE aluno_id = :id GROUP BY infracoes_texto ORDER BY qtd DESC LIMIT 1");
    $sqlReincidente->execute(['id' => $aluno_id]);
    $resReincidente = $sqlReincidente->fetch(PDO::FETCH_ASSOC);
    $maisReincidente = $resReincidente ? $resReincidente['infracoes_texto'] : 'Nenhuma infração registrada';

    // 3c. HISTÓRICO DE OCORRÊNCIAS DO ALUNO
    $sqlHistorico = $pdo->prepare("SELECT id, DATE_FORMAT(data_registro, '%d/%m/%Y') as data_formatada, TIME_FORMAT(horario, '%H:%i') as hora_formatada, materia_professor, infracoes_ids, infracoes_texto, status, notif_responsavel FROM ocorrencias WHERE aluno_id = :id ORDER BY data_registro DESC, horario DESC");
    $sqlHistorico->execute(['id' => $aluno_id]);
    $historicoOcorrencias = $sqlHistorico->fetchAll(PDO::FETCH_ASSOC);

    // 3d. BADGE DA NAVBAR GLOBAL (Contador geral do sistema)
    $sqlBadgeGlobal = $pdo->query("SELECT COUNT(*) as total FROM ocorrencias WHERE status = 'pendente'");
    $totalPendentesGlobal = $sqlBadgeGlobal->fetch(PDO::FETCH_ASSOC)['total'];

} catch (PDOException $e) {
    /*
     * Não foi possível conectar ao banco real (extensão pdo_mysql
     * desabilitada, ou o banco "ocosis_db" do grupo ainda não está
     * acessível neste ambiente). Em vez de travar a tela, caímos
     * para dados fictícios — só para visualização/teste.
     *
     * As queries reais ficam intactas lá em cima e voltam a ser
     * usadas automaticamente assim que a conexão funcionar. Você
     * não precisa alterar nada quando o banco do grupo estiver pronto.
     */
    $modoDemonstracao = true;

    $mockAlunos = [
        101 => ['id' => 101, 'nome' => 'Fernanda Lima',      'simade' => '20231101', 'nascimento' => '12/03/2011', 'turma_atual' => '2º Ano B'],
        102 => ['id' => 102, 'nome' => 'Maria Eduarda',      'simade' => '20231102', 'nascimento' => '05/07/2012', 'turma_atual' => '1º Ano A'],
        103 => ['id' => 103, 'nome' => 'Ricardo Souza',      'simade' => '20231103', 'nascimento' => '22/11/2012', 'turma_atual' => '1º Ano A'],
        104 => ['id' => 104, 'nome' => 'Alessandra Vieira',  'simade' => '20231104', 'nascimento' => '14/02/2012', 'turma_atual' => '1º Ano A'],
        105 => ['id' => 105, 'nome' => 'João Silva Sauro',   'simade' => '20231105', 'nascimento' => '30/09/2012', 'turma_atual' => '1º Ano A'],
    ];

    $mockOcorrencias = [
        101 => [['id' => 1, 'data_formatada' => '15/05/2026', 'hora_formatada' => '10:00', 'materia_professor' => 'Inglês / Prof. William',    'infracoes_ids' => '7, 8', 'infracoes_texto' => 'Chegou atrasado, após o horário de entrada permitido; Fez uso do celular ou outro aparelho eletrônico durante as aulas', 'status' => 'pendente', 'notif_responsavel' => 1]],
        102 => [['id' => 2, 'data_formatada' => '10/05/2026', 'hora_formatada' => '09:15', 'materia_professor' => 'Português / Profª Sandra',   'infracoes_ids' => '8, 2', 'infracoes_texto' => 'Fez uso do celular ou outro aparelho eletrônico durante as aulas; Desrespeitou o(a) professor(a)',                          'status' => 'pendente', 'notif_responsavel' => 1]],
        103 => [['id' => 3, 'data_formatada' => '28/04/2026', 'hora_formatada' => '11:40', 'materia_professor' => 'Química / Prof. Eduardo',    'infracoes_ids' => '3',    'infracoes_texto' => 'Agrediu o(a) colega',                                                                                       'status' => 'pendente', 'notif_responsavel' => 1]],
        104 => [['id' => 4, 'data_formatada' => '20/04/2026', 'hora_formatada' => '08:30', 'materia_professor' => 'Matemática / Prof. Marcos',  'infracoes_ids' => '2',    'infracoes_texto' => 'Desrespeitou o(a) professor(a)',                                                                            'status' => 'pendente', 'notif_responsavel' => 1]],
        105 => [['id' => 5, 'data_formatada' => '15/04/2026', 'hora_formatada' => '13:50', 'materia_professor' => 'Física / Prof. Carlos',      'infracoes_ids' => '8',    'infracoes_texto' => 'Fez uso do celular ou outro aparelho eletrônico durante as aulas',                                          'status' => 'pendente', 'notif_responsavel' => 1]],
    ];

    $aluno = $mockAlunos[$aluno_id] ?? null;

    if (!$aluno) {
        die("<div style='padding:20px; font-family:sans-serif;'><h3>Erro: Aluno não encontrado (modo de demonstração).</h3><p>Sem conexão com o banco real — use um dos IDs de teste: 101 a 105.</p><a href='pendentes.php'>Voltar</a></div>");
    }

    $historicoOcorrencias = $mockOcorrencias[$aluno_id] ?? [];
    $totalOcorrencias      = count($historicoOcorrencias);
    $totalPendentes        = count(array_filter($historicoOcorrencias, fn($o) => $o['status'] === 'pendente'));
    $maisReincidente       = $historicoOcorrencias[0]['infracoes_texto'] ?? 'Nenhuma infração registrada';
    $totalPendentesGlobal  = 5; // mesmo total fixo usado na tela de Pendentes
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Aluno · Ocorrências</title>
    <style>
        /* ── RESET & BASE ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f0f2f5; color: #2d3748; min-height: 100vh;
        }
        /* ── NAVBAR ──────────────────────────────────────── */
        .navbar {
            background: #1a56db; height: 56px; display: flex; align-items: center;
            padding: 0 2rem; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }
        .navbar-brand {
            color: #fff; font-size: 1rem; font-weight: 700; text-decoration: none;
            display: flex; align-items: center; gap: 0.45rem; margin-right: 2.5rem; letter-spacing: -0.01em; white-space: nowrap;
        }
        .navbar-nav { display: flex; align-items: center; list-style: none; flex: 1; gap: 0; }
        .navbar-nav li a {
            display: flex; align-items: center; height: 56px; padding: 0 1.1rem; color: rgba(255,255,255,0.8);
            text-decoration: none; font-size: 0.9rem; font-weight: 500; position: relative; transition: color 0.15s; white-space: nowrap;
        }
        .navbar-nav li a:hover { color: #fff; }
        .navbar-nav li a.active { color: #fff; font-weight: 700; }
        .navbar-nav li a.active::after {
            content: ''; position: absolute; bottom: 0; left: 1.1rem; right: 1.1rem; height: 3px; background: #fff; border-radius: 3px 3px 0 0;
        }
        .badge-nav {
            display: inline-flex; align-items: center; justify-content: center; background: #e53e3e; color: #fff;
            font-size: 0.68rem; font-weight: 700; min-width: 18px; height: 18px; padding: 0 4px; border-radius: 999px; margin-left: 5px; line-height: 1;
        }
        .navbar-actions { margin-left: auto; }
        .btn-sair {
            background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,0.55);
            padding: 0.35rem 1.1rem; border-radius: 7px; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: background 0.15s, border-color 0.15s; font-family: inherit;
        }
        .btn-sair:hover { background: rgba(255,255,255,0.15); border-color: #fff; }

        /* ── AVISO DE MODO DE DEMONSTRAÇÃO ───────────────── */
        .demo-banner {
            background: #fffaf0; border: 1.5px solid #feebc8; color: #9c4221;
            border-radius: 10px; padding: 0.75rem 1.1rem; margin-bottom: 1.25rem;
            font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;
        }

        /* ── LAYOUT DE CONTEÚDO ───────────────────────────── */
        .main { max-width: 1120px; margin: 0 auto; padding: 2.25rem 1.5rem 3rem; }
        .top-actions { margin-bottom: 1.25rem; }
        .btn-voltar {
            display: inline-flex; align-items: center; background: #fff; color: #4a5568; border: 1.5px solid #e2e8f0;
            padding: 0.4rem 1.2rem; border-radius: 7px; font-size: 0.88rem; font-weight: 600; text-decoration: none; transition: background 0.15s;
        }
        .btn-voltar:hover { background: #f7fafc; }

        .profile-header-container { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .profile-title { font-size: 1.65rem; font-weight: 700; color: #1a202c; }
        .btn-imprimir-todas {
            background: #4a5568; color: #fff; border: none; padding: 0.55rem 1.2rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: background 0.15s;
        }
        .btn-imprimir-todas:hover { background: #2d3748; }

        .cards-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2.5rem; }
        .card-info { background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 0 0 1px rgba(0,0,0,0.04); display: flex; flex-direction: column; gap: 0.75rem; }
        .card-resumo { background: #fffaf0; border: 1.5px solid #feebc8; }
        .card-resumo-title { font-size: 0.95rem; font-weight: 700; color: #dd6b20; margin-bottom: 0.25rem; }
        .info-item { font-size: 0.92rem; color: #4a5568; }
        .info-item strong { color: #1a202c; font-weight: 600; }
        .text-danger-custom { color: #c53030; font-weight: 700; }

        .section-title { font-size: 1.15rem; font-weight: 700; color: #2d3748; margin-bottom: 1.25rem; }
        .table-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px rgba(0,0,0,0.04); overflow: hidden; overflow-x: auto; }
        .ocorrencias-table { width: 100%; border-collapse: collapse; min-width: 850px; }
        .ocorrencias-table thead tr { border-bottom: 1.5px solid #e8ecf2; }
        .ocorrencias-table th { padding: 0.95rem 1.1rem; text-align: left; font-size: 0.8rem; font-weight: 700; color: #718096; background: #fcfdfe; }
        .ocorrencias-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.2s; }
        .ocorrencias-table tbody tr:last-child { border-bottom: none; }
        .ocorrencias-table tbody tr:hover { background: #f8fafd; }
        .ocorrencias-table td { padding: 1rem 1.1rem; font-size: 0.88rem; vertical-align: top; }

        .infracao-tag-container { display: flex; flex-direction: column; gap: 0.35rem; }
        .infracao-ids { display: flex; gap: 0.5rem; color: #1a56db; font-weight: 700; font-size: 0.85rem; }
        .infracao-texto { color: #4a5568; line-height: 1.4; }
        .status-wrapper { display: flex; flex-direction: column; gap: 0.3rem; }
        .status-pill { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; font-weight: 700; padding: 0.22rem 0.7rem; border-radius: 999px; white-space: nowrap; width: fit-content; }
        .status-pendente { background: #fde2e2; color: #c53030; }
        .status-resolvida { background: #d4edda; color: #276749; }
        .sub-notif { font-size: 0.74rem; color: #dd6b20; font-weight: 600; white-space: nowrap; }
        .actions-cell { display: flex; gap: 0.4rem; }

        .btn-action-editar {
            background: #fff; color: #dd6b20; border: 1.5px solid #fbd38d; padding: 0.4rem 0.85rem;
            border-radius: 6px; font-size: 0.82rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; transition: background 0.15s;
        }
        .btn-action-editar:hover { background: #fffaf0; }
        .btn-action-print {
            background: #4a5568; color: #fff; border: none; padding: 0.4rem 0.65rem; border-radius: 6px;
            cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: background 0.15s;
        }
        .btn-action-print:hover { background: #2d3748; }

        /* ── TOAST (feedback rápido) ──────────────────────── */
        .toast {
            position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%) translateY(20px);
            background: #1a202c; color: #fff; padding: 0.75rem 1.4rem; border-radius: 999px;
            font-size: 0.88rem; font-weight: 600; box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            opacity: 0; transition: opacity 0.25s, transform 0.25s; z-index: 300; pointer-events: none;
        }
        .toast.toast-visivel { opacity: 1; transform: translateX(-50%) translateY(0); }

        @media (max-width: 768px) {
            .cards-grid { grid-template-columns: 1fr; gap: 1rem; }
            .main { padding: 1.5rem 1rem 2.5rem; }
            .profile-title { font-size: 1.35rem; }
        }

        /* ── IMPRESSÃO DE UMA ÚNICA OCORRÊNCIA ────────────────
           Ativado via JS (perfil.js) ao clicar no 🖨️ de uma linha.
           Esconde tudo, exceto a identificação do aluno e a linha
           escolhida na tabela. */
        body.imprimir-uma-ocorrencia .card-resumo,
        body.imprimir-uma-ocorrencia .section-title { display: none; }
        body.imprimir-uma-ocorrencia .cards-grid { grid-template-columns: 1fr; }
        body.imprimir-uma-ocorrencia .ocorrencias-table tbody tr { display: none; }
        body.imprimir-uma-ocorrencia .ocorrencias-table tbody tr.linha-imprimir-ativa { display: table-row; }

        /* ── REGRAS GERAIS DE IMPRESSÃO ───────────────────── */
        @media print {
            .navbar, .top-actions, .btn-imprimir-todas, .actions-cell, .demo-banner, .toast {
                display: none !important;
            }
            body { background: #fff; }
            .main { padding: 0; max-width: 100%; }
            .table-card { box-shadow: none; border: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="navbar-brand">🏠 Ocorrências</a>
    <ul class="navbar-nav">
        <li><a href="nova_ocorrencia.php">Nova Ocorrência</a></li>
        <li><a href="pesquisa_turmas.php">Pesquisa e Turmas</a></li>
        <li>
            <a href="pendentes.php">
                Ocorrências Pendentes
                <?php if ($totalPendentesGlobal > 0): ?>
                    <span class="badge-nav"><?= $totalPendentesGlobal ?></span>
                <?php endif; ?>
            </a>
        </li>
    </ul>
    <div class="navbar-actions">
        <form method="POST" action="logout.php">
            <button type="submit" class="btn-sair">Sair</button>
        </form>
    </div>
</nav>

<main class="main">

    <?php if ($modoDemonstracao): ?>
        <div class="demo-banner">
            ⚠️ Modo de demonstração — não foi possível conectar ao banco de dados real
            (driver PDO indisponível ou banco do grupo inacessível). Exibindo dados fictícios
            só para você visualizar e testar a tela.
        </div>
    <?php endif; ?>

    <div class="top-actions">
        <a href="pendentes.php" class="btn-voltar">← Voltar</a>
    </div>

    <div class="profile-header-container">
        <h1 class="profile-title">Perfil: <?= htmlspecialchars($aluno['nome']) ?></h1>
        <button type="button" class="btn-imprimir-todas">🖨️ Imprimir Todas</button>
    </div>

    <div class="cards-grid">
        <div class="card-info">
            <p class="info-item"><strong>Nº SIMADE:</strong> <?= htmlspecialchars($aluno['simade']) ?></p>
            <p class="info-item"><strong>Nascimento:</strong> <?= htmlspecialchars($aluno['nascimento']) ?></p>
            <p class="info-item"><strong>Turma Atual:</strong> <?= htmlspecialchars($aluno['turma_atual']) ?></p>
        </div>

        <div class="card-info card-resumo">
            <h2 class="card-resumo-title">Resumo do Ano Letivo</h2>
            <p class="info-item"><strong>Total de Ocorrências:</strong> <?= $totalOcorrencias ?></p>
            <p class="info-item"><strong>Pendentes:</strong> <span class="text-danger-custom"><?= $totalPendentes ?></span></p>
            <p class="info-item"><strong>Mais reincidente:</strong> <?= htmlspecialchars($maisReincidente) ?></p>
        </div>
    </div>

    <h2 class="section-title">Histórico de Ocorrências</h2>

    <div class="table-card">
        <table class="ocorrencias-table">
            <thead>
                <tr>
                    <th style="width: 110px;">Data</th>
                    <th style="width: 80px;">Horário</th>
                    <th style="width: 220px;">Matéria / Professor</th>
                    <th>Infrações</th>
                    <th style="width: 150px;">Status</th>
                    <th style="width: 120px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($historicoOcorrencias) === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #718096; padding: 2rem;">
                            Nenhuma ocorrência registrada para este aluno.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($historicoOcorrencias as $oc): ?>
                        <tr data-id="<?= $oc['id'] ?>">
                            <td><?= $oc['data_formatada'] ?></td>
                            <td><?= $oc['hora_formatada'] ?></td>
                            <td><?= htmlspecialchars($oc['materia_professor'] ?? '—') ?></td>
                            <td>
                                <div class="infracao-tag-container">
                                    <div class="infracao-ids">
                                        <?php
                                        // Quebra os IDs separados por vírgula no banco em blocos visuais
                                        $ids = explode(',', $oc['infracoes_ids']);
                                        foreach ($ids as $id):
                                        ?>
                                            <span><?= trim($id) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="infracao-texto">
                                        <?= htmlspecialchars($oc['infracoes_texto'] ?? '—') ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="status-wrapper">
                                    <?php if ($oc['status'] === 'pendente'): ?>
                                        <span class="status-pill status-pendente">🔴 Pendente</span>
                                        <?php if ($oc['notif_responsavel'] == 1): ?>
                                            <span class="sub-notif">⚠ Notif. responsável</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="status-pill status-resolvida">✅ Resolvida</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <button type="button" class="btn-action-editar">✏️ Editar</button>
                                    <button type="button" class="btn-action-print" title="Imprimir Ocorrência">🖨️</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="toast" class="toast"></div>

<script src="perfil.js"></script>
</body>
</html>