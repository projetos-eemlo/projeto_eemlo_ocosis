cument.addEventListener('DOMContentLoaded', () => {
    injetarEstilosDinamicos();
    initFiltroTurma();
    initBuscaRapida();
    initOrdenacaoTabela();
    initNavegacaoLinha();
});
 
 
/* ════════════════════════════════════════════════════════════
   0. ESTILOS INJETADOS DINAMICAMENTE
   (overlay de carregamento + setas de ordenação)
   ════════════════════════════════════════════════════════════ */
function injetarEstilosDinamicos() {
    if (document.getElementById('estilos-js-dinamicos')) return;
 
    const style = document.createElement('style');
    style.id = 'estilos-js-dinamicos';
    style.textContent = `
        #overlay-carregando {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.65);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            z-index: 1000;
            font-size: 0.95rem;
            font-weight: 600;
            color: #1a56db;
        }
 
        #overlay-carregando .spinner {
            width: 18px;
            height: 18px;
            border: 3px solid rgba(26, 86, 219, 0.25);
            border-top-color: #1a56db;
            border-radius: 50%;
            animation: girar 0.7s linear infinite;
        }
 
        @keyframes girar {
            to { transform: rotate(360deg); }
        }
 
        .th-ordenavel {
            cursor: pointer;
            user-select: none;
        }
 
        .th-ordenavel::after {
            content: '';
            display: inline-block;
            width: 0.7em;
            margin-left: 4px;
            opacity: 0.35;
        }
 
        .th-ordenavel[data-direcao="asc"]::after  { content: '▲'; opacity: 1; }
        .th-ordenavel[data-direcao="desc"]::after { content: '▼'; opacity: 1; }
 
        .students-table tbody tr[tabindex]:focus-visible {
            outline: 2px solid #1a56db;
            outline-offset: -2px;
        }
    `;
    document.head.appendChild(style);
}
function initFiltroTurma() {
    const select = document.getElementById('turma-select');
    const form = document.getElementById('filter-form');
 
    if (!select || !form) return; // evita erro se a página mudar
 
    select.addEventListener('change', () => {
        mostrarCarregando('Atualizando turma...');
        form.submit();
    });
}
 
function mostrarCarregando(mensagem) {
    // Evita criar overlays duplicados se o usuário for rápido
    if (document.getElementById('overlay-carregando')) return;
 
    const select = document.getElementById('turma-select');
    if (select) select.disabled = true;
 
    const overlay = document.createElement('div');
    overlay.id = 'overlay-carregando';
 
    const spinner = document.createElement('span');
    spinner.className = 'spinner';
 
    const texto = document.createElement('span');
    texto.textContent = mensagem;
 
    overlay.append(spinner, texto);
    document.body.appendChild(overlay);
}
 
 
/* ════════════════════════════════════════════════════════════
   2. BUSCA RÁPIDA (lado cliente)
   Filtra as linhas já carregadas na tabela, por nome ou SIMADE,
   sem precisar recarregar a página. Usa "debounce" para não
   filtrar a cada tecla, e ignora acentos para facilitar a busca.
   ════════════════════════════════════════════════════════════ */
function initBuscaRapida() {
    const input = document.getElementById('busca-aluno');
    const tbody = document.getElementById('tabela-alunos-corpo');
 
    if (!input || !tbody) return;
 
    const aplicarFiltro = debounce(() => {
        const termo = normalizarTexto(input.value);
        const linhas = tbody.querySelectorAll('tr:not(.busca-vazia):not(.empty-row)');
        let visiveis = 0;
 
        linhas.forEach((linha) => {
            const nome = normalizarTexto(
                linha.querySelector('.student-name')?.textContent || ''
            );
            const simade = normalizarTexto(
                linha.querySelector('.td-simade')?.textContent || ''
            );
 
            const corresponde = termo === '' || nome.includes(termo) || simade.includes(termo);
            linha.style.display = corresponde ? '' : 'none';
            if (corresponde) visiveis++;
        });
 
        atualizarMensagemBuscaVazia(tbody, visiveis, termo);
    }, 250);
 
    input.addEventListener('input', aplicarFiltro);
 
    input.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            input.value = '';
            aplicarFiltro();
        }
    });
}
 
function atualizarMensagemBuscaVazia(tbody, visiveis, termo) {
    let linhaVazia = tbody.querySelector('.busca-vazia');
 
    if (visiveis === 0 && termo !== '') {
        if (!linhaVazia) {
            linhaVazia = document.createElement('tr');
            linhaVazia.className = 'empty-row busca-vazia';
            linhaVazia.innerHTML = `
                <td colspan="5">
                    <span class="empty-icon">🔍</span>
                    Nenhum aluno corresponde à busca.
                </td>`;
            tbody.appendChild(linhaVazia);
        }
        linhaVazia.style.display = '';
    } else if (linhaVazia) {
        linhaVazia.style.display = 'none';
    }
}
 
