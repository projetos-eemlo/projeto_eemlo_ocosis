/**
 * OCOSIS - Comportamentos da tela "Visualizar Relatório" (perfil do aluno).
 * Escopo: SOMENTE esta tela — não interfere em pendentes.php
 * nem em nenhuma outra página do projeto.
 *
 * Funcionalidades:
 * 1. Imprimir o relatório completo (🖨️ Imprimir Todas)
 * 2. Imprimir uma única ocorrência (🖨️ na linha da tabela)
 * 3. Redirecionar para a edição (✏️ Editar)
 */

document.addEventListener('DOMContentLoaded', () => {
    initImprimirTudo();
    initImprimirOcorrencia();
    initEditarOcorrencia();
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

    // O evento "afterprint" garante que o layout volte ao normal 
    // independente do usuário ter confirmado ou cancelado a impressão.
    window.addEventListener('afterprint', limparEstadoDeImpressao);
}

function limparEstadoDeImpressao() {
    document.body.classList.remove('imprimir-uma-ocorrencia');
    document.querySelectorAll('.linha-imprimir-ativa').forEach((linha) => {
        linha.classList.remove('linha-imprimir-ativa');
    });
}

/* ════════════════════════════════════════════════════════════
   3. EDITAR OCORRÊNCIA (REDIRECIONAMENTO)
   ════════════════════════════════════════════════════════════ */
function initEditarOcorrencia() {
    const botoesEditar = document.querySelectorAll('.btn-action-editar');
    if (!botoesEditar.length) return;

    botoesEditar.forEach((botao) => {
        botao.addEventListener('click', () => {
            const linha = botao.closest('tr');
            const occId = linha ? linha.dataset.id : null;

            mostrarToast('Redirecionando para Ocorrências Pendentes...');

            setTimeout(() => {
                window.location.href = occId
                    ? `pendentes.php?editar_id=${occId}`
                    : 'pendentes.php';
            }, 700);
        });
    });
}

/* ════════════════════════════════════════════════════════════
   TOAST DE FEEDBACK (防 Comportamento Duplicado)
   ════════════════════════════════════════════════════════════ */
function mostrarToast(mensagem) {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = mensaje;
    toast.classList.add('toast-visivel');

    // Limpa o delay anterior se o usuário clicar freneticamente no botão
    clearTimeout(mostrarToast._temporizador);
    mostrarToast._temporizador = setTimeout(() => {
        toast.classList.remove('toast-visivel');
    }, 2500);
}