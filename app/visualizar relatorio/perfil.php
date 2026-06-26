<?php
/**
 * OCOSIS - Visualização do Relatório do Aluno (Dinamizado)
 * Responsável: Última Etapa do Projeto
 */

// 1. CONFIGURAÇÃO DA CONEXÃO COM O BANCO DE DADOS
// Se o seu grupo já tiver um arquivo central de conexão (ex: conexao.php), 
// comente as linhas abaixo e use apenas: include_once("conexao.php");
$db_host = "localhost";
$db_name = "ocosis_db"; // Substitua pelo nome real do banco de dados do grupo
$db_user = "root";
$db_pass = "";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='padding:20px; color:red; font-family:sans-serif;'><b>Erro de Conexão:</b> " . $e->getMessage() . "</div>");
}

// 2. CAPTURA O ID DO ALUNO PELA URL (Ex: perfil.php?id=5)
// Se não vier nenhum ID válido, o sistema interrompe para não quebrar a tela
$aluno_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($aluno_id === 0) {
    die("<div style='padding:20px; font-family:sans-serif;'><h3>Erro: Nenhum ID de aluno foi especificado para gerar o relatório.</h3><a href='pendentes.php'>Voltar para Pendentes</a></div>");
}

// 3. BUSCA OS DADOS CADASTRAIS DO ALUNO
$sqlAluno = $pdo->prepare("SELECT id, nome, simade, DATE_FORMAT(data_nascimento, '%d/%m/%Y') AS nascimento, turma_atual FROM alunos WHERE id = :id");
$sqlAluno->execute(['id' => $aluno_id]);
$aluno = $sqlAluno->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    die("<div style='padding:20px; font-family:sans-serif;'><h3>Erro: Aluno não encontrado no banco de dados.</h3><a href='pendentes.php'>Voltar</a></div>");
}

// 4. MSTRÉTRICAS PARA O CARD "RESUMO DO ANO LETIVO" (Cálculos Automáticos)
// Total Geral
$sqlTotal = $pdo->prepare("SELECT COUNT(*) as total FROM ocorrencias WHERE aluno_id = :id");
$sqlTotal->execute(['id' => $aluno_id]);
$totalOcorrencias = $sqlTotal->fetch(PDO::FETCH_ASSOC)['total'];

// Total Pendentes
$sqlPendentes = $pdo->prepare("SELECT COUNT(*) as total FROM ocorrencias WHERE aluno_id = :id AND status = 'pendente'");
$sqlPendentes->execute(['id' => $aluno_id]);
$totalPendentes = $sqlPendentes->fetch(PDO::FETCH_ASSOC)['total'];

// Infração mais frequente (Reincidência)
$sqlReincidente = $pdo->prepare("SELECT infracoes_texto, COUNT(*) as qtd FROM ocorrencias WHERE aluno_id = :id GROUP BY infracoes_texto ORDER BY qtd DESC LIMIT 1");
$sqlReincidente->execute(['id' => $aluno_id]);
$resReincidente = $sqlReincidente->fetch(PDO::FETCH_ASSOC);
$maisReincidente = $resReincidente ? $resReincidente['infracoes_texto'] : 'Nenhuma infração registrada';

// 5. HISTÓRICO DE OCORRÊNCIAS DO ALUNO
$sqlHistorico = $pdo->prepare("SELECT id, DATE_FORMAT(data_registro, '%d/%m/%Y') as data_formatada, TIME_FORMAT(horario, '%H:%i') as hora_formatada, materia_professor, infracoes_ids, infracoes_texto, status, notif_responsavel FROM ocorrencias WHERE aluno_id = :id ORDER BY data_registro DESC, horario DESC");
$sqlHistorico->execute(['id' => $aluno_id]);
$historicoOcorrencias = $sqlHistorico->fetchAll(PDO::FETCH_ASSOC);

// 6. BADGE DA NAVBAR GLOBAL (Contador geral do sistema)
$sqlBadgeGlobal = $pdo->query("SELECT COUNT(*) as total FROM ocorrencias WHERE status = 'pendente'");
$totalPendentesGlobal = $sqlBadgeGlobal->fetch(PDO::FETCH_ASSOC)['total'];
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

        @media (max-width: 768px) {
            .cards-grid { grid-template-columns: 1fr; gap: 1rem; }
            .main { padding: 1.5rem 1rem 2.5rem; }
            .profile-title { font-size: 1.35rem; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="navbar-brand">🏠 Ocorrências</a>
    <ul class="navbar-nav">
        <li><a href="nova_ocorrencia.php">Nova Ocorrência</a></li>
        <li><a href="pesquisa_turmas.php" class="active">Pesquisa e Turmas</a></li>
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
                                        <?= htmlspecialchars($oc['infracoes_text'] ?? $oc['infracoes_texto']) ?>
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

<script src="perfil.js"></script>
</body>
</html>