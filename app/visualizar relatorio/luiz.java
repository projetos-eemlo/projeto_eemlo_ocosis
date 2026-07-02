<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocorrências Pendentes · Ocorrências</title>
    <style>
        /* ── RESET ─────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f0f2f5;
            color: #2d3748;
            min-height: 100vh;
        }
 
        /* ── NAVBAR ────────────────────────────────────────── */
        .navbar {
            background: #1a56db;
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }
 
        .navbar-brand {
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.45rem;
            margin-right: 2.5rem;
            letter-spacing: -0.01em;
            white-space: nowrap;
        }
 
        .navbar-nav {
            display: flex;
            align-items: center;
            list-style: none;
            flex: 1;
            gap: 0;
        }
 
        .navbar-nav li a {
            display: flex;
            align-items: center;
            height: 56px;
            padding: 0 1.1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            position: relative;
            transition: color 0.15s;
            white-space: nowrap;
        }
 
        .navbar-nav li a:hover { color: #fff; }
 
        .navbar-nav li a.active { color: #fff; font-weight: 700; }
 
        .navbar-nav li a.active::after {
            content: '';
            position: absolute;
            bottom: 0; left: 1.1rem; right: 1.1rem;
            height: 3px;
            background: #fff;
            border-radius: 3px 3px 0 0;
        }
 
        .badge-nav {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e53e3e;
            color: #fff;
            font-size: 0.68rem;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 999px;
            margin-left: 5px;
            line-height: 1;
        }
 
        .navbar-actions { margin-left: auto; }
 
        .btn-sair {
            background: transparent;
            color: #fff;
            border: 1.5px solid rgba(255,255,255,0.55);
            padding: 0.35rem 1.1rem;
            border-radius: 7px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
            font-family: inherit;
        }
 
        .btn-sair:hover { background: rgba(255,255,255,0.15); border-color: #fff; }
 
        /* ── LAYOUT ─────────────────────────────────────────── */
        .main {
            max-width: 1120px;
            margin: 0 auto;
            padding: 2.25rem 1.5rem 3rem;
        }
 
        .page-header { margin-bottom: 1.5rem; }
 
        .page-title {
            font-size: 1.65rem;
            font-weight: 700;
            color: #1a202c;
            line-height: 1.2;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
 
        .badge-pendentes {
            background: #fde2e2;
            color: #c53030;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 0.3rem 0.9rem;
            border-radius: 999px;
        }
 
        /* ── AVISO PENDENTES ────────────────────────────────── */
        .alert-pendentes {
            background: #fff5f5;
            border: 1.5px solid #feb2b2;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
 
        .alert-pendentes-titulo {
            font-size: 0.88rem;
            font-weight: 700;
            color: #c53030;
            margin-bottom: 0.75rem;
        }
 
        .chips-alunos {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }
 
        .chip-aluno {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: #fff;
            border: 1.5px solid #fca5a5;
            color: #9b2c2c;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.15s, transform 0.1s;
        }
 
        .chip-aluno:hover { background: #fff5f5; }
        .chip-aluno:active { transform: scale(0.97); }
 
        /* ── DOTS ───────────────────────────────────────────── */
        .dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
        .dot-red { background: #e53e3e; box-shadow: 0 0 0 2px rgba(229,62,62,.18); }
 
        /* ── TABLE CARD ─────────────────────────────────────── */
        .table-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px rgba(0,0,0,0.04);
            overflow: hidden;
            overflow-x: auto;
        }
 
        .ocorrencias-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }
 
        .ocorrencias-table thead tr { border-bottom: 1.5px solid #e8ecf2; }
 
        .ocorrencias-table th {
            padding: 0.95rem 1.1rem;
            text-align: left;
            font-size: 0.8rem;
            font-weight: 700;
            color: #718096;
        }
 
        .ocorrencias-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.4s; }
        .ocorrencias-table tbody tr:last-child { border-bottom: none; }
        .ocorrencias-table tbody tr:hover { background: #f8fafd; }
        .ocorrencias-table tbody tr.linha-destacada { background: #fff5f5; }
 
        .ocorrencias-table td {
            padding: 0.85rem 1.1rem;
            font-size: 0.88rem;
            vertical-align: top;
        }
 
        .turma-badge {
            display: inline-block;
            background: #ebf4ff;
            color: #1a56db;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.18rem 0.65rem;
            border-radius: 999px;
            letter-spacing: 0.01em;
            white-space: nowrap;
        }
 
        .aluno-pendente {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 600;
            color: #9b2c2c;
            white-space: nowrap;
        }
 
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.22rem 0.7rem;
            border-radius: 999px;
            white-space: nowrap;
        }
 
        /* NOVAS CORES DE STATUS */
        .status-pendente { background: #fde2e2; color: #c53030; }
        .status-entregue { background: #d4edda; color: #276749; }
        .status-foraprazo { background: #feebc8; color: #c05621; }
 
        .resp-convocado {
            display: block;
            font-size: 0.74rem;
            color: #c05621;
            margin-top: 0.3rem;
            font-weight: 600;
        }
 
        .td-acoes { display: flex; gap: 0.5rem; white-space: nowrap; }
 
        .btn-perfil {
            display: inline-block;
            background: #276749;
            color: #fff;
            border: none;
            padding: 0.42rem 1.05rem;
            border-radius: 7px;
            font-size: 0.84rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: background 0.15s, transform 0.1s;
        }
        .btn-perfil:hover  { background: #22543d; }
        .btn-perfil:active { transform: scale(0.97); }
 
        .btn-editar {
            display: inline-block;
            background: #fff;
            color: #c53030;
            border: 1.5px solid #fca5a5;
            padding: 0.4rem 1rem;
            border-radius: 7px;
            font-size: 0.84rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.15s, transform 0.1s;
        }
        .btn-editar:hover  { background: #fff5f5; }
        .btn-editar:active { transform: scale(0.97); }
 
        /* ── MODAL DE STATUS (ENXUGADO) ─────────────────────── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(26, 32, 44, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            z-index: 200;
        }
 
        .modal-overlay[hidden] { display: none; }
 
        .modal-editar {
            background: #fff;
            border-radius: 14px;
            width: 100%;
            max-width: 480px; /* Levemente maior para acomodar 3 botões lado a lado */
            max-height: 90vh;
            overflow-y: auto;
            padding: 1.5rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }
 
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.35rem;
        }
 
        .modal-header h2 { font-size: 1.15rem; font-weight: 700; color: #1a202c; }
 
        .modal-fechar {
            background: none;
            border: none;
            font-size: 1.4rem;
            line-height: 1;
            color: #a0aec0;
            cursor: pointer;
            padding: 0.2rem;
        }
        .modal-fechar:hover { color: #4a5568; }
 
        .modal-subtitulo {
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 1.25rem;
        }
 
        .campo-grupo { margin-bottom: 1.1rem; }
        .campo-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }
 
        /* STATUS COM 3 OPÇÕES */
        .status-toggle {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0.5rem;
        }
 
        .status-opcao {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 0.4rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            padding: 0.8rem 0.5rem;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            color: #4a5568;
            transition: border-color 0.15s, background 0.15s;
        }
 
        .status-opcao input[type="radio"] {
            accent-color: #1a56db;
            width: 16px;
            height: 16px;
            margin: 0;
        }
 
        .status-opcao:has(input:checked) { border-width: 2px; }
 
        .status-opcao.status-opcao-pendente:has(input:checked) {
            border-color: #e53e3e; background: #fff5f5; color: #c53030;
        }
        .status-opcao.status-opcao-entregue:has(input:checked) {
            border-color: #38a169; background: #f0fff4; color: #276749;
        }
        .status-opcao.status-opcao-foraprazo:has(input:checked) {
            border-color: #dd6b20; background: #fffaf0; color: #c05621;
        }
 
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.7rem;
            margin-top: 1.5rem;
        }
 
        .btn-cancelar {
            background: #fff;
            color: #4a5568;
            border: 1.5px solid #e2e8f0;
            padding: 0.5rem 1.2rem;
            border-radius: 7px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-salvar {
            background: #1a56db;
            color: #fff;
            border: none;
            padding: 0.5rem 1.3rem;
            border-radius: 7px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }
 
        /* ── TOAST ──────────────────────────────────────────── */
        .toast {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: #1a202c;
            color: #fff;
            padding: 0.75rem 1.4rem;
            border-radius: 999px;
            font-size: 0.88rem;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            opacity: 0;
            transition: opacity 0.25s, transform 0.25s;
            z-index: 300;
            pointer-events: none;
        }
        .toast.toast-visivel { opacity: 1; transform: translateX(-50%) translateY(0); }
 
        @media (max-width: 768px) {
            .status-toggle { grid-template-columns: 1fr; }
            .status-opcao { flex-direction: row; justify-content: flex-start; padding: 0.6rem 1rem;}
        }
    </style>
</head>
<body>
<?php
/* ════════════════════════════════════════════════════════════
    DADOS TEMPORÁRIOS (MOCK)
   ════════════════════════════════════════════════════════════ */
$tiposInfracao = [
    1 => 'Indisciplina durante a aula de',
    2 => 'Desrespeitou o(a) professor(a)',
    3 => 'Agrediu o(a) colega',
    4 => 'Não trouxe o material necessário',
    5 => 'Não fez as atividades e/ou trabalho solicitado',
    6 => 'Tem deixado as atividades de sala incompletas',
    7 => 'Chegou atrasado, após o horário de entrada permitido',
    8 => 'Fez uso do celular ou outro aparelho eletrônico durante as aulas',
];
 
$ocorrenciasPendentes = [
    [
        'id' => 1, 'aluno_id' => 101, 'aluno' => 'Fernanda Lima', 'turma' => '2º Ano B',
        'data' => '2026-05-15', 'hora' => '10:00', 'disciplina' => 'Inglês', 'professor' => 'Prof. William',
        'infracoes' => [7, 8], 'descricao' => 'Atrasou e estava com celular em mãos.',
        'notificar_responsavel' => true, 'resp_convocado' => false, 'status' => 'pendente',
    ],
    [
        'id' => 2, 'aluno_id' => 102, 'aluno' => 'Maria Eduarda', 'turma' => '1º Ano A',
        'data' => '2026-05-10', 'hora' => '09:15', 'disciplina' => 'Português', 'professor' => 'Profª Sandra',
        'infracoes' => [8, 2], 'descricao' => '',
        'notificar_responsavel' => false, 'resp_convocado' => true, 'status' => 'pendente',
    ]
];
 
/* ── FUNÇÕES AUXILIARES ─────────────────────────────────────── */
function classeStatus(string $status): string {
    if ($status === 'entregue') return 'status-entregue';
    if ($status === 'fora_do_prazo') return 'status-foraprazo';
    return 'status-pendente';
}
 
function textoStatus(string $status): string {
    if ($status === 'entregue') return 'Entregue';
    if ($status === 'fora_do_prazo') return 'Fora do prazo';
    return 'Pendente';
}
 
function formatarData(string $dataIso): string { return date('d/m/Y', strtotime($dataIso)); }
 
function textoInfracoes(array $idsInfracao, array $tiposInfracao): string {
    $textos = [];
    foreach ($idsInfracao as $id) {
        if (isset($tiposInfracao[$id])) $textos[] = $tiposInfracao[$id];
    }
    return implode('; ', $textos);
}
 
$totalPendentes = count(array_filter($ocorrenciasPendentes, fn($o) => $o['status'] === 'pendente'));
 
$alunosPendentesUnicos = [];
$idsVistos = [];
foreach ($ocorrenciasPendentes as $oc) {
    if ($oc['status'] === 'pendente' && !in_array($oc['aluno_id'], $idsVistos, true)) {
        $alunosPendentesUnicos[] = ['id' => $oc['aluno_id'], 'nome' => $oc['aluno']];
        $idsVistos[] = $oc['aluno_id'];
    }
}
?>
 
<nav class="navbar">
    <a href="index.php" class="navbar-brand">🏠 Ocorrências</a>
    <ul class="navbar-nav">
        <li><a href="nova_ocorrencia.php">Nova Ocorrência</a></li>
        <li><a href="pesquisa_turmas.php">Pesquisa e Turmas</a></li>
        <li>
            <a href="pendentes.php" class="active">
                Ocorrências Pendentes
                <?php if ($totalPendentes > 0): ?>
                    <span class="badge-nav"><?= $totalPendentes ?></span>
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
    <div class="page-header">
        <h1 class="page-title">
            Ocorrências Pendentes
            <span class="badge-pendentes"><?= $totalPendentes ?> pendentes</span>
        </h1>
    </div>
 
    <?php if (!empty($alunosPendentesUnicos)): ?>
        <div class="alert-pendentes">
            <p class="alert-pendentes-titulo">Alunos com ocorrências pendentes:</p>
            <div class="chips-alunos">
                <?php foreach ($alunosPendentesUnicos as $a): ?>
                    <button type="button" class="chip-aluno" data-aluno-id="<?= $a['id'] ?>">
                        <span class="dot dot-red"></span>
                        <?= htmlspecialchars($a['nome']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
 
    <div class="table-card">
        <table class="ocorrencias-table" id="tabela-pendentes">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Aluno</th>
                    <th>Turma</th>
                    <th>Infrações</th>
                    <th>Matéria / Prof.</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="tabela-pendentes-corpo">
                <?php foreach ($ocorrenciasPendentes as $oc): ?>
                    <tr data-occ-id="<?= $oc['id'] ?>" data-aluno-id="<?= $oc['aluno_id'] ?>" class="linha-destacada">
                        <td><?= formatarData($oc['data']) ?></td>
                        <td>
                            <span class="aluno-pendente">
                                <span class="dot dot-red"></span>
                                <?= htmlspecialchars($oc['aluno']) ?>
                            </span>
                        </td>
                        <td><span class="turma-badge"><?= htmlspecialchars($oc['turma']) ?></span></td>
                        <td class="td-infracoes"><?= htmlspecialchars(textoInfracoes($oc['infracoes'], $tiposInfracao)) ?></td>
                        <td class="td-materia"><?= htmlspecialchars($oc['disciplina']) ?> / <?= htmlspecialchars($oc['professor']) ?></td>
                        <td class="td-status">
                            <span class="status-pill <?= classeStatus($oc['status']) ?>">
                                <?= textoStatus($oc['status']) ?>
                            </span>
                            <?php if ($oc['resp_convocado']): ?>
                                <span class="resp-convocado">⚠ Resp. convocado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="td-acoes">
                                <button
                                    type="button"
                                    class="btn-editar"
                                    data-occ='<?= htmlspecialchars(json_encode([
                                        'id' => $oc['id'],
                                        'aluno' => $oc['aluno'],
                                        'turma' => $oc['turma'],
                                        'data' => formatarData($oc['data']),
                                        'hora' => $oc['hora'],
                                        'status' => $oc['status'],
                                    ]), ENT_QUOTES, 'UTF-8') ?>'
                                >Atualizar Status</button>
 
                                <a href="perfil.php?id=<?= $oc['aluno_id'] ?>" class="btn-perfil">Ver Perfil</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
 
<div class="modal-overlay" id="modal-overlay" hidden>
    <div class="modal-editar" role="dialog" aria-modal="true" aria-labelledby="modal-titulo">
        <div class="modal-header">
            <h2 id="modal-titulo">Atualizar Status</h2>
            <button type="button" class="modal-fechar" id="modal-fechar" aria-label="Fechar">&times;</button>
        </div>
        <p class="modal-subtitulo" id="modal-subtitulo"></p>
 
        <form id="form-editar-ocorrencia">
            <input type="hidden" id="modal-occ-id" name="id">
 
            <div class="campo-grupo">
                <label class="campo-label">Status da Ocorrência</label>
                <div class="status-toggle">
                    <label class="status-opcao status-opcao-pendente">
                        <input type="radio" name="status" value="pendente">
                        Pendente
                    </label>
                    <label class="status-opcao status-opcao-entregue">
                        <input type="radio" name="status" value="entregue">
                        Entregue
                    </label>
                    <label class="status-opcao status-opcao-foraprazo">
                        <input type="radio" name="status" value="fora_do_prazo">
                        Fora do prazo
                    </label>
                </div>
            </div>
 
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" id="modal-cancelar">Cancelar</button>
                <button type="submit" class="btn-salvar">Salvar Status</button>
            </div>
        </form>
    </div>
</div>
 
<div id="toast" class="toast">Status atualizado com sucesso!</div>
 
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalOverlay = document.getElementById('modal-overlay');
    const formEditar = document.getElementById('form-editar-ocorrencia');
    const btnFechar = document.getElementById('modal-fechar');
    const btnCancelar = document.getElementById('modal-cancelar');
    const toast = document.getElementById('toast');
 
    const modalSubtitulo = document.getElementById('modal-subtitulo');
    const modalOccId = document.getElementById('modal-occ-id');
 
    // Abrir o modal
    document.querySelectorAll('.btn-editar').forEach(botao => {
        botao.addEventListener('click', () => {
            const dadosOcorrencia = JSON.parse(botao.getAttribute('data-occ'));
 
            modalSubtitulo.innerHTML = `<strong>${dadosOcorrencia.aluno}</strong> · ${dadosOcorrencia.data} · ${dadosOcorrencia.hora} · ${dadosOcorrencia.turma}`;
            modalOccId.value = dadosOcorrencia.id;
 
            const radioStatus = formEditar.querySelector(`input[name="status"][value="${dadosOcorrencia.status}"]`);
            if (radioStatus) {
                radioStatus.checked = true;
            }
 
            modalOverlay.removeAttribute('hidden');
        });
    });
 
    // Fechar o modal
    const fecharModal = () => {
        modalOverlay.setAttribute('hidden', 'true');
        formEditar.reset();
    };
 
    btnFechar.addEventListener('click', fecharModal);
    btnCancelar.addEventListener('click', fecharModal);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) fecharModal();
    });
 
    // Salvar (Simulação)
    formEditar.addEventListener('submit', (e) => {
        e.preventDefault();
        
        fecharModal();
        
        // Exibe o toast
        toast.classList.add('toast-visivel');
        setTimeout(() => {
            toast.classList.remove('toast-visivel');
        }, 3000);
    });
});
</script>
</body>
</html>




























<?php
// 1. CONFIGURAÇÃO DA CONEXÃO COM O BANCO DE DADOS
$db_host = "localhost";
$db_name = "ocosis";
$db_user = "root";
$db_pass = "";

// 2. CAPTURA O ID DO ALUNO PELA URL
$aluno_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($aluno_id === 0) {
    die("<div style='padding:20px; font-family:sans-serif;'><h3>Erro: Nenhum ID de aluno foi especificado para gerar o relatório.</h3><a href='pendentes.php'>Voltar para Pendentes</a></div>");
}

$modoDemonstracao = false;

try {
    // 3. CONEXÃO REAL
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 4. CONSULTA DO ALUNO (Ajustada para o ocosis.sql com JOIN para pegar a turma)
    $sqlAluno = $pdo->prepare("
        SELECT 
            a.id_aluno AS id, 
            a.nome_aluno AS nome, 
            a.num_simade AS simade, 
            a.dt_nascimento AS data_nascimento, 
            t.desc_turma AS turma_atual
        FROM alunos a
        LEFT JOIN turma t ON a.id_turma = t.id_turma
        WHERE a.id_aluno = :id
    ");
    $sqlAluno->execute(['id' => $aluno_id]);
    $aluno = $sqlAluno->fetch(PDO::FETCH_ASSOC);

    if ($aluno && isset($aluno['data_nascimento'])) {
        $aluno['nascimento'] = date('d/m/Y', strtotime($aluno['data_nascimento']));
    }
    
    if (!$aluno) {
        throw new Exception("Aluno não encontrado no banco de dados.");
    }

    // 5. CONSULTA DAS OCORRÊNCIAS (Ajustada mapeando os nomes para o seu código)
    // Nota: Ajuste os nomes das colunas de ocorrências abaixo caso o seu grupo mude no futuro
    $sqlOcorrencias = $pdo->prepare("
        SELECT 
            id_ocorrencia   AS id,
            id_aluno        AS aluno_id,
            data_ocorrencia AS data_registro,
            horario,
            disciplina      AS materia_professor,
            id_tipo_infracao AS infracoes_ids,
            desc_ocorrencia  AS infracoes_texto
        FROM ocorrencias 
        WHERE id_aluno = :aluno_id
        ORDER BY data_ocorrencia DESC, horario DESC
    ");
    $sqlOcorrencias->execute(['aluno_id' => $aluno_id]);
    $ocorrencias = $sqlOcorrencias->fetchAll(PDO::FETCH_ASSOC);

    // Monta o histórico no formato esperado pelo HTML
    $historicoOcorrencias = [];
    foreach ($ocorrencias as $row) {
        $historicoOcorrencias[] = [
            'id'                => $row['id'],
            'data_formatada'    => date('d/m/Y', strtotime($row['data_registro'])),
            'hora_formatada'    => substr($row['horario'], 0, 5),
            'materia_professor' => $row['materia_professor'] ?? '—',
            'infracoes_ids'     => $row['infracoes_ids'] ?? '',
            'infracoes_texto'   => $row['infracoes_texto'] ?? '—',
            // status e notif_responsavel não existem ainda no schema
            'status'            => 'pendente',
            'notif_responsavel' => 0,
        ];
    }

    $totalOcorrencias     = count($historicoOcorrencias);
    $totalPendentes       = $totalOcorrencias;
    $maisReincidente      = $historicoOcorrencias[0]['infracoes_texto'] ?? 'Nenhuma infração registrada';
    $totalPendentesGlobal = $totalPendentes;

} catch (Exception $e) {
    // SE O BANCO NÃO ESTIVER LIGADO, ENTRA NO SEU MODO DE DEMONSTRAÇÃO (MANTIDO IGUAL)
    $modoDemonstracao = true;
    
    // ... (pode manter todo o seu array de dados fictícios da Fernanda Lima aqui dentro do catch)
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

        /* ── MODAL DE CONFIRMAÇÃO ────────────────────────── */
        .modal-confirmacao-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.55);
            display: flex; align-items: center; justify-content: center;
            z-index: 300; padding: 1rem;
        }
        .modal-confirmacao-overlay[hidden] { display: none; }

        .modal-confirmacao {
            background: #fff; border-radius: 14px;
            width: 100%; max-width: 380px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            padding: 1.75rem 1.5rem 1.4rem;
            display: flex; flex-direction: column; gap: 0.6rem;
            text-align: center;
        }
        .modal-confirmacao-icone { font-size: 2rem; line-height: 1; }
        .modal-confirmacao h3 { font-size: 1.05rem; font-weight: 700; color: #1a202c; }
        .modal-confirmacao p  { font-size: 0.87rem; color: #718096; line-height: 1.5; }
        .modal-confirmacao-acoes {
            display: flex; gap: 0.75rem; justify-content: center; margin-top: 0.5rem;
        }
        .btn-confirmar-nao {
            flex: 1; background: #fff; color: #4a5568; border: 1.5px solid #e2e8f0;
            padding: 0.6rem 1rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600;
            cursor: pointer; font-family: inherit; transition: background 0.15s;
        }
        .btn-confirmar-nao:hover { background: #f7fafc; }
        .btn-confirmar-sim {
            flex: 1; background: #1a56db; color: #fff; border: none;
            padding: 0.6rem 1rem; border-radius: 8px; font-size: 0.88rem; font-weight: 700;
            cursor: pointer; font-family: inherit; transition: background 0.15s;
        }
        .btn-confirmar-sim:hover { background: #1648c0; }

        /* ── MODAL DE EDIÇÃO ──────────────────────────────── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.45);
            display: flex; align-items: flex-start; justify-content: center;
            z-index: 200;
            padding: 1.5rem 1rem;
            overflow-y: auto;
        }
        .modal-overlay[hidden] { display: none; }

        .modal-editar {
            background: #fff; border-radius: 14px;
            width: 100%; max-width: 560px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            display: flex; flex-direction: column;
            margin: auto;
        }

        .modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem 1.5rem; border-bottom: 1.5px solid #e8ecf2;
            position: sticky; top: 0; background: #fff; z-index: 1;
            border-radius: 14px 14px 0 0;
        }
        .modal-header h2 { font-size: 1.1rem; font-weight: 700; color: #1a202c; }
        .modal-fechar {
            background: none; border: none; cursor: pointer; color: #718096;
            font-size: 1.4rem; line-height: 1; padding: 0.1rem 0.3rem;
            border-radius: 4px; transition: color 0.15s, background 0.15s;
        }
        .modal-fechar:hover { color: #1a202c; background: #f1f5f9; }

        .modal-subtitulo {
            padding: 0.75rem 1.5rem;
            background: #f8fafd; border-bottom: 1px solid #e8ecf2;
            font-size: 0.88rem; color: #4a5568;
        }

        .modal-corpo { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1.1rem; }

        .campo-grupo { display: flex; flex-direction: column; gap: 0.45rem; }
        .campo-label { font-size: 0.82rem; font-weight: 700; color: #4a5568; text-transform: uppercase; letter-spacing: 0.03em; }

        /* Status toggle (radio visual) */
        .status-toggle { display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .status-opcao {
            flex: 1; min-width: 130px; display: flex; align-items: center; gap: 0.6rem;
            border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.7rem 1rem;
            cursor: pointer; font-size: 0.88rem; font-weight: 600; color: #4a5568;
            transition: border-color 0.15s, background 0.15s;
        }
        .status-opcao input[type="radio"] { accent-color: #1a56db; width: 16px; height: 16px; }
        .status-opcao:has(input:checked).status-opcao-pendente  { border-color: #e53e3e; background: #fff5f5; color: #c53030; }
        .status-opcao:has(input:checked).status-opcao-resolvida { border-color: #38a169; background: #f0fff4; color: #276749; }

        /* Selects */
        .campo-select {
            width: 100%; padding: 0.6rem 0.9rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 0.9rem; font-family: inherit; color: #2d3748;
            background: #fff; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23718096' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 0.85rem center;
            cursor: pointer; transition: border-color 0.15s;
        }
        .campo-select:focus { outline: none; border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,0.12); }

        /* Checkboxes de infração */
        .infracoes-lista {
            border: 1.5px solid #e2e8f0; border-radius: 8px;
            max-height: 190px; overflow-y: auto; padding: 0.25rem 0;
        }
        .infracao-item {
            display: flex; align-items: flex-start; gap: 0.7rem;
            padding: 0.5rem 0.9rem; cursor: pointer; font-size: 0.87rem; color: #2d3748;
            transition: background 0.12s;
        }
        .infracao-item:hover { background: #f8fafd; }
        .infracao-item input[type="checkbox"] { accent-color: #1a56db; margin-top: 2px; flex-shrink: 0; width: 15px; height: 15px; }

        /* Textarea */
        .campo-textarea {
            width: 100%; padding: 0.6rem 0.9rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 0.9rem; font-family: inherit; color: #2d3748; resize: vertical; min-height: 80px;
            transition: border-color 0.15s;
        }
        .campo-textarea:focus { outline: none; border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,0.12); }

        /* Checkbox notif */
        .notif-box {
            border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.9rem 1rem;
            display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer;
        }
        .notif-box input[type="checkbox"] { accent-color: #1a56db; width: 16px; height: 16px; margin-top: 2px; flex-shrink: 0; }
        .notif-box-texto strong { font-size: 0.88rem; font-weight: 700; color: #1a202c; display: block; }
        .notif-box-texto span { font-size: 0.78rem; color: #718096; }

        .modal-footer {
            display: flex; justify-content: flex-end; gap: 0.75rem;
            padding: 1.1rem 1.5rem; border-top: 1.5px solid #e8ecf2;
        }
        .btn-cancelar {
            background: #fff; color: #4a5568; border: 1.5px solid #e2e8f0;
            padding: 0.55rem 1.3rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600;
            cursor: pointer; font-family: inherit; transition: background 0.15s;
        }
        .btn-cancelar:hover { background: #f7fafc; }
        .btn-salvar {
            background: #1a56db; color: #fff; border: none;
            padding: 0.55rem 1.5rem; border-radius: 8px; font-size: 0.88rem; font-weight: 700;
            cursor: pointer; font-family: inherit; transition: background 0.15s;
        }
        .btn-salvar:hover { background: #1648c0; }

        /* Linha dupla de selects */
        .campos-duplos { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        @media (max-width: 500px) { .campos-duplos { grid-template-columns: 1fr; } }
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

<!-- ── MODAL EDITAR OCORRÊNCIA ─────────────────────────── -->
<div class="modal-overlay" id="modal-overlay" hidden>
    <div class="modal-editar" role="dialog" aria-modal="true" aria-labelledby="modal-titulo">

        <div class="modal-header">
            <h2 id="modal-titulo">Editar Ocorrência</h2>
            <button type="button" class="modal-fechar" id="modal-fechar" aria-label="Fechar">&times;</button>
        </div>

        <p class="modal-subtitulo" id="modal-subtitulo"></p>

        <form id="form-editar-ocorrencia">
            <div class="modal-corpo">

                <!-- Status -->
                <div class="campo-grupo">
                    <span class="campo-label">Status</span>
                    <div class="status-toggle">
                        <label class="status-opcao status-opcao-pendente">
                            <input type="radio" name="status" value="pendente"> 🔴 Pendente
                        </label>
                        <label class="status-opcao status-opcao-resolvida">
                            <input type="radio" name="status" value="resolvida"> ✅ Resolvida
                        </label>
                    </div>
                </div>

                <!-- Disciplina + Professor -->
                <div class="campos-duplos">
                    <div class="campo-grupo">
                        <label class="campo-label" for="modal-disciplina">Disciplina</label>
                        <select id="modal-disciplina" name="disciplina" class="campo-select">
                            <option value="">Selecione...</option>
                            <option>Português</option>
                            <option>Matemática</option>
                            <option>Inglês</option>
                            <option>Física</option>
                            <option>Química</option>
                            <option>Biologia</option>
                            <option>História</option>
                            <option>Geografia</option>
                            <option>Arte</option>
                            <option>Educação Física</option>
                        </select>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label" for="modal-professor">Professor(a)</label>
                        <select id="modal-professor" name="professor" class="campo-select">
                            <option value="">Selecione...</option>
                            <option>Prof. William</option>
                            <option>Profª Sandra</option>
                            <option>Prof. Eduardo</option>
                            <option>Prof. Marcos</option>
                            <option>Prof. Carlos</option>
                        </select>
                    </div>
                </div>

                <!-- Tipo(s) de Infração -->
                <div class="campo-grupo">
                    <span class="campo-label">Tipo(s) de Infração</span>
                    <div class="infracoes-lista" id="modal-infracoes-lista">
                        <?php
                        $tiposInfracaoModal = [
                            1  => 'Indisciplina durante a aula de',
                            2  => 'Desrespeitou o(a) professor(a)',
                            3  => 'Agrediu o(a) colega',
                            4  => 'Não trouxe o material necessário',
                            5  => 'Não fez as atividades e/ou trabalho solicitado',
                            6  => 'Tem deixado as atividades de sala incompletas',
                            7  => 'Chegou atrasado, após o horário de entrada permitido',
                            8  => 'Fez uso do celular ou outro aparelho eletrônico durante as aulas',
                            9  => 'Saiu da sala sem autorização',
                            10 => 'Perturbou a ordem e o silêncio durante as aulas',
                            11 => 'Destruiu patrimônio da escola',
                            12 => 'Trouxe objetos não permitidos',
                            13 => 'Usou linguagem inadequada ou ofensiva',
                            14 => 'Praticou bullying ou assédio a colegas',
                            15 => 'Tentou fraudar avaliações ou trabalhos',
                        ];
                        foreach ($tiposInfracaoModal as $idInf => $descInf): ?>
                            <label class="infracao-item">
                                <input type="checkbox" name="infracoes[]" value="<?= $idInf ?>">
                                <span><strong><?= $idInf ?>.</strong> <?= htmlspecialchars($descInf) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Descrição -->
                <div class="campo-grupo">
                    <label class="campo-label" for="modal-descricao">Descrição / Observações</label>
                    <textarea id="modal-descricao" name="descricao" class="campo-textarea" placeholder="Descreva o ocorrido..."></textarea>
                </div>

                <!-- Notificar responsável -->
                <label class="notif-box">
                    <input type="checkbox" id="modal-notif" name="notif_responsavel" value="1">
                    <div class="notif-box-texto">
                        <strong>16. Notificar responsável</strong>
                        <span>Aparecerá na impressão da folha</span>
                    </div>
                </label>

            </div><!-- /.modal-corpo -->

            <div class="modal-footer">
                <button type="button" class="btn-cancelar" id="modal-cancelar">Cancelar</button>
                <button type="submit" class="btn-salvar">Salvar Alterações</button>
            </div>
        </form>

    </div>
</div>

<!-- ── MODAL DE CONFIRMAÇÃO ───────────────────────────── -->
<div class="modal-confirmacao-overlay" id="modal-confirmacao-overlay" hidden>
    <div class="modal-confirmacao" role="dialog" aria-modal="true" aria-labelledby="conf-titulo">
        <div class="modal-confirmacao-icone">💾</div>
        <h3 id="conf-titulo">Confirmar alterações?</h3>
        <p>Tem certeza que deseja salvar as alterações feitas nesta ocorrência?</p>
        <div class="modal-confirmacao-acoes">
            <button type="button" class="btn-confirmar-nao" id="btn-confirmar-nao">Cancelar</button>
            <button type="button" class="btn-confirmar-sim" id="btn-confirmar-sim">Confirmar</button>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<script src="perfil.js"></script>
</body>
</html>