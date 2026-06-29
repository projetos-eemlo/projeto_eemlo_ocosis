/**
 * OCOSIS - Comportamentos da tela "Visualizar Relatório" (perfil do aluno).
 * Escopo: SOMENTE esta tela — não interfere em pendentes.php
 * nem em nenhuma outra página do projeto.
 *
 * Funcionalidades:
 * 1. Imprimir o relatório completo (🖨️ Imprimir Todas)
 * 2. Imprimir uma única ocorrência (🖨️ na linha da tabela)
 * 3. Abrir modal de edição da ocorrência (✏️ Editar) — direto no perfil
 */

document.addEventListener('DOMContentLoaded', () => {
    initImprimirTudo();
    initImprimirOcorrencia();
    initModalEditar();
});

/* ════════════════════════════════════════════════════════════
   1. IMPRIMIR O RELATÓRIO COMPLETO
   ════════════════════════════════════════════════════════════ */
function initImprimirTudo() {
    const botao = document.querySelector('.btn-imprimir-todas');
    if (!botao) return;

    botao.addEventListener('click', () => {
        window.print();
    });
}

/* ════════════════════════════════════════════════════════════
   2. IMPRIMIR UMA ÚNICA OCORRÊNCIA
   ════════════════════════════════════════════════════════════ */
function initImprimirOcorrencia() {
    const botoesImprimir = document.querySelectorAll('.btn-action-print');
    if (!botoesImprimir.length) return;

    botoesImprimir.forEach((botao) => {
        botao.addEventListener('click', () => {
            const linha = botao.closest('tr');
            if (!linha) return;

            linha.classList.add('linha-imprimir-ativa');
            document.body.classList.add('imprimir-uma-ocorrencia');

            window.print();
        });
    });

    window.addEventListener('afterprint', limparEstadoDeImpressao);
}

function limparEstadoDeImpressao() {
    document.body.classList.remove('imprimir-uma-ocorrencia');
    document.querySelectorAll('.linha-imprimir-ativa').forEach((linha) => {
        linha.classList.remove('linha-imprimir-ativa');
    });
}

/* ════════════════════════════════════════════════════════════
   3. MODAL DE EDIÇÃO — abre direto no perfil do aluno
   ════════════════════════════════════════════════════════════ */
function initModalEditar() {
    const overlay      = document.getElementById('modal-overlay');
    const form         = document.getElementById('form-editar-ocorrencia');
    const btnFechar    = document.getElementById('modal-fechar');
    const btnCancelar  = document.getElementById('modal-cancelar');
    const subtitulo    = document.getElementById('modal-subtitulo');
    const selDisciplina = document.getElementById('modal-disciplina');
    const selProfessor  = document.getElementById('modal-professor');
    const txtDescricao  = document.getElementById('modal-descricao');
    const chkNotif      = document.getElementById('modal-notif');

    if (!overlay || !form) return;

    // Abre o modal ao clicar em ✏️ Editar
    document.querySelectorAll('.btn-action-editar').forEach((botao) => {
        botao.addEventListener('click', () => {
            const linha = botao.closest('tr');
            if (!linha) return;

            // — Subtítulo com info da ocorrência —
            const data     = linha.querySelector('td:nth-child(1)')?.textContent.trim() ?? '';
            const hora     = linha.querySelector('td:nth-child(2)')?.textContent.trim() ?? '';
            const materia  = linha.querySelector('td:nth-child(3)')?.textContent.trim() ?? '';

            subtitulo.innerHTML =
                `<strong>${document.querySelector('.profile-title')?.textContent.replace('Perfil: ', '') ?? ''}</strong>` +
                ` · ${data} · ${hora} · ${materia}`;

            // — Pré-preenche Status —
            const statusAtual = linha.querySelector('.status-pill')?.classList.contains('status-pendente')
                ? 'pendente' : 'resolvida';
            const radioStatus = form.querySelector(`input[name="status"][value="${statusAtual}"]`);
            if (radioStatus) radioStatus.checked = true;

            // — Pré-preenche Disciplina e Professor a partir do texto "Disciplina / Prof. X" —
            const [disciplinaTexto, professorTexto] = materia.split(' / ').map(s => s.trim());
            selecionarOpcaoTexto(selDisciplina, disciplinaTexto);
            selecionarOpcaoTexto(selProfessor, professorTexto);

            // — Pré-marca as infrações a partir dos IDs na linha —
            const checkboxes = form.querySelectorAll('input[name="infracoes[]"]');
            checkboxes.forEach(cb => cb.checked = false);

            const infIds = linha.querySelector('.infracao-ids');
            if (infIds) {
                infIds.querySelectorAll('span').forEach(span => {
                    const id = span.textContent.trim();
                    const cb = form.querySelector(`input[name="infracoes[]"][value="${id}"]`);
                    if (cb) cb.checked = true;
                });
            }

            // — Pré-preenche descrição —
            const descricaoTexto = linha.querySelector('.infracao-texto')?.textContent.trim() ?? '';
            txtDescricao.value = descricaoTexto;

            // — Notificar responsável —
            const temNotif = linha.querySelector('.sub-notif') !== null;
            chkNotif.checked = temNotif;

            // — Armazena referência à linha para atualizar após salvar —
            overlay.dataset.linhaAtual = Array.from(
                linha.parentElement.children
            ).indexOf(linha);

            overlay.removeAttribute('hidden');
            // Foca no primeiro radio para acessibilidade
            form.querySelector('input[name="status"]')?.focus();
        });
    });

    // Fecha o modal
    const fecharModal = () => {
        overlay.setAttribute('hidden', 'true');
        form.reset();
    };

    btnFechar.addEventListener('click', fecharModal);
    btnCancelar.addEventListener('click', fecharModal);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) fecharModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !overlay.hasAttribute('hidden')) fecharModal();
    });

    // Salva (simulação — substituir pelo fetch real quando o back-end estiver pronto)
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        abrirConfirmacao(() => {
            fecharModal();
            mostrarToast('Ocorrência atualizada com sucesso!');
        });
    });
}

