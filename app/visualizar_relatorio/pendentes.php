<?php

$db_host = "localhost";
$db_port = "3306";
$db_name = "ocosis";
$db_user = "root";
$db_pass = "";

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='padding:20px;font-family:sans-serif;'><h3>Erro ao conectar ao banco de dados.</h3><p>" . htmlspecialchars($e->getMessage()) . "</p></div>");
}

/* ════════════════════════════════════════════════════════════════
   AJAX — ATUALIZAR STATUS DE UMA OCORRÊNCIA (modal "Atualizar Status")
   ════════════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax_action'] ?? '') === 'atualizar_status') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $id     = intval($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'pendente';

        // No banco (ocosis.sql) a coluna `status` só aceita ENUM('pendente','resolvida').
        // O modal desta tela ainda oferece 'entregue' / 'fora_do_prazo' (legado da versão
        // anterior); por enquanto qualquer valor diferente de 'pendente' é tratado como
        // 'resolvida' pra não quebrar a gravação. Ajustar aqui se o ENUM crescer no futuro.
        $status = ($status === 'pendente') ? 'pendente' : 'resolvida';

        if ($id === 0) {
            throw new Exception('ID da ocorrência inválido.');
        }

        $stmt = $pdo->prepare("UPDATE ocorrencias SET status = :status WHERE id_ocorrencia = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);

        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}

/* ════════════════════════════════════════════════════════════════
   CARREGAMENTO NORMAL DA PÁGINA — OCORRÊNCIAS PENDENTES REAIS
   ════════════════════════════════════════════════════════════════ */
