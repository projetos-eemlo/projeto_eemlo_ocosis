/**
 * perfil.js
 * ──────────────────────────────────────────────────────────────
 * Comportamentos da tela "Visualizar Relatório" (perfil do aluno).
 * Escopo: SOMENTE esta tela — não interfere em pendentes.php
 * nem em nenhuma outra página do projeto.
 *
 * Funcionalidades:
 *   1. Imprimir o relatório completo (🖨️ Imprimir Todas)
 *   2. Imprimir uma única ocorrência (🖨️ na linha da tabela)
 *   3. Redirecionar para a edição (✏️ Editar) — a edição em si
 *      é feita na tela de Ocorrências Pendentes, fora do escopo
 *      desta tela.
 * ──────────────────────────────────────────────────────────────
 */

document.addEventListener('DOMContentLoaded', () => {
    initImprimirTudo();
    initImprimirOcorrencia();
    initEditarOcorrencia();
});


/* ════════════════════════════════════════════════════════════
   1. IMPRIMIR O RELATÓRIO COMPLETO
   Usa a impressão nativa do navegador. As regras @media print
   (no <style> do perfil.php) já escondem navbar/botões e deixam
   só o conteúdo do relatório.
   ════════════════════════════════════════════════════════════ */
function initImprimirTudo() {
    const botao = document.querySelector('.btn-imprimir-todas');
    if (!botao) return; // protege caso o botão não exista nesta página

    botao.addEventListener('click', () => {
        window.print();
    });
}


/* ════════════════════════════════════════════════════════════
   2. IMPRIMIR UMA ÚNICA OCORRÊNCIA
   Ao clicar no 🖨️ de uma linha, marcamos só aquela linha como
   "ativa para impressão" e adicionamos uma classe no <body>.
   O CSS (body.imprimir-uma-ocorrencia ...) faz o resto: esconde
   o card de resumo e as outras linhas da tabela, mostrando só
   a identificação do aluno + a ocorrência escolhida.
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

    // O evento "afterprint" dispara quando a caixa de impressão é
    // fechada (tanto se a pessoa imprimiu quanto se cancelou).
    // Aproveitamos para limpar as classes temporárias.
    window.addEventListener('afterprint', limparEstadoDeImpressao);
}

function limparEstadoDeImpressao() {
    document.body.classList.remove('imprimir-uma-ocorrencia');
    document.querySelectorAll('.linha-imprimir-ativa').forEach((linha) => {
        linha.classList.remove('linha-imprimir-ativa');
    });
}


/* ════════════════════════════════════════════════════════════
   3. EDITAR OCORRÊNCIA
   Esta tela é só de VISUALIZAÇÃO — a edição de fato acontece na
   tela "Ocorrências Pendentes". Por isso, em vez de duplicar o
   modal de edição aqui, avisamos a pessoa e a levamos pra lá.
   (Se no futuro vocês quiserem que pendentes.php abra direto na
   ocorrência certa, é só ler o parâmetro ?editar_id= que mandamos
   na URL.)
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
   TOAST DE FEEDBACK (reaproveitável)
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