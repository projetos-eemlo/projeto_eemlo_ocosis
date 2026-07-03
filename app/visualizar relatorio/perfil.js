/**
 * OCOSIS — Perfil do Aluno
 * Modal de edição de ocorrência (status, disciplina, professor, infrações,
 * descrição, notificar responsável), modal de confirmação antes de salvar,
 * e impressão da Folha de Ocorrência oficial (mesmo padrão de pendentes.js).
 *
 * IMPORTANTE: a lista TODAS_INFRACOES abaixo precisa ficar igual ao array
 * $tiposInfracaoModal do perfil.php e à tabela tipo_ocorrencia do banco.
 * Se adicionar/mudar uma infração lá, atualize aqui também.
 */

const TODAS_INFRACOES = {
    1:  'Indisciplina durante a aula de',
    2:  'Desrespeitou o(a) professor(a)',
    3:  'Agrediu o(a) colega',
    4:  'Não trouxe o material necessário',
    5:  'Não fez as atividades e/ou trabalho solicitado',
    6:  'Tem deixado as atividades de sala incompletas',
    7:  'Chegou atrasado, após o horário de entrada permitido',
    8:  'Chegou atrasado na sala, no horário do(a) professor(a)',
    9:  'Praticou bullying',
    10: 'Atrapalha o bom andamento das aulas com brincadeiras inadequadas/comportamento inconveniente',
    11: 'Fez uso do celular ou outro aparelho eletrônico durante as aulas',
    12: 'Não estava usando uniforme',
    13: 'Estava usando roupas inadequadas para o ambiente escolar',
    14: 'Estava "matando aula" do(a) professor(a)',
    15: 'Se envolveu em boatos e fofocas, causando transtornos na convivência escolar',
    16: 'É preciso que o(a) responsável compareça à escola e procure __________ na data ___/___/________, horário _________',
    17: 'Outros (especificado abaixo)',
};