$sqlPendentes = $pdo->query("
    SELECT
        o.id_ocorrencia         AS id,
        a.id_aluno               AS aluno_id,
        a.nome_aluno             AS aluno,
        t.desc_turma             AS turma,
        o.data_ocorrencia        AS data,
        o.horario                AS hora,
        d.desc_disciplina        AS disciplina,
        fu.nome_funcionario      AS professor,
        o.desc_ocorrencia        AS descricao,
        o.notificar_responsavel  AS notificar_responsavel,
        o.status                 AS status,
        GROUP_CONCAT(ti.num_item ORDER BY ti.num_item)               AS infracoes_ids,
        GROUP_CONCAT(ti.desc_ocorrencia ORDER BY ti.num_item SEPARATOR '; ') AS infracoes_texto
    FROM ocorrencias o
    JOIN alunos             a  ON a.id_aluno = o.id_aluno
    LEFT JOIN turma         t  ON t.id_turma = a.id_turma
    LEFT JOIN disciplinas   d  ON d.id_disciplina = o.id_disciplina
    LEFT JOIN funcionarios  fu ON fu.id_funcionario = o.id_funcionario
    LEFT JOIN ocorrencia_tipos ot ON ot.id_ocorrencia = o.id_ocorrencia
    LEFT JOIN tipo_ocorrencia  ti ON ti.id_tipo_ocorrencia = ot.id_tipo_ocorrencia
    WHERE o.status = 'pendente'
    GROUP BY o.id_ocorrencia
    ORDER BY o.data_ocorrencia DESC, o.horario DESC
");

$ocorrenciasPendentes = [];
foreach ($sqlPendentes->fetchAll() as $row) {
    $ocorrenciasPendentes[] = [
        'id'                    => $row['id'],
        'aluno_id'              => $row['aluno_id'],
        'aluno'                 => $row['aluno'],
        'turma'                 => $row['turma'] ?? '—',
        'data'                  => $row['data'],
        'hora'                  => substr($row['hora'], 0, 5),
        'disciplina'            => $row['disciplina'] ?? '',
        'professor'             => $row['professor'] ?? '',
        'infracoes'             => $row['infracoes_ids'] ? array_map('intval', explode(',', $row['infracoes_ids'])) : [],
        'infracoes_texto'       => $row['infracoes_texto'] ?? '',
        'descricao'             => $row['descricao'] ?? '',
        'notificar_responsavel' => (bool) $row['notificar_responsavel'],
        'resp_convocado'        => (bool) $row['notificar_responsavel'], // mesma flag do banco
        'status'                => $row['status'],
    ];
}

// Lista oficial de infrações vinda do banco (não hardcoded), usada na legenda
// da tabela e na folha de impressão.
$tiposInfracao = $pdo->query("SELECT num_item, desc_ocorrencia FROM tipo_ocorrencia ORDER BY num_item")
    ->fetchAll(PDO::FETCH_KEY_PAIR);

/* ── FUNÇÕES AUXILIARES ─────────────────────────────────────── */
function classeStatus(string $status): string {
    if ($status === 'resolvida') return 'status-entregue';
    return 'status-pendente';
}

function textoStatus(string $status): string {
    if ($status === 'resolvida') return 'Resolvida';
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

/* ── VARIÁVEIS PARA O HEADER UNIFICADO ─────────────────────── */
$pagina_atual         = 'pendentes';
$totalPendentesGlobal = $totalPendentes;
?>
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

        /* CORES DE STATUS */
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

        .td-acoes { display: flex; gap: 0.5rem; white-space: nowrap; align-items: center; }

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

        .btn-imprimir {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #4a5568;
            color: #fff;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 7px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-imprimir:hover { background: #2d3748; }
        .btn-imprimir svg { width: 16px; height: 16px; fill: currentColor; }

        /* ── MODAL DE STATUS ─────────────────────── */
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
            max-width: 480px;
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

        /* STATUS — apenas pendente/resolvida (bate com o ENUM do banco) */
        .status-toggle {
            display: grid;
            grid-template-columns: 1fr 1fr;
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

        /* ── SEÇÃO DE IMPRESSÃO BLOQUEADA NA TELA ─────────────── */
        #folha-impressao-container { display: none; }

        @media print {
            body * { display: none !important; }
            #folha-impressao-container, #folha-impressao-container * { display: block !important; }
            #folha-impressao-container {
                display: block !important;
                position: absolute;
                left: 0; top: 0; width: 100%;
                font-family: Arial, sans-serif;
                color: #000;
                padding: 10px;
            }
            .print-header {
                display: flex !important;
                align-items: center;
                border-bottom: 2px solid #000;
                padding-bottom: 8px;
                margin-bottom: 15px;
            }
            .print-logo-box {
                border: 2px solid #000;
                padding: 10px;
                font-weight: bold;
                font-size: 14px;
                text-align: center;
                margin-right: 15px;
                line-height: 1.2;
            }
            .print-header-text h2 { font-size: 16px; font-weight: bold; color: #0b2373 !important; }
            .print-header-text p { font-size: 11px; margin-top: 2px; }
            .print-cidade-data { text-align: right; font-size: 13px; margin-bottom: 15px; font-weight: 500; }
            .print-linha-aluno { display: flex !important; font-size: 14px; margin-bottom: 15px; width: 100%; }
            .print-input-fill { flex: 1; border-bottom: 1px solid #000; margin-left: 5px; padding-left: 5px; font-weight: bold; }
            .print-comunicado { font-size: 13px; margin-bottom: 12px; }
            .print-lista-infracoes { list-style: none; margin-bottom: 15px; }
            .print-lista-infracoes li { display: flex !important; align-items: flex-start; font-size: 12px; margin-bottom: 5px; line-height: 1.3; }
            .print-checkbox {
                width: 16px; height: 16px; border: 1.5px solid #000;
                margin-right: 8px; flex-shrink: 0; display: inline-flex !important;
                align-items: center; justify-content: center; font-weight: bold; font-size: 11px;
            }
            .print-obs { font-size: 13px; margin-bottom: 30px; border-bottom: 1px dashed #777; padding-bottom: 5px; }
            .print-assinaturas-row { display: flex !important; justify-content: space-between; margin-bottom: 25px; margin-top: 40px; }
            .print-col-assinatura { width: 45%; text-align: center; font-size: 12px; border-top: 1px solid #000; padding-top: 5px; }
            .print-divisor-recibo { border-top: 2px dashed #000; margin: 25px 0; padding-top: 20px; text-align: center; position: relative; }
            .print-recibo-titulo { font-size: 13px; font-weight: bold; letter-spacing: 1px; margin-bottom: 15px; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

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
                    <a href="perfil.php?id=<?= $a['id'] ?>" class="chip-aluno" style="text-decoration:none;">
                        <span class="dot dot-red"></span>
                        <?= htmlspecialchars($a['nome']) ?>
                    </a>
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
                <?php if (count($ocorrenciasPendentes) === 0): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:#718096; padding:2rem;">
                            Nenhuma ocorrência pendente no momento. 🎉
                        </td>
                    </tr>
                <?php else: ?>
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
                            <td class="td-infracoes"><?= htmlspecialchars($oc['infracoes_texto'] ?: textoInfracoes($oc['infracoes'], $tiposInfracao)) ?></td>
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

                                    <button type="button" class="btn-imprimir" title="Imprimir Ocorrência"
                                        data-print-aluno="<?= htmlspecialchars($oc['aluno']) ?>"
                                        data-print-turma="<?= htmlspecialchars($oc['turma']) ?>"
                                        data-print-data="<?= formatarData($oc['data']) ?>"
                                        data-print-horario="<?= htmlspecialchars($oc['hora']) ?> · <?= htmlspecialchars($oc['disciplina']) ?>"
                                        data-print-infracoes="<?= implode(',', $oc['infracoes']) ?>"
                                        data-print-obs="<?= htmlspecialchars($oc['descricao']) ?>"
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
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
                        <input type="radio" name="status" value="resolvida">
                        Resolvida
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

<div id="folha-impressao-container">
    <div class="print-header">
        <div class="print-logo-box">E.E.<br>M.L.O.</div>
        <div class="print-header-text">
            <h2>ESCOLA ESTADUAL MARIA DE LOURDES DE OLIVEIRA</h2>
            <p>Rua José Diório de Miranda, 549 – B. Mara Corneto – Tel.: 3382-2770</p>
        </div>
    </div>

    <div class="print-cidade-data">Belo Horizonte, <span id="print-f1-data"></span></div>

    <div class="print-linha-aluno">
        <span>Aluno(a):</span><div class="print-input-fill" id="print-f1-aluno"></div>
        <span style="margin-left: 20px;">Turma:</span><div class="print-input-fill" id="print-f1-turma" style="flex: 0 0 150px;"></div>
    </div>

    <p class="print-comunicado">Comunicamos que o (a) aluno (a) recebeu uma <strong>ocorrência disciplinar</strong> quanto a:</p>

    <ul class="print-lista-infracoes">
        <?php foreach ($tiposInfracao as $id => $texto): ?>
            <li>
                <div class="print-checkbox" id="chk-f1-<?= $id ?>"></div>
                ( ) <?= $id ?>. <?= htmlspecialchars($texto) ?>;
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="print-obs">
        <strong>Obs.:</strong> <span id="print-f1-obs"></span>
    </div>

    <div class="print-assinaturas-row">
        <div class="print-col-assinatura">Assinatura do professor ou supervisor pedagógico</div>
        <div class="print-col-assinatura">Assinatura do responsável</div>
    </div>

    <div class="print-divisor-recibo">
        <div class="print-recibo-titulo">ESCOLA ESTADUAL MARIA DE LOURDES DE OLIVEIRA<br><small>RECIBO DE RECEBIMENTO DA OCORRÊNCIA</small></div>
    </div>

    <div class="print-linha-aluno">
        <span>Aluno(a):</span><div class="print-input-fill" id="print-f2-aluno"></div>
        <span style="margin-left: 20px;">Turma:</span><div class="print-input-fill" id="print-f2-turma" style="flex: 0 0 150px;"></div>
    </div>

    <div class="print-linha-aluno" style="margin-top: 10px;">
        <span>Motivo ( <span id="print-f2-motivos"></span> ) — Horário:</span><div class="print-input-fill" id="print-f2-horario"></div>
    </div>

    <div class="print-assinaturas-row" style="margin-top: 50px; margin-bottom: 5px;">
        <div class="print-col-assinatura">Assinatura do vice-diretor ou supervisor</div>
        <div class="print-col-assinatura">Assinatura do responsável</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalOverlay = document.getElementById('modal-overlay');
    const formEditar = document.getElementById('form-editar-ocorrencia');
    const btnFechar = document.getElementById('modal-fechar');
    const btnCancelar = document.getElementById('modal-cancelar');
    const toast = document.getElementById('toast');

    const modalSubtitulo = document.getElementById('modal-subtitulo');
    const modalOccId = document.getElementById('modal-occ-id');

    function mostrarToast(msg) {
        toast.textContent = msg;
        toast.classList.add('toast-visivel');
        setTimeout(() => toast.classList.remove('toast-visivel'), 3000);
    }

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

    // Salvar — grava de verdade no banco via AJAX
    formEditar.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formEditar);
        formData.append('ajax_action', 'atualizar_status');

        try {
            const resp = await fetch(window.location.pathname, {
                method: 'POST',
                body: formData,
            });
            const data = await resp.json();

            if (data.ok) {
                fecharModal();
                mostrarToast('Status atualizado com sucesso!');
                setTimeout(() => window.location.reload(), 800);
            } else {
                mostrarToast('Erro ao salvar: ' + (data.erro || 'tente novamente.'));
            }
        } catch (err) {
            console.error('Erro ao salvar status:', err);
            mostrarToast('Erro de conexão ao salvar.');
        }
    });

    // Lógica de impressão dinâmica
    document.querySelectorAll('.btn-imprimir').forEach(botao => {
        botao.addEventListener('click', () => {
            const aluno = botao.getAttribute('data-print-aluno');
            const turma = botao.getAttribute('data-print-turma');
            const data = botao.getAttribute('data-print-data');
            const horario = botao.getAttribute('data-print-horario');
            const infracoesIds = botao.getAttribute('data-print-infracoes').split(',').filter(Boolean);
            const obs = botao.getAttribute('data-print-obs');

            document.getElementById('print-f1-aluno').textContent = aluno;
            document.getElementById('print-f1-turma').textContent = turma;
            document.getElementById('print-f1-data').textContent = data;
            document.getElementById('print-f1-obs').textContent = obs || 'Nenhuma.';

            document.getElementById('print-f2-aluno').textContent = aluno;
            document.getElementById('print-f2-turma').textContent = turma;
            document.getElementById('print-f2-horario').textContent = horario;
            document.getElementById('print-f2-motivos').textContent = infracoesIds.join(', ');

            document.querySelectorAll('.print-checkbox').forEach(cb => cb.textContent = '');

            infracoesIds.forEach(id => {
                const targetCheckbox = document.getElementById(`chk-f1-${id.trim()}`);
                if (targetCheckbox) {
                    targetCheckbox.textContent = '✓';
                }
            });

            window.print();
        });
    });
});
</script>
</body>
</html>