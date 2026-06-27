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
    // --- 1.5 BUSCAR TURMAS PARA O FILTRO ---
    function carregarTurmasParaFiltro() {
        fetch('listar_turmas.php')
        .then(response => response.json())
        .then(data => {
            const filtroTurma = document.getElementById('filtroTurma');
            
            // Mantém apenas a primeira opção (Todas as Turmas)
            filtroTurma.innerHTML = '<option value="todas">Todas as Turmas</option>';
            
            if (data.sucesso && data.dados.length > 0) {
                data.dados.forEach(turma => {
                    const option = document.createElement('option');
                    // Usamos a descrição tanto pro valor quanto pro texto, 
                    // para a lógica do seu filtro continuar funcionando certinho!
                    option.value = turma.desc_turma; 
                    option.textContent = turma.desc_turma;
                    filtroTurma.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Erro ao carregar lista de turmas:', error));
    }
    // Carrega os alunos assim que a tela abre
    carregarAlunos();
    carregarTurmasParaFiltro();
    

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


    // --- 3. NAVEGAÇÃO E DADOS REAIS DO PERFIL DO ALUNO ---
    const telaPesquisa = document.getElementById('telaPesquisa');
    const telaPerfil = document.getElementById('telaPerfil');
    const btnVoltar = document.getElementById('btnVoltar');
    const tbodyHistorico = document.getElementById('tabelaHistoricoOcorrencias');

    function atribuirEventosPerfil() {
        const botoesPerfil = document.querySelectorAll('.btn-ver-perfil');
        
        botoesPerfil.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const simade = e.target.getAttribute('data-simade');
                
                // Mostrar "Carregando" na tabela de histórico
                tbodyHistorico.innerHTML = '<tr><td colspan="6" style="text-align: center;">Carregando dados do banco...</td></tr>';
                
                // Esconde a tabela e mostra o perfil
                telaPesquisa.style.display = 'none';
                telaPerfil.style.display = 'block';

                // Busca os dados REAIS no banco de dados
                fetch(`buscar_perfil_aluno.php?simade=${simade}`)
                .then(response => response.json())
                .then(data => {
                    if(data.sucesso) {
                        const aluno = data.aluno;
                        const ocorrencias = data.ocorrencias;

                        // 1. Preenche os dados do aluno nos cards
                        document.getElementById('nomeAlunoPerfil').textContent = aluno.nome_aluno;
                        document.getElementById('simadePerfil').textContent = aluno.num_simade;
                        document.getElementById('nascPerfil').textContent = aluno.dt_nascimento || '--/--/----';
                        document.getElementById('turmaPerfil').textContent = aluno.desc_turma || 'Sem Turma';
                        
                        document.getElementById('totalOcorrenciasPerfil').textContent = ocorrencias.length;
                        document.getElementById('pendentesPerfil').textContent = ocorrencias.length; // Simulado
                        
                        // 2. Preenche a tabela de Histórico
                        tbodyHistorico.innerHTML = '';
                        
                        if(ocorrencias.length > 0) {
                            // Pega a primeira ocorrência como 'mais reincidente' só como exemplo visual
                            document.getElementById('reincidentePerfil').textContent = ocorrencias[0].tipo_infracao;

                            ocorrencias.forEach(oc => {
                                const tr = document.createElement('tr');
                                const materiaProf = (oc.desc_disciplina && oc.nome_funcionario) 
                                    ? `${oc.desc_disciplina} / ${oc.nome_funcionario}` 
                                    : 'Não informado';
                                
                                tr.innerHTML = `
                                    <td>${oc.data_formatada}</td>
                                    <td>${oc.horario || '--:--'}</td>
                                    <td>${materiaProf}</td>
                                    <td>
                                        <span class="text-blue font-bold">Registro</span><br>
                                        <span class="text-small">${oc.tipo_infracao || 'Sem descrição específica'}</span>
                                    </td>
                                    <td>
                                        <span class="status-dot red"></span> Pendente<br>
                                        <span class="text-small text-orange">Notif. responsável</span>
                                    </td>
                                    <td class="action-buttons">
                                        <!-- Ao clicar em editar, passa os dados pro modal -->
                                        <button class="btn-edit btnAbrirModalEditar" 
                                            data-nome="${aluno.nome_aluno}" 
                                            data-data="${oc.data_formatada}"
                                            data-turma="${aluno.desc_turma}">Editar</button>
                                        <button class="btn-icon">🖨️</button>
                                    </td>
                                `;
                                tbodyHistorico.appendChild(tr);
                            });
                        } else {
                            document.getElementById('reincidentePerfil').textContent = '-';
                            tbodyHistorico.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #64748b;">Nenhuma ocorrência registrada para este aluno.</td></tr>';
                        }
                    } else {
                        alert("Erro ao buscar dados: " + data.mensagem);
                        btnVoltar.click();
                    }
                })
                .catch(error => {
                    console.error("Erro no fetch:", error);
                    tbodyHistorico.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Erro de conexão com o banco de dados.</td></tr>';
                });
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