document.addEventListener('DOMContentLoaded', () => {
    /* ============ ELEMENTOS ============ */
    const modalOverlay      = document.getElementById('modal-overlay');
    const formEditar        = document.getElementById('form-editar-ocorrencia');
    const btnFechar          = document.getElementById('modal-fechar');
    const btnCancelar        = document.getElementById('modal-cancelar');
    const toast               = document.getElementById('toast');
    const modalSubtitulo     = document.getElementById('modal-subtitulo');
    const modalOccId          = document.getElementById('modal-occ-id');

    const confOverlay        = document.getElementById('modal-confirmacao-overlay');
    const btnConfirmarSim    = document.getElementById('btn-confirmar-sim');
    const btnConfirmarNao    = document.getElementById('btn-confirmar-nao');

    const btnImprimirTodas   = document.querySelector('.btn-imprimir-todas');

    /* ============ HELPERS ============ */
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function mostrarToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.classList.add('toast-visivel');
        setTimeout(() => toast.classList.remove('toast-visivel'), 3000);
    }

    // A linha <tr> carrega o JSON completo em data-occ; os botões (Editar/Imprimir)
    // ficam dentro da linha, então buscamos os dados a partir da linha mais próxima.
    function dadosDaLinha(botao) {
        const tr = botao.closest('tr[data-occ]');
        if (!tr) return null;
        try {
            return JSON.parse(tr.dataset.occ);
        } catch (e) {
            console.error('Não foi possível ler os dados da ocorrência:', e);
            return null;
        }
    }

    // Os <select> de disciplina/professor usam texto fixo como valor
    // ("Prof. William", "Inglês"...), não IDs do banco — seleciona pelo texto.
    function selecionarPorTexto(selectEl, texto) {
        if (!selectEl) return;
        const opcao = Array.from(selectEl.options).find(
            (o) => o.value.trim().toLowerCase() === (texto || '').trim().toLowerCase()
        );
        selectEl.value = opcao ? opcao.value : '';
    }

    /* ============ MODAL: EDITAR OCORRÊNCIA ============ */
    document.querySelectorAll('.btn-action-editar').forEach((botao) => {
        botao.addEventListener('click', () => {
            const oc = dadosDaLinha(botao);
            if (!oc) return;

            modalSubtitulo.innerHTML =
                `<strong>${escapeHtml(oc.aluno)}</strong> · ${escapeHtml(oc.data)} · ${escapeHtml(oc.hora)} · ${escapeHtml(oc.turma)}`;

            formEditar.reset();
           // 🔄 SUBSTITUA ISSO:
// modalOccId.value = oc.id;

// 🚀 POR ISTO (Busca o campo diretamente no momento do clique):
const campoId = document.getElementById('modalOccId') || document.querySelector('#form-editar-ocorrencia input[name="id"]');
if (campoId) {
    campoId.value = oc.id;
} else {
    console.error("O campo oculto com o ID da ocorrência não foi encontrado no HTML!");
}
            // Status
            const radio = formEditar.querySelector(`input[name="status"][value="${oc.status}"]`);
            if (radio) radio.checked = true;

            // Disciplina / Professor
            selecionarPorTexto(document.getElementById('modal-disciplina'), oc.disciplina);
            selecionarPorTexto(document.getElementById('modal-professor'), oc.professor);

            // Infrações (checkboxes)
            const idsMarcados = new Set((oc.infracoes || []).map(Number));
            formEditar.querySelectorAll('input[name="infracoes[]"]').forEach((chk) => {
                chk.checked = idsMarcados.has(Number(chk.value));
            });

            // Descrição
            document.getElementById('modal-descricao').value = oc.descricao || '';

            // Notificar responsável
            document.getElementById('modal-notif').checked = !!oc.notif_responsavel;

            modalOverlay.removeAttribute('hidden');
        });
    });

    function fecharModal() {
        modalOverlay.setAttribute('hidden', 'true');
        formEditar.reset();
    }

    btnFechar.addEventListener('click', fecharModal);
    btnCancelar.addEventListener('click', fecharModal);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) fecharModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modalOverlay.hasAttribute('hidden')) fecharModal();
    });

    /* ============ SUBMIT -> MODAL DE CONFIRMAÇÃO ============ */
    formEditar.addEventListener('submit', (e) => {
        e.preventDefault();
        modalOverlay.setAttribute('hidden', 'true');
        confOverlay.removeAttribute('hidden');
    });

    btnConfirmarNao.addEventListener('click', () => {
        confOverlay.setAttribute('hidden', 'true');
        modalOverlay.removeAttribute('hidden'); // volta pro modal de edição
    });

    btnConfirmarSim.addEventListener('click', () => {
        confOverlay.setAttribute('hidden', 'true');

        // TODO: substituir pela chamada real ao back-end, por exemplo:
        // const formData = new FormData(formEditar);
        // fetch('atualizar_ocorrencia.php', {
        //     method: 'POST',
        //     body: new URLSearchParams(formData),
        // }).then(() => window.location.reload()).catch(...);
        // Por enquanto, é só uma simulação em tela (sem persistir no banco).

        fecharModal();
        mostrarToast('Ocorrência atualizada com sucesso!');
    });

    /* ============ IMPRESSÃO — FOLHA DE OCORRÊNCIA ============ */
    document.querySelectorAll('.btn-action-print').forEach((botao) => {
        botao.addEventListener('click', () => {
            const oc = dadosDaLinha(botao);
            if (oc) imprimirFolhaDeOcorrencia(oc);
        });
    });

    if (btnImprimirTodas) {
        btnImprimirTodas.addEventListener('click', () => window.print());
    }

    function imprimirFolhaDeOcorrencia(oc) {
        const marcados = new Set((oc.infracoes || []).map(Number));

        // Divide as 17 infrações no layout oficial
        const linhasInfracoes = Object.entries(TODAS_INFRACOES).map(([id, texto]) => {
            const checked = marcados.has(Number(id)) ? '☑' : '☐';
            let textoFormatado = texto;
            if (id == "16") {
                textoFormatado = 'É preciso que o(a) responsável compareça à escola e procure ______________________ na data ____/____/________, horário _________';
            }
            return `<div class="folha-infracao">${checked} ( ) <span>${id}.</span> ${textoFormatado}</div>`;
        }).join('');

        const janela = window.open('', '_blank', 'width=850,height=1000');
        if (!janela) {
            mostrarToast('Permita pop-ups para imprimir a folha.');
            return;
        }

        janela.document.write(`
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Folha de Ocorrência — ${escapeHtml(oc.aluno)}</title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; color:#000; padding: 20px; margin: 0; line-height: 1.4; }

    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .header-logo { border: 1.5px solid #000; width: 80px; text-align: center; font-weight: bold; font-size: 11px; padding: 10px 5px; }
    .header-title { border: 1.5px solid #000; border-left: none; padding-left: 15px; vertical-align: middle; }
    .header-title h1 { font-size: 15px; margin: 0; color: #0b2265; font-weight: bold; font-family: 'Arial Black', Gadget, sans-serif; }
    .header-title p { font-size: 11px; margin: 3px 0 0 0; color: #333; }

    .data-local { text-align: right; font-size: 13px; margin-bottom: 15px; }
    .info-aluno { font-size: 13px; margin-bottom: 15px; display: flex; justify-content: space-between; }
    .linha-campo { border-bottom: 1px solid #000; display: inline-block; padding-bottom: 1px; }

    .comunicado { font-size: 13px; margin-bottom: 15px; }
    .container-infracoes { margin-bottom: 15px; }
    .folha-infracao { font-size: 11.5px; padding: 2px 0; display: flex; align-items: flex-start; }
    .folha-infracao span { font-weight: bold; margin-right: 4px; }

    .obs-secao { font-size: 13px; margin: 15px 0; }
    .obs-campo { border-bottom: 1px dashed #999; padding-bottom: 3px; display: inline-block; width: 85%; }

    .folha-assinaturas { margin-top: 40px; display: flex; justify-content: space-between; }
    .linha-assinatura { border-top: 1px solid #000; text-align: center; font-size: 11px; width: 45%; padding-top: 5px; }

    .linha-recorte { border-top: 2px dashed #000; margin: 35px 0 20px 0; position: relative; }

    .recibo-titulo { text-align: center; font-size: 12px; font-weight: bold; margin-bottom: 15px; }
    .recibo-titulo span { display: block; font-size: 10px; font-weight: normal; }

    @media print {
        body { padding: 10px; }
        .linha-recorte { margin: 30px 0 15px 0; }
    }
</style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">E.E.<br>M.L.O.</td>
            <td class="header-title">
                <h1>ESCOLA ESTADUAL MARIA DE LOURDES DE OLIVEIRA</h1>
                <p>Rua José Diório de Miranda, 549 – B. Mara Corneto – Tel.: 3382-2770</p>
            </td>
        </tr>
    </table>

    <div class="data-local">Belo Horizonte, ${escapeHtml(oc.data)}</div>

    <div class="info-aluno">
        <div style="width: 70%;">Aluno(a): <span class="linha-campo" style="width: 85%;">${escapeHtml(oc.aluno)}</span></div>
        <div style="width: 28%;">Turma: <span class="linha-campo" style="width: 75%;">${escapeHtml(oc.turma)}</span></div>
    </div>

    <div class="comunicado">
        Comunicamos que o (a) aluno (a) recebeu uma <strong>ocorrência disciplinar</strong> quanto a:
    </div>

    <div class="container-infracoes">
        ${linhasInfracoes}
    </div>

    <div class="obs-secao">
        <strong>Obs.:</strong> <span class="obs-campo">${escapeHtml(oc.descricao || 'Nenhuma observação adicional.')}</span>
    </div>

    <div class="folha-assinaturas">
        <div class="linha-assinatura">Assinatura do professor ou supervisor pedagógico</div>
        <div class="linha-assinatura">Assinatura do responsável</div>
    </div>

    <div class="linha-recorte"></div>

    <table class="header-table" style="margin-bottom: 10px;">
        <tr>
            <td class="header-logo" style="padding: 5px;">E.E.<br>M.L.O.</td>
            <td class="header-title" style="padding-left: 10px;">
                <div style="font-size: 12px; font-weight: bold; color: #0b2265;">ESCOLA ESTADUAL MARIA DE LOURDES DE OLIVEIRA</div>
                <div style="font-size: 10px; font-weight: bold; letter-spacing: 0.5px;">RECIBO DE RECEBIMENTO DA OCORRÊNCIA</div>
            </td>
        </tr>
    </table>

    <div class="info-aluno" style="margin-bottom: 10px;">
        <div style="width: 70%;">Aluno(a): <span class="linha-campo" style="width: 85%;">${escapeHtml(oc.aluno)}</span></div>
        <div style="width: 28%;">Turma: <span class="linha-campo" style="width: 75%;">${escapeHtml(oc.turma)}</span></div>
    </div>

    <div style="font-size: 13px; margin-bottom: 35px;">
        Motivo ( ${escapeHtml((oc.infracoes || []).join(', ') || '—')} ) — <strong>${escapeHtml(oc.hora || '')}</strong>
    </div>

    <div class="folha-assinaturas" style="margin-top: 25px;">
        <div class="linha-assinatura">Assinatura do vice-diretor ou supervisor</div>
        <div class="linha-assinatura">Assinatura do responsável</div>
    </div>

</body>
</html>
        `);
        janela.document.close();
        janela.focus();
        janela.onload = () => {
            janela.print();
        };
    }
});