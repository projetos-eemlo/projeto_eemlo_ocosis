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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
                         'Helvetica Neue', Arial, sans-serif;
            background: #f0f2f5;
            color: #2d3748;
            min-height: 100vh;
        }
 
        /* ── NAVBAR (igual às outras páginas do sistema) ─────── */
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
 
        .navbar-nav li a.active {
            color: #fff;
            font-weight: 700;
        }
 
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
 
        .btn-sair:hover {
            background: rgba(255,255,255,0.15);
            border-color: #fff;
        }
 
        /* ── LAYOUT ─────────────────────────────────────────── */
        .main {
            max-width: 1120px;
            margin: 0 auto;
            padding: 2.25rem 1.5rem 3rem;
        }
 
        .page-header {
            margin-bottom: 1.5rem;
        }
 
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
 
        /* ── AVISO: ALUNOS COM OCORRÊNCIAS PENDENTES ─────────── */
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
 
        .chip-aluno.chip-ativo {
            background: #fed7d7;
            border-color: #e53e3e;
        }
 
        /* ── DOT (reaproveitado das outras telas) ────────────── */
        .dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
        }
 
        .dot-red { background: #e53e3e; box-shadow: 0 0 0 2px rgba(229,62,62,.18); }
 
        /* ── TABLE CARD (reaproveitado) ───────────────────────── */
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
 
        .ocorrencias-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.4s;
        }
 
        .ocorrencias-table tbody tr:last-child { border-bottom: none; }
        .ocorrencias-table tbody tr:hover { background: #f8fafd; }
 
        .ocorrencias-table tbody tr.linha-destacada {
            background: #fff5f5;
        }
 
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
 
        .status-pendente { background: #fde2e2; color: #c53030; }
        .status-resolvida { background: #d4edda; color: #276749; }
 
        .resp-convocado {
            display: block;
            font-size: 0.74rem;
            color: #c05621;
            margin-top: 0.3rem;
            font-weight: 600;
        }
 
        .td-acoes {
            display: flex;
            gap: 0.5rem;
            white-space: nowrap;
        }
 
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
            white-space: nowrap;
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
            white-space: nowrap;
        }
 
        .btn-editar:hover  { background: #fff5f5; }
        .btn-editar:active { transform: scale(0.97); }
 
        /* ════════════════════════════════════════════════════
           MODAL — EDITAR OCORRÊNCIA
           ════════════════════════════════════════════════════ */
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
            max-width: 460px;
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
 
        .modal-header h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1a202c;
        }
 
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
 
        .campo-linha {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.85rem;
        }
 
        .filter-select {
            width: 100%;
            padding: 0.5rem 2.2rem 0.5rem 0.8rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #2d3748;
            background: #fff;
            cursor: pointer;
            font-family: inherit;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%23718096' d='M5 7L0 2h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.7rem center;
        }
 
        .filter-select:focus {
            outline: none;
            border-color: #1a56db;
            box-shadow: 0 0 0 3px rgba(26,86,219,0.12);
        }
 
        /* status: pendente / resolvida (cartões selecionáveis) */
        .status-toggle {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
 
        .status-opcao {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            padding: 0.6rem 0.8rem;
            cursor: pointer;
            font-size: 0.88rem;
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
 
        .status-opcao:has(input:checked) {
            border-width: 2px;
        }
 
        .status-opcao.status-opcao-pendente:has(input:checked) {
            border-color: #e53e3e;
            background: #fff5f5;
            color: #c53030;
        }
 
        .status-opcao.status-opcao-resolvida:has(input:checked) {
            border-color: #38a169;
            background: #f0fff4;
            color: #276749;
        }
 
        .lista-infracoes {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            max-height: 175px;
            overflow-y: auto;
        }
 
        .item-infracao {
            display: flex;
            align-items: flex-start;
            gap: 0.55rem;
            padding: 0.55rem 0.75rem;
            font-size: 0.85rem;
            color: #2d3748;
            cursor: pointer;
            transition: background 0.12s;
        }
 
        .item-infracao:hover { background: #f8fafd; }
        .item-infracao:nth-child(even) { background: #fafbfd; }
        .item-infracao:nth-child(even):hover { background: #f1f5f9; }
 
        .item-infracao input {
            margin-top: 0.15rem;
            accent-color: #1a56db;
            flex-shrink: 0;
        }
 
        .campo-textarea {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.88rem;
            font-family: inherit;
            color: #2d3748;
            resize: vertical;
        }
 
        .campo-textarea:focus {
            outline: none;
            border-color: #1a56db;
            box-shadow: 0 0 0 3px rgba(26,86,219,0.12);
        }
 
        .item-notificar {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 0.9rem;
            margin-bottom: 1.25rem;
            cursor: pointer;
            font-size: 0.85rem;
            color: #2d3748;
        }
 
        .item-notificar input {
            margin-top: 0.2rem;
            accent-color: #1a56db;
        }
 
        .item-notificar small {
            display: block;
            color: #a0aec0;
            font-weight: 400;
            margin-top: 0.15rem;
        }
 
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.7rem;
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
            transition: background 0.15s;
        }
 
        .btn-cancelar:hover { background: #f7fafc; }
 
        .btn-salvar {
            background: #276749;
            color: #fff;
            border: none;
            padding: 0.5rem 1.3rem;
            border-radius: 7px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.15s, transform 0.1s;
        }
 
        .btn-salvar:hover  { background: #22543d; }
        .btn-salvar:active { transform: scale(0.97); }
 
        /* ── TOAST (feedback de "salvo com sucesso") ─────────── */
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
 
        .toast.toast-visivel {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
 
        /* ── RESPONSIVO ─────────────────────────────────────── */
        @media (max-width: 768px) {
            .navbar { padding: 0 1rem; }
            .navbar-brand { margin-right: 1rem; }
            .main { padding: 1.5rem 1rem 2.5rem; }
            .page-title { font-size: 1.3rem; }
            .campo-linha { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php
/* ════════════════════════════════════════════════════════════
    DADOS TEMPORÁRIOS (MOCK)
    Isso substitui, por enquanto, a consulta ao banco de dados.
    Quando formos para a Parte 2 (backend), isso vira uma query real.
    ════════════════════════════════════════════════════════════ */
 
$disciplinas = ['Português', 'Matemática', 'Inglês', 'Química', 'Física', 'História', 'Geografia', 'Educação Física', 'Artes'];
 
$professores = ['Prof. William', 'Profª Sandra', 'Prof. Eduardo', 'Prof. Marcos', 'Prof. Carlos'];
 
// Lista de tipos de infração.
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
    ],
    [
        'id' => 3, 'aluno_id' => 103, 'aluno' => 'Ricardo Souza', 'turma' => '1º Ano A',
        'data' => '2026-04-28', 'hora' => '11:40', 'disciplina' => 'Química', 'professor' => 'Prof. Eduardo',
        'infracoes' => [3], 'descricao' => '',
        'notificar_responsavel' => false, 'resp_convocado' => true, 'status' => 'pendente',
    ],
    [
        'id' => 4, 'aluno_id' => 104, 'aluno' => 'Alessandra Vieira', 'turma' => '1º Ano A',
        'data' => '2026-04-20', 'hora' => '08:30', 'disciplina' => 'Matemática', 'professor' => 'Prof. Marcos',
        'infracoes' => [2], 'descricao' => '',
        'notificar_responsavel' => false, 'resp_convocado' => true, 'status' => 'pendente',
    ],
    [
        'id' => 5, 'aluno_id' => 105, 'aluno' => 'João Silva Sauro', 'turma' => '1º Ano A',
        'data' => '2026-04-15', 'hora' => '13:50', 'disciplina' => 'Física', 'professor' => 'Prof. Carlos',
        'infracoes' => [8], 'descricao' => '',
        'notificar_responsavel' => false, 'resp_convocado' => true, 'status' => 'pendente',
    ],
];
 
/* ── FUNÇÕES AUXILIARES ─────────────────────────────────────── */
function classeStatus(string $status): string {
    return $status === 'resolvida' ? 'status-resolvida' : 'status-pendente';
}
 
function textoStatus(string $status): string {
    return $status === 'resolvida' ? 'Resolvida' : 'Pendente';
}
 
function formatarData(string $dataIso): string {
    return date('d/m/Y', strtotime($dataIso));
}
 
function textoInfracoes(array $idsInfracao, array $tiposInfracao): string {
    $textos = [];
    foreach ($idsInfracao as $id) {
        if (isset($tiposInfracao[$id])) {
            $textos[] = $tiposInfracao[$id];
        }
    }
    return implode('; ', $textos);
}
 
$totalPendentes = count(array_filter($ocorrenciasPendentes, fn($o) => $o['status'] === 'pendente'));
 
// Lista única de alunos com pendência
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
                                        'disciplina' => $oc['disciplina'],
                                        'professor' => $oc['professor'],
                                        'infracoes' => $oc['infracoes'],
                                        'descricao' => $oc['descricao'],
                                        'notificarResponsavel' => $oc['notificar_responsavel'],
                                        'status' => $oc['status'],
                                    ]), ENT_QUOTES, 'UTF-8') ?>'
                                >Editar</button>
 
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
            <h2 id="modal-titulo">Editar Ocorrência</h2>
            <button type="button" class="modal-fechar" id="modal-fechar" aria-label="Fechar">&times;</button>
        </div>
        <p class="modal-subtitulo" id="modal-subtitulo"></p>
 
        <form id="form-editar-ocorrencia">
            <input type="hidden" id="modal-occ-id" name="id">
 
            <div class="campo-grupo">
                <label class="campo-label">Status</label>
                <div class="status-toggle">
                    <label class="status-opcao status-opcao-pendente">
                        <input type="radio" name="status" value="pendente">
                        <span class="dot dot-red"></span> Pendente
                    </label>
                    <label class="status-opcao status-opcao-resolvida">
                        <input type="radio" name="status" value="resolvida">
                        ✅ Resolvida
                    </label>
                </div>
            </div>
 
            <div class="campo-linha">
                <div class="campo-grupo">
                    <label class="campo-label" for="modal-disciplina">Disciplina</label>
                    <select id="modal-disciplina" name="disciplina" class="filter-select">
                        <?php foreach ($disciplinas as $d): ?>
                            <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label" for="modal-professor">Professor(a)</label>
                    <select id="modal-professor" name="professor" class="filter-select">
                        <?php foreach ($professores as $p): ?>
                            <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
 
            <div class="campo-grupo">
                <label class="campo-label">Tipo(s) de Infração</label>
                <div class="lista-infracoes" id="lista-infracoes">
                    <?php foreach ($tiposInfracao as $idInfracao => $texto): ?>
                        <label class="item-infracao">
                            <input type="checkbox" name="infracoes[]" value="<?= $idInfracao ?>">
                            <span><?= $idInfracao ?>. <?= htmlspecialchars($texto) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
 
            <div class="campo-grupo">
                <label class="campo-label" for="modal-descricao">Descrição / Observações</label>
                <textarea id="modal-descricao" name="descricao" class="campo-textarea" rows="3"></textarea>
            </div>
 
            <label class="item-notificar">
                <input type="checkbox" name="notificar_responsavel" id="modal-notificar">
                <span>
                    <strong>16. Notificar responsável</strong>
                    <small>Aparecerá na impressão da folha</small>
                </span>
            </label>
 
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" id="modal-cancelar">Cancelar</button>
                <button type="submit" class="btn-salvar">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<div id="toast" class="toast">Alterações salvas com sucesso!</div>
 
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalOverlay = document.getElementById('modal-overlay');
    const formEditar = document.getElementById('form-editar-ocorrencia');
    const btnFechar = document.getElementById('modal-fechar');
    const btnCancelar = document.getElementById('modal-cancelar');
    const toast = document.getElementById('toast');

    // Elementos internos do modal
    const modalSubtitulo = document.getElementById('modal-subtitulo');
    const modalOccId = document.getElementById('modal-occ-id');
    const modalDisciplina = document.getElementById('modal-disciplina');
    const modalProfessor = document.getElementById('modal-professor');
    const modalDescricao = document.getElementById('modal-descricao');
    const modalNotificar = document.getElementById('modal-notificar');

    // 1. ABRIR E PREENCHER O MODAL COM OS DADOS DA LINHA CLICADA
    document.querySelectorAll('.btn-editar').forEach(botao => {
        botao.addEventListener('click', () => {
            // Captura o JSON que está guardado no atributo data-occ do botão
            const dadosOcorrencia = JSON.parse(botao.getAttribute('data-occ'));

            // Preenche o cabeçalho idêntico ao protótipo: Aluno · Data · Hora · Turma
            modalSubtitulo.innerHTML = `<strong>${dadosOcorrencia.aluno}</strong> · ${dadosOcorrencia.data} · ${dadosOcorrencia.hora} · ${dadosOcorrencia.turma}`;
            
            // Preenche os campos normais
            modalOccId.value = dadosOcorrencia.id;
            modalDisciplina.value = dadosOcorrencia.disciplina;
            modalProfessor.value = dadosOcorrencia.professor;
            modalDescricao.value = dadosOcorrencia.descricao;
            modalNotificar.checked = dadosOcorrencia.notificarResponsavel;

            // Ativa o rádio button do status correto (Pendente ou Resolvida)
            const radioStatus = formEditar.querySelector(`input[name="status"][value="${dadosOcorrencia.status}"]`);
            if (radioStatus) radioStatus.checked = true;

            // Limpa todos os checkboxes de infração antes de marcar os novos
            formEditar.querySelectorAll('input[name="infracoes[]"]').forEach(chk => chk.checked = false);

            // Marca as caixas das infrações que vieram no array
            dadosOcorrencia.infracoes.forEach(idInfracao => {
                const chk = formEditar.querySelector(`input[name="infracoes[]"][value="${idInfracao}"]`);
                if (chk) chk.checked = true;
            });

            // Torna o modal visível removendo o atributo hidden
            modalOverlay.removeAttribute('hidden');
        });
    });

    // 2. FUNÇÕES PARA FECHAR O MODAL
    const fecharModal = () => {
        modalOverlay.setAttribute('hidden', '');
        formEditar.reset();
    };

    btnFechar.addEventListener('click', fecharModal);
    btnCancelar.addEventListener('click', fecharModal);
    
    // Fecha se clicar na área escura (fora da caixa branca)
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) fecharModal();
    });

    // 3. EVENTO DE SUBMIT (Simulação visual de salvamento no Frontend)
    formEditar.addEventListener('submit', (e) => {
        e.preventDefault(); // Impede o recarregamento da página

        const id = modalOccId.value;
        const statusSelecionado = formEditar.querySelector('input[name="status"]:checked').value;
        const linhaTabela = document.querySelector(`tr[data-occ-id="${id}"]`);

        if (linhaTabela) {
            const statusTd = linhaTabela.querySelector('.td-status');
            const statusPill = statusTd.querySelector('.status-pill');

            // Atualiza as classes e o texto do Pill de Status na tabela
            if (statusSelecionado === 'resolvida') {
                statusPill.className = 'status-pill status-resolvida';
                statusPill.textContent = 'Resolvida';
                linhaTabela.classList.remove('linha-destacada'); // tira o fundo vermelho
            } else {
                statusPill.className = 'status-pill status-pendente';
                statusPill.textContent = 'Pendente';
                linhaTabela.classList.add('linha-destacada');
            }
        }

        // Fecha o modal e exibe o balão de sucesso
        fecharModal();
        mostrarToast();
    });

    // Auxiliar para animar o Toast de sucesso
    const mostrarToast = () => {
        toast.classList.add('toast-visivel');
        setTimeout(() => {
            toast.classList.remove('toast-visivel');
        }, 3000);
    };
});
</script>
</body>
</html>