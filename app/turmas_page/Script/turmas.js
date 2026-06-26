document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. BUSCAR ALUNOS NO BANCO AO CARREGAR A PÁGINA ---
    const tbody = document.getElementById('tabelaAlunosBody');
    const contador = document.getElementById('contadorAlunos');

    function carregarAlunos() {
        fetch('listar_todos_alunos.php')
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = ''; 
            
            if (data.sucesso && data.dados.length > 0) {
                data.dados.forEach(aluno => {
                    // Decide a cor da bolinha e a tag baseado nas ocorrências
                    let statusDot = aluno.total_ocorrencias > 0 ? "red" : "clear";
                    let badgeOcorrencia = aluno.total_ocorrencias > 0 ? 
                        `<span class="badge badge-orange">${aluno.total_ocorrencias} ocorrência(s)</span>` : 
                        `<span class="text-muted">—</span>`;

                    let turmaNome = aluno.desc_turma ? aluno.desc_turma : "Sem Turma";

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${aluno.num_simade}</td>
                        <td><span class="status-dot ${statusDot}"></span> ${aluno.nome_aluno}</td>
                        <td class="col-turma"><span class="badge badge-blue">${turmaNome}</span></td>
                        <td>${badgeOcorrencia}</td>
                        <td><button class="btn-acao btn-ver-perfil" data-nome="${aluno.nome_aluno}" data-simade="${aluno.num_simade}" data-turma="${turmaNome}" data-ocorrencias="${aluno.total_ocorrencias}">Ver Perfil</button></td>
                    `;
                    tbody.appendChild(tr);
                });
                
                contador.textContent = `${data.dados.length} alunos encontrados`;
                atribuirEventosPerfil(); // Ativa os botões "Ver Perfil"
            } else {
                tbody.innerHTML = '<tr><td colspan="5">Nenhum aluno encontrado no banco.</td></tr>';
                contador.textContent = "0 alunos encontrados";
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            tbody.innerHTML = '<tr><td colspan="5">Erro ao carregar dados do servidor.</td></tr>';
        });
    }

    // Carrega os alunos assim que a tela abre
    carregarAlunos();


    // --- 2. LÓGICA DO FILTRO DE TURMAS ---
    const filtroTurma = document.getElementById('filtroTurma');
    filtroTurma.addEventListener('change', () => {
        const turmaSelecionada = filtroTurma.options[filtroTurma.selectedIndex].text;
        const rows = tbody.getElementsByTagName('tr');
        let count = 0;

        for (let i = 0; i < rows.length; i++) {
            const rowTurmaText = rows[i].querySelector('.col-turma').textContent.trim();
            
            if (turmaSelecionada === "Todas as Turmas" || rowTurmaText === turmaSelecionada) {
                rows[i].style.display = "";
                count++;
            } else {
                rows[i].style.display = "none";
            }
        }
        contador.textContent = count === 1 ? "1 aluno encontrado" : `${count} alunos encontrados`;
    });


    // --- 3. NAVEGAÇÃO: TELA PRINCIPAL <-> PERFIL DO ALUNO ---
    const telaPesquisa = document.getElementById('telaPesquisa');
    const telaPerfil = document.getElementById('telaPerfil');
    const btnVoltar = document.getElementById('btnVoltar');

    function atribuirEventosPerfil() {
        const botoesPerfil = document.querySelectorAll('.btn-ver-perfil');
        
        botoesPerfil.forEach(btn => {
            btn.addEventListener('click', (e) => {
                // Pega os dados do aluno clicado através dos atributos data-*
                const nome = e.target.getAttribute('data-nome');
                const simade = e.target.getAttribute('data-simade');
                const turma = e.target.getAttribute('data-turma');
                const ocorrencias = e.target.getAttribute('data-ocorrencias');

                // Preenche a tela de perfil
                document.getElementById('nomeAlunoPerfil').textContent = nome;
                document.getElementById('simadePerfil').textContent = simade;
                document.getElementById('turmaPerfil').textContent = turma;
                document.getElementById('totalOcorrenciasPerfil').textContent = ocorrencias;

                // Esconde a tabela e mostra o perfil
                telaPesquisa.style.display = 'none';
                telaPerfil.style.display = 'block';
            });
        });
    }

    btnVoltar.addEventListener('click', () => {
        telaPerfil.style.display = 'none';
        telaPesquisa.style.display = 'block';
    });


    // --- 4. MODAL 1: CADASTRAR TURMA ---
    const modalTurma = document.getElementById("modalTurma");
    const btnNovaTurma = document.getElementById("btnNovaTurma");
    const fecharModalTurma = modalTurma.querySelector(".fechar_modal");
    
    btnNovaTurma.addEventListener('click', () => modalTurma.style.display = "block");
    fecharModalTurma.addEventListener('click', () => modalTurma.style.display = "none");


    // --- 5. MODAL 2: EDITAR OCORRÊNCIA ---
    const modalEditar = document.getElementById("modalEditar");
    const btnFecharEditar = modalEditar.querySelector(".close_editar");
    const btnCancelarEditar = modalEditar.querySelector(".close_editar_btn");

    // Usa delegação de eventos, pois os botões "Editar" podem ser carregados dinamicamente
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btnAbrirModalEditar')) {
            modalEditar.style.display = "block";
        }
    });

    btnFecharEditar.addEventListener('click', () => modalEditar.style.display = "none");
    btnCancelarEditar.addEventListener('click', () => modalEditar.style.display = "none");


    // --- 6. FECHAR MODAIS CLICANDO FORA ---
    window.addEventListener('click', (e) => {
        if (e.target === modalTurma) modalTurma.style.display = "none";
        if (e.target === modalEditar) modalEditar.style.display = "none";
    });

});