// Remove acentos e caixa para comparar texto de forma mais tolerante
function normalizarTexto(texto) {
    return texto
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase();
}
 
// Evita executar a função a cada tecla digitada; espera o usuário parar
function debounce(funcao, atraso) {
    let temporizador;
    return (...args) => {
        clearTimeout(temporizador);
        temporizador = setTimeout(() => funcao(...args), atraso);
    };
}
 
 
/* ════════════════════════════════════════════════════════════
   3. ORDENAÇÃO DA TABELA
   Clicar (ou apertar Enter/Espaço, para acessibilidade) em um
   cabeçalho com [data-sort] reordena as linhas já renderizadas.
   ════════════════════════════════════════════════════════════ */
function initOrdenacaoTabela() {
    const cabecalhos = document.querySelectorAll('#tabela-alunos th[data-sort]');
    const tbody = document.getElementById('tabela-alunos-corpo');
 
    if (!cabecalhos.length || !tbody) return;
 
    cabecalhos.forEach((th) => {
        th.classList.add('th-ordenavel');
        th.setAttribute('role', 'button');
        th.setAttribute('tabindex', '0');
 
        th.addEventListener('click', () => ordenarPor(th, tbody));
        th.addEventListener('keydown', (evento) => {
            if (evento.key === 'Enter' || evento.key === ' ') {
                evento.preventDefault();
                ordenarPor(th, tbody);
            }
        });
    });
}
 
function ordenarPor(thClicado, tbody) {
    const campo = thClicado.dataset.sort;
    const novaDirecao = thClicado.dataset.direcao === 'asc' ? 'desc' : 'asc';
 
    // Limpa o indicador visual (▲/▼) das outras colunas
    thClicado.parentElement
        .querySelectorAll('th[data-sort]')
        .forEach((th) => delete th.dataset.direcao);
 
    thClicado.dataset.direcao = novaDirecao;
 
    const linhas = Array.from(
        tbody.querySelectorAll('tr:not(.busca-vazia):not(.empty-row)')
    );
 
    linhas.sort((linhaA, linhaB) => {
        const valorA = valorParaOrdenacao(linhaA, campo);
        const valorB = valorParaOrdenacao(linhaB, campo);
 
        if (typeof valorA === 'number' && typeof valorB === 'number') {
            return novaDirecao === 'asc' ? valorA - valorB : valorB - valorA;
        }
 
        return novaDirecao === 'asc'
            ? String(valorA).localeCompare(String(valorB), 'pt-BR')
            : String(valorB).localeCompare(String(valorA), 'pt-BR');
    });
 
    // Reinsere as linhas na nova ordem (move, não duplica)
    linhas.forEach((linha) => tbody.appendChild(linha));
}
 
function valorParaOrdenacao(linha, campo) {
    switch (campo) {
        case 'simade':
            return linha.querySelector('.td-simade')?.textContent.trim() || '';
        case 'nome':
            return linha.querySelector('.student-name')?.textContent.trim() || '';
        case 'turma':
            return linha.querySelector('.turma-badge')?.textContent.trim() || '';
        case 'ocorrencias':
            return Number(linha.dataset.ocorrencias || 0);
        default:
            return '';
    }
}
 
 
/* ════════════════════════════════════════════════════════════
   4. NAVEGAÇÃO PELA LINHA
   Clicar em qualquer parte da linha (exceto no próprio botão)
   leva ao perfil do aluno. Funciona também via teclado (Tab + Enter).
   ════════════════════════════════════════════════════════════ */
function initNavegacaoLinha() {
    const tbody = document.getElementById('tabela-alunos-corpo');
    if (!tbody) return;
 
    // Torna cada linha focável e clicável
    tbody.querySelectorAll('tr:not(.empty-row)').forEach((linha) => {
        linha.setAttribute('tabindex', '0');
        linha.style.cursor = 'pointer';
    });
 
    tbody.addEventListener('click', (evento) => {
        if (evento.target.closest('.btn-perfil')) return; // o link já navega por si só
        navegarParaPerfil(evento.target.closest('tr'));
    });
 
    tbody.addEventListener('keydown', (evento) => {
        if (evento.key !== 'Enter') return;
        navegarParaPerfil(evento.target.closest('tr'));
    });
}
 
function navegarParaPerfil(linha) {
    if (!linha || linha.classList.contains('empty-row')) return;
 
    const link = linha.querySelector('.btn-perfil');
    if (link) window.location.href = link.href;
}