/* ════════════════════════════════════════════════════════════
   4. MODAL DE CONFIRMAÇÃO
   Aparece ao clicar em "Salvar Alterações", pedindo uma
   segunda confirmação antes de efetivar a operação.
   Recebe um callback que é executado apenas se o usuário
   clicar em "Confirmar".
   ════════════════════════════════════════════════════════════ */
function abrirConfirmacao(onConfirmar) {
    const overlay   = document.getElementById('modal-confirmacao-overlay');
    const btnSim    = document.getElementById('btn-confirmar-sim');
    const btnNao    = document.getElementById('btn-confirmar-nao');

    if (!overlay || !btnSim || !btnNao) {
        // Fallback: se o HTML não tiver o modal, confirma direto
        onConfirmar();
        return;
    }

    overlay.removeAttribute('hidden');
    btnSim.focus();

    // Clona os botões para remover listeners anteriores (evita acúmulo)
    const novoBtnSim = btnSim.cloneNode(true);
    const novoBtnNao = btnNao.cloneNode(true);
    btnSim.replaceWith(novoBtnSim);
    btnNao.replaceWith(novoBtnNao);

    const fecharConfirmacao = () => overlay.setAttribute('hidden', 'true');

    novoBtnSim.addEventListener('click', () => {
        fecharConfirmacao();
        onConfirmar();
    });

    novoBtnNao.addEventListener('click', fecharConfirmacao);

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) fecharConfirmacao();
    }, { once: true });

    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            fecharConfirmacao();
            document.removeEventListener('keydown', escHandler);
        }
    });
}

/* ─── Utilitário: seleciona <option> pelo texto ─── */
function selecionarOpcaoTexto(select, texto) {
    if (!select || !texto) return;
    for (const option of select.options) {
        if (option.text.trim().toLowerCase() === texto.toLowerCase()) {
            option.selected = true;
            return;
        }
    }
    // Se não encontrar, deixa em branco
    select.value = '';
}

/* ════════════════════════════════════════════════════════════
   TOAST DE FEEDBACK
   ════════════════════════════════════════════════════════════ */
function mostrarToast(mensagem) {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = mensagem;
    toast.classList.add('toast-visivel');

    clearTimeout(mostrarToast._temporizador);
    mostrarToast._temporizador = setTimeout(() => {
        toast.classList.remove('toast-visivel');
    }, 2500);
}