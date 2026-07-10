<?php

$db_host = "localhost";
$db_port = "3306";
$db_name = "ocosis";
$db_user = "root";
$db_pass = "";

try {
    // CORRIGIDO: a porta ($db_port) precisa entrar na DSN, senão o PDO
    // sempre tenta a porta padrão do MySQL (3306), ignorando o que você configurou.
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='padding:20px;font-family:sans-serif;'><h3>Erro ao conectar ao banco de dados.</h3><p>" . htmlspecialchars($e->getMessage()) . "</p></div>");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax_action'] ?? '') === 'atualizar_ocorrencia') {
    header('Content-Type: application/json; charset=utf-8');
 
    try {
        $id         = intval($_POST['id'] ?? 0);
        $status     = ($_POST['status'] ?? 'pendente') === 'resolvida' ? 'resolvida' : 'pendente';
        $descricao  = trim($_POST['descricao'] ?? '');
        $notif      = isset($_POST['notif_responsavel']) ? 1 : 0;
        $discNome   = trim($_POST['disciplina'] ?? '');
        $profNome   = trim($_POST['professor'] ?? '');
        $infracoes  = isset($_POST['infracoes']) ? array_map('intval', (array) $_POST['infracoes']) : [];
 
        if ($id === 0) {
            throw new Exception('ID da ocorrência inválido.');
        }
 
        // Campo vazio -> grava NULL (permite "limpar" disciplina/professor).
        // Campo preenchido mas não encontrado no banco -> erro explícito,
        // em vez de silenciosamente manter o valor antigo.
        $idDisciplina = null;
        if ($discNome !== '') {
            $s = $pdo->prepare("SELECT id_disciplina FROM disciplinas WHERE desc_disciplina = :d LIMIT 1");
            $s->execute(['d' => $discNome]);
            $idDisciplina = $s->fetchColumn();
            if ($idDisciplina === false) {
                throw new Exception("Disciplina '$discNome' não encontrada no banco de dados.");
            }
        }
 
        $idFuncionario = null;
        if ($profNome !== '') {
            $s = $pdo->prepare("SELECT id_funcionario FROM funcionarios WHERE nome_funcionario = :n LIMIT 1");
            $s->execute(['n' => $profNome]);
            $idFuncionario = $s->fetchColumn();
            if ($idFuncionario === false) {
                throw new Exception("Professor(a) '$profNome' não encontrado(a) no banco de dados.");
            }
        }
 
        $pdo->beginTransaction();
 
        $sql = "UPDATE ocorrencias
                   SET status = :status,
                       desc_ocorrencia = :descricao,
                       notificar_responsavel = :notif,
                       id_disciplina = :id_disciplina,
                       id_funcionario = :id_funcionario
                 WHERE id_ocorrencia = :id";
 
        $params = [
            'status'         => $status,
            'descricao'      => $descricao,
            'notif'          => $notif,
            'id_disciplina'  => $idDisciplina,
            'id_funcionario' => $idFuncionario,
            'id'             => $id,
        ];
 
        $pdo->prepare($sql)->execute($params);
 
        // Refaz o vínculo de infrações (ocorrencia_tipos)
        $pdo->prepare("DELETE FROM ocorrencia_tipos WHERE id_ocorrencia = :id")->execute(['id' => $id]);
 
        if (!empty($infracoes)) {
            $stmtTipo   = $pdo->prepare("SELECT id_tipo_ocorrencia FROM tipo_ocorrencia WHERE num_item = :n");
            $stmtInsert = $pdo->prepare("INSERT INTO ocorrencia_tipos (id_ocorrencia, id_tipo_ocorrencia) VALUES (:oc, :tp)");
            foreach ($infracoes as $numItem) {
                $stmtTipo->execute(['n' => $numItem]);
                $idTipo = $stmtTipo->fetchColumn();
                if ($idTipo) {
                    $stmtInsert->execute(['oc' => $id, 'tp' => $idTipo]);
                }
            }
        }
 
        $pdo->commit();
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}
 
/* ════════════════════════════════════════════════════════════════
   CARREGAMENTO NORMAL DA PÁGINA
   ════════════════════════════════════════════════════════════════ */
$aluno_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
 
if ($aluno_id === 0) {
    die("<div style='padding:20px; font-family:sans-serif;'><h3>Erro: Nenhum ID de aluno foi especificado para gerar o relatório.</h3><a href='pendentes.php'>Voltar para Pendentes</a></div>");
}
 
// Aluno + turma atual
$sqlAluno = $pdo->prepare("
    SELECT
        a.id_aluno       AS id,
        a.nome_aluno     AS nome,
        a.num_simade     AS simade,
        a.dt_nascimento  AS data_nascimento,
        t.desc_turma     AS turma_atual
    FROM alunos a
    LEFT JOIN turma t ON a.id_turma = t.id_turma
    WHERE a.id_aluno = :id
");
$sqlAluno->execute(['id' => $aluno_id]);
$aluno = $sqlAluno->fetch(PDO::FETCH_ASSOC);
 
if (!$aluno) {
    die("<div style='padding:20px; font-family:sans-serif;'><h3>Erro: Aluno não encontrado no banco de dados.</h3><a href='pendentes.php'>Voltar</a></div>");
}
 
$aluno['nascimento'] = isset($aluno['data_nascimento'])
    ? date('d/m/Y', strtotime($aluno['data_nascimento']))
    : '—';
 
// Histórico de ocorrências do aluno (com infrações agregadas)
$sqlOcorrencias = $pdo->prepare("
    SELECT
        o.id_ocorrencia         AS id,
        o.data_ocorrencia       AS data_registro,
        o.horario                AS horario,
        d.desc_disciplina        AS disciplina,
        fu.nome_funcionario      AS professor,
        o.desc_ocorrencia        AS descricao,
        o.status                 AS status,
        o.notificar_responsavel  AS notif_responsavel,
        GROUP_CONCAT(t.num_item ORDER BY t.num_item SEPARATOR ', ')       AS infracoes_ids,
        GROUP_CONCAT(t.desc_ocorrencia ORDER BY t.num_item SEPARATOR '; ') AS infracoes_texto
    FROM ocorrencias o
    LEFT JOIN disciplinas       d  ON o.id_disciplina = d.id_disciplina
    LEFT JOIN funcionarios      fu ON o.id_funcionario = fu.id_funcionario
    LEFT JOIN ocorrencia_tipos  ot ON ot.id_ocorrencia = o.id_ocorrencia
    LEFT JOIN tipo_ocorrencia   t  ON t.id_tipo_ocorrencia = ot.id_tipo_ocorrencia
    WHERE o.id_aluno = :aluno_id
    GROUP BY o.id_ocorrencia
    ORDER BY o.data_ocorrencia DESC, o.horario DESC
");
$sqlOcorrencias->execute(['aluno_id' => $aluno_id]);
$ocorrencias = $sqlOcorrencias->fetchAll(PDO::FETCH_ASSOC);
 
$historicoOcorrencias = [];
foreach ($ocorrencias as $row) {
    $materiaProfessor = trim(($row['disciplina'] ?? '—') . ' / ' . ($row['professor'] ?? '—'), ' /');
    if ($materiaProfessor === '') {
        $materiaProfessor = '—';
    }
 
    $infracoesArr = $row['infracoes_ids']
        ? array_map('intval', explode(',', $row['infracoes_ids']))
        : [];
 
    $historicoOcorrencias[] = [
        'id'                => $row['id'],
        'data_formatada'    => date('d/m/Y', strtotime($row['data_registro'])),
        'hora_formatada'    => substr($row['horario'], 0, 5),
        'materia_professor' => $materiaProfessor,
        'disciplina'        => $row['disciplina'] ?? '',
        'professor'         => $row['professor'] ?? '',
        'descricao'         => $row['descricao'] ?? '',
        'infracoes_ids'     => $row['infracoes_ids'] ?? '',
        'infracoes_arr'     => $infracoesArr,
        'infracoes_texto'   => $row['infracoes_texto'] ?? ($row['descricao'] ?: '—'),
        'status'            => $row['status'],
        'notif_responsavel' => (int) $row['notif_responsavel'],
    ];
}
 
$totalOcorrencias = count($historicoOcorrencias);
$totalPendentes   = count(array_filter($historicoOcorrencias, fn($o) => $o['status'] === 'pendente'));
 
// Conta a frequência de cada tipo de infração no histórico e escolhe
// a de maior contagem (em vez de simplesmente pegar a mais recente).
$contagemInfracoes = [];
foreach ($historicoOcorrencias as $oc) {
    foreach ($oc['infracoes_arr'] as $numItem) {
        $contagemInfracoes[$numItem] = ($contagemInfracoes[$numItem] ?? 0) + 1;
    }
}
 
$maisReincidente = 'Nenhuma infração registrada';
if (!empty($contagemInfracoes)) {
    arsort($contagemInfracoes);
    $numItemTopo = array_key_first($contagemInfracoes);
 
    foreach ($historicoOcorrencias as $oc) {
        $idx = array_search($numItemTopo, $oc['infracoes_arr'], true);
        if ($idx !== false) {
            $textos = explode(';', $oc['infracoes_texto']);
            $maisReincidente = trim($textos[$idx] ?? $oc['infracoes_texto']);
            break;
        }
    }
}
 
// Total de ocorrências pendentes no sistema todo (badge da navbar unificada)
$totalPendentesGlobal = (int) $pdo->query("SELECT COUNT(*) FROM ocorrencias WHERE status = 'pendente'")->fetchColumn();
 
// Disciplinas e professores reais (pra popular os <select> do modal de edição)
$disciplinasDb = $pdo->query("SELECT desc_disciplina FROM disciplinas ORDER BY desc_disciplina")->fetchAll(PDO::FETCH_COLUMN);
$professoresDb = $pdo->query("SELECT nome_funcionario FROM funcionarios WHERE cargo_funcionario LIKE 'Professor%' ORDER BY nome_funcionario")->fetchAll(PDO::FETCH_COLUMN);
 
// Lista oficial de infrações — vem direto da tabela tipo_ocorrencia,
// então fica sempre igual ao que está cadastrado no banco.
$tiposInfracaoModal = $pdo->query("SELECT num_item, desc_ocorrencia FROM tipo_ocorrencia ORDER BY num_item")
    ->fetchAll(PDO::FETCH_KEY_PAIR);
 
// Usado pelo header.php pra destacar o item certo no menu e montar os
// caminhos relativos (perfil.php está uma pasta abaixo da raiz do app).
$base_path   = '../';
$pagina_atual = 'pendentes';
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
 
        /* ── IMPRESSÃO DE UMA ÚNICA OCORRÊNCIA ─────────────── */
        body.imprimir-uma-ocorrencia .card-resumo,
        body.imprimir-uma-ocorrencia .section-title { display: none; }
        body.imprimir-uma-ocorrencia .cards-grid { grid-template-columns: 1fr; }
        body.imprimir-uma-ocorrencia .ocorrencias-table tbody tr { display: none; }
        body.imprimir-uma-ocorrencia .ocorrencias-table tbody tr.linha-imprimir-ativa { display: table-row; }
 
        @media print {
            .navbar, .unified-navbar, .top-actions, .btn-imprimir-todas, .actions-cell, .toast {
                display: none !important;
            }
            body { background: #fff; }
            .main { padding: 0; max-width: 100%; }
            .table-card { box-shadow: none; border: 1px solid #e2e8f0; }
        }
 
        /* ── MODAL DE CONFIRMAÇÃO ────────────────────────── */
        .modal-confirmacao-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.55);
            display: flex; align-items: center; justify-content: center; z-index: 300; padding: 1rem;
        }
        .modal-confirmacao-overlay[hidden] { display: none; }
        .modal-confirmacao {
            background: #fff; border-radius: 14px; width: 100%; max-width: 380px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25); padding: 1.75rem 1.5rem 1.4rem;
            display: flex; flex-direction: column; gap: 0.6rem; text-align: center;
        }
        .modal-confirmacao-icone { font-size: 2rem; line-height: 1; }
        .modal-confirmacao h3 { font-size: 1.05rem; font-weight: 700; color: #1a202c; }
        .modal-confirmacao p  { font-size: 0.87rem; color: #718096; line-height: 1.5; }
        .modal-confirmacao-acoes { display: flex; gap: 0.75rem; justify-content: center; margin-top: 0.5rem; }
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
            position: fixed; inset: 0; background: rgba(0,0,0,0.45);
            display: flex; align-items: flex-start; justify-content: center;
            z-index: 200; padding: 1.5rem 1rem; overflow-y: auto;
        }
        .modal-overlay[hidden] { display: none; }
        .modal-editar {
            background: #fff; border-radius: 14px; width: 100%; max-width: 560px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2); display: flex; flex-direction: column; margin: auto;
        }
        .modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem 1.5rem; border-bottom: 1.5px solid #e8ecf2;
            position: sticky; top: 0; background: #fff; z-index: 1; border-radius: 14px 14px 0 0;
        }
        .modal-header h2 { font-size: 1.1rem; font-weight: 700; color: #1a202c; }
        .modal-fechar {
            background: none; border: none; cursor: pointer; color: #718096;
            font-size: 1.4rem; line-height: 1; padding: 0.1rem 0.3rem; border-radius: 4px; transition: color 0.15s, background 0.15s;
        }
        .modal-fechar:hover { color: #1a202c; background: #f1f5f9; }
        .modal-subtitulo { padding: 0.75rem 1.5rem; background: #f8fafd; border-bottom: 1px solid #e8ecf2; font-size: 0.88rem; color: #4a5568; }
        .modal-corpo { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1.1rem; }
        .campo-grupo { display: flex; flex-direction: column; gap: 0.45rem; }
        .campo-label { font-size: 0.82rem; font-weight: 700; color: #4a5568; text-transform: uppercase; letter-spacing: 0.03em; }
        .status-toggle { display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .status-opcao {
            flex: 1; min-width: 130px; display: flex; align-items: center; gap: 0.6rem;
            border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.7rem 1rem;
            cursor: pointer; font-size: 0.88rem; font-weight: 600; color: #4a5568; transition: border-color 0.15s, background 0.15s;
        }
        .status-opcao input[type="radio"] { accent-color: #1a56db; width: 16px; height: 16px; }
        .status-opcao:has(input:checked).status-opcao-pendente  { border-color: #e53e3e; background: #fff5f5; color: #c53030; }
        .status-opcao:has(input:checked).status-opcao-resolvida { border-color: #38a169; background: #f0fff4; color: #276749; }
        .campo-select {
            width: 100%; padding: 0.6rem 0.9rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 0.9rem; font-family: inherit; color: #2d3748; background: #fff; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23718096' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 0.85rem center; cursor: pointer; transition: border-color 0.15s;
        }
        .campo-select:focus { outline: none; border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,0.12); }
        .infracoes-lista { border: 1.5px solid #e2e8f0; border-radius: 8px; max-height: 190px; overflow-y: auto; padding: 0.25rem 0; }
        .infracao-item { display: flex; align-items: flex-start; gap: 0.7rem; padding: 0.5rem 0.9rem; cursor: pointer; font-size: 0.87rem; color: #2d3748; transition: background 0.12s; }
        .infracao-item:hover { background: #f8fafd; }
        .infracao-item input[type="checkbox"] { accent-color: #1a56db; margin-top: 2px; flex-shrink: 0; width: 15px; height: 15px; }
        .campo-textarea {
            width: 100%; padding: 0.6rem 0.9rem; border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 0.9rem; font-family: inherit; color: #2d3748; resize: vertical; min-height: 80px; transition: border-color 0.15s;
        }
        .campo-textarea:focus { outline: none; border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,0.12); }
        .notif-box { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.9rem 1rem; display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; }
        .notif-box input[type="checkbox"] { accent-color: #1a56db; width: 16px; height: 16px; margin-top: 2px; flex-shrink: 0; }
        .notif-box-texto strong { font-size: 0.88rem; font-weight: 700; color: #1a202c; display: block; }
        .notif-box-texto span { font-size: 0.78rem; color: #718096; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.1rem 1.5rem; border-top: 1.5px solid #e8ecf2; }
        .btn-cancelar {
            background: #fff; color: #4a5568; border: 1.5px solid #e2e8f0; padding: 0.55rem 1.3rem; border-radius: 8px;
            font-size: 0.88rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: background 0.15s;
        }
        .btn-cancelar:hover { background: #f7fafc; }
        .btn-salvar {
            background: #1a56db; color: #fff; border: none; padding: 0.55rem 1.5rem; border-radius: 8px;
            font-size: 0.88rem; font-weight: 700; cursor: pointer; font-family: inherit; transition: background 0.15s;
        }
        .btn-salvar:hover { background: #1648c0; }
        .campos-duplos { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        @media (max-width: 500px) { .campos-duplos { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
 
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Mesma checagem usada nas outras páginas do sistema: o login guarda
    // o estado no sessionStorage do navegador, não em $_SESSION do PHP.
    const masp  = sessionStorage.getItem('masp_logado');
    const cargo = sessionStorage.getItem('cargo_logado');
 
    if (!masp) {
        alert("Você precisa fazer login primeiro!");
        // visualizar_relatorio/ está no mesmo nível de login_screen/, então "../" basta
        window.location.href = "../login_screen/login.html";
        return;
    }
 
    const infoUsuario = document.getElementById('info-usuario');
    if (infoUsuario) {
        infoUsuario.innerHTML = `Logado como: <strong>${cargo}</strong> (MASP: ${masp})`;
    }
});
</script>
 
<?php require __DIR__ . '/../header.php'; ?>
 
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
            <p class="info-item"><strong>Turma Atual:</strong> <?= htmlspecialchars($aluno['turma_atual'] ?? '—') ?></p>
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
                        <tr
                            data-id="<?= $oc['id'] ?>"
                            data-occ='<?= htmlspecialchars(json_encode([
                                'id'                => $oc['id'],
                                'aluno'             => $aluno['nome'],
                                'turma'             => $aluno['turma_atual'] ?? '',
                                'data'              => $oc['data_formatada'],
                                'hora'              => $oc['hora_formatada'],
                                'status'            => $oc['status'],
                                'disciplina'        => $oc['disciplina'] ?? '',
                                'professor'         => $oc['professor'] ?? '',
                                'descricao'         => $oc['descricao'] ?? '',
                                'infracoes'         => $oc['infracoes_arr'] ?? [],
                                'notif_responsavel' => $oc['notif_responsavel'],
                            ]), ENT_QUOTES, 'UTF-8') ?>'
                        >
                            <td><?= $oc['data_formatada'] ?></td>
                            <td><?= $oc['hora_formatada'] ?></td>
                            <td><?= htmlspecialchars($oc['materia_professor'] ?? '—') ?></td>
                            <td>
                                <div class="infracao-tag-container">
                                    <div class="infracao-ids">
                                        <?php if ($oc['infracoes_ids'] !== ''): ?>
                                            <?php foreach (explode(',', $oc['infracoes_ids']) as $id): ?>
                                                <span><?= htmlspecialchars(trim($id)) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span>—</span>
                                        <?php endif; ?>
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
            <!-- id="modal-occ-id" (com hífen) — precisa bater exatamente com o
                 seletor usado no perfil.js, senão o clique em "Editar" quebra -->
            <input type="hidden" id="modal-occ-id" name="id">
            <div class="modal-corpo">
 
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
 
                <div class="campos-duplos">
                    <div class="campo-grupo">
                        <label class="campo-label" for="modal-disciplina">Disciplina</label>
                        <select id="modal-disciplina" name="disciplina" class="campo-select">
                            <option value="">Selecione...</option>
                            <?php foreach ($disciplinasDb as $d): ?>
                                <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo-grupo">
                        <label class="campo-label" for="modal-professor">Professor(a)</label>
                        <select id="modal-professor" name="professor" class="campo-select">
                            <option value="">Selecione...</option>
                            <?php foreach ($professoresDb as $p): ?>
                                <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
 
                <div class="campo-grupo">
                    <span class="campo-label">Tipo(s) de Infração</span>
                    <div class="infracoes-lista" id="modal-infracoes-lista">
                        <?php foreach ($tiposInfracaoModal as $numItem => $descInf): ?>
                            <label class="infracao-item">
                                <input type="checkbox" name="infracoes[]" value="<?= $numItem ?>">
                                <span><strong><?= $numItem ?>.</strong> <?= htmlspecialchars($descInf) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
 
                <div class="campo-grupo">
                    <label class="campo-label" for="modal-descricao">Descrição / Observações</label>
                    <textarea id="modal-descricao" name="descricao" class="campo-textarea" placeholder="Descreva o ocorrido..."></textarea>
                </div>
 
                <label class="notif-box">
                    <input type="checkbox" id="modal-notif" name="notif_responsavel" value="1">
                    <div class="notif-box-texto">
                        <strong>16. Notificar responsável</strong>
                        <span>Aparecerá na impressão da folha</span>
                    </div>
                </label>
 
            </div>
 
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
 