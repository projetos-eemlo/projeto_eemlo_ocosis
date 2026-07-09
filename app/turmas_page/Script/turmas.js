document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. BUSCAR ALUNOS NO BANCO AO CARREGAR A PÁGINA ---
    const tbody = document.getElementById('tabelaAlunosBody');
    const contador = document.getElementById('contadorAlunos');

    function carregarAlunos() {
        fetch('php/listar_todos_alunos.php')
        .then(response => response.json())
        .then(data => {
            if(!tbody) return; // Evita erro se a tabela não existir
            tbody.innerHTML = ''; 
            
            if (data.sucesso && data.dados.length > 0) {
                data.dados.forEach(aluno => {
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
                
                if(contador) contador.textContent = `${data.dados.length} alunos encontrados`;
                atribuirEventosPerfil(); // Ativa os botões "Ver Perfil"
            } else {
                tbody.innerHTML = '<tr><td colspan="5">Nenhum aluno encontrado no banco.</td></tr>';
                if(contador) contador.textContent = "0 alunos encontrados";
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            if(tbody) tbody.innerHTML = '<tr><td colspan="5">Erro ao carregar dados do servidor.</td></tr>';
        });
    }

    // --- 1.5 BUSCAR TURMAS PARA O FILTRO ---
    const filtroTurma = document.getElementById('filtroTurma');
    
    function carregarTurmasParaFiltro() {
        fetch('php/listar_turmas.php')
        .then(response => response.json())
        .then(data => {
            if(!filtroTurma) return;
            
            filtroTurma.innerHTML = '<option value="todas">Todas as Turmas</option>';
            
            if (data.sucesso && data.dados.length > 0) {
                data.dados.forEach(turma => {
                    const option = document.createElement('option');
                    // Aqui usamos o ID para o value, para buscar no banco corretamente depois
                    option.value = turma.id_turma; 
                    option.textContent = turma.desc_turma;
                    filtroTurma.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Erro ao carregar lista de turmas:', error));
    }

    // --- NOVO: FAZER O FILTRO FUNCIONAR AO MUDAR A OPÇÃO ---
    if (filtroTurma) {
        filtroTurma.addEventListener('change', (e) => {
            if (e.target.value === 'todas') {
                carregarAlunos(); // Volta a mostrar todos
            } else {
                carregarAlunosPorTurma(e.target.value); // Busca pela turma específica
            }
        });
    }

    // Carrega os alunos assim que a tela abre
    carregarAlunos();
    carregarTurmasParaFiltro();
    

    // --- 2. CARREGAR ALUNOS DA TURMA SELECIONADA ---
    // CORREÇÃO: Mudei o nome de 'carregarAlunos' para 'carregarAlunosPorTurma'
    function carregarAlunosPorTurma(idTurma) {
        if (!idTurma) {
            // CORREÇÃO: Mudei 'tabelaAlunosCorpo' para 'tbody'
            if(tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Selecione uma turma para ver os alunos.</td></tr>';
            return;
        }

        fetch(`php/buscar_alunos.php?id_turma=${idTurma}`)
            .then(response => {
                if (!response.ok) throw new Error('Erro na resposta do servidor');
                return response.json();
            })
            .then(data => {
                if(!tbody) return;
                tbody.innerHTML = ""; // Limpa a tabela antes de preencher
                
                // Trata caso a resposta venha encapsulada em 'data.dados'
                const alunos = data.dados ? data.dados : data; 

                if (!alunos || alunos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Nenhum aluno cadastrado nesta turma.</td></tr>';
                    if(contador) contador.textContent = "0 alunos encontrados";
                    return;
                }

                alunos.forEach(aluno => {
                    const row = document.createElement("tr");
                    // CORREÇÃO: Arrumei as colunas para baterem com as 5 colunas da sua tabela original
                    row.innerHTML = `
                        <td>${aluno.num_simade}</td>
                        <td><span class="status-dot clear"></span> ${aluno.nome_aluno}</td>
                        <td class="col-turma"><span class="badge badge-blue">Filtrado</span></td>
                        <td><span class="text-muted">—</span></td>
                        <td><button class="btn-acao btn-ver-perfil" data-nome="${aluno.nome_aluno}" data-simade="${aluno.num_simade}">Ver Perfil</button></td>
                    `;
                    tbody.appendChild(row);
                });

                if(contador) contador.textContent = `${alunos.length} alunos encontrados`;
                
                // CORREÇÃO: Mudei 'configurarBotoesPerfil' para 'atribuirEventosPerfil'
                atribuirEventosPerfil(); 
            })
            .catch(error => {
                console.error("Erro ao carregar alunos:", error);
                if(tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Erro ao carregar os alunos.</td></tr>';
            });
    }


    // --- 3. NAVEGAÇÃO E DADOS REAIS DO PERFIL DO ALUNO ---
    const telaPesquisa = document.getElementById('telaPesquisa');
    const telaPerfil = document.getElementById('telaPerfil');
    const btnVoltar = document.getElementById('btnVoltar');
    const tbodyHistorico = document.getElementById('tabelaHistoricoOcorrencias');

    function atribuirEventosPerfil() {
    const botoesPerfil = document.querySelectorAll('.btn-ver-perfil');
    
    botoesPerfil.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Pega o SIMADE do aluno que foi clicado
            const simade = e.target.getAttribute('data-simade');
            
            if (simade) {
                // REDIRECIONA para a nova página passando o SIMADE na URL
                // Ajuste o caminho '../perfil_aluno.php' para onde o arquivo do seu colega realmente estiver
                window.location.href = `../visualizar_relatorio/perfil.php?simade=${simade}`;
            } else {
                alert("Erro: SIMADE do aluno não encontrado.");
            }
        });
    });
    }

    if(btnVoltar) {
        btnVoltar.addEventListener('click', () => {
            if(telaPerfil) telaPerfil.style.display = 'none';
            if(telaPesquisa) telaPesquisa.style.display = 'block';
        });
    }

   // --- 4. MODAL 1: CADASTRAR TURMA ---
    // Usando optional chaining (?.) para não quebrar a página se o modal não existir
    const modalTurma = document.getElementById("modalTurma");
    const btnNovaTurma = document.getElementById("btnNovaTurma");
    const fecharModalTurma = modalTurma?.querySelector(".fechar_modal");
    
    if (btnNovaTurma) btnNovaTurma.addEventListener('click', () => modalTurma.style.display = "block");
    if (fecharModalTurma) fecharModalTurma.addEventListener('click', () => modalTurma.style.display = "none");

    const formCadastroTurma = document.getElementById("formCadastroTurma");
    let enviandoFormulario = false;

    if (formCadastroTurma) {
        formCadastroTurma.addEventListener('submit', (e) => {
            e.preventDefault(); 

            const descTurmaValue = document.getElementById("descTurma").value.trim();
            const anoLetivoValue = document.getElementById("anoLetivo").value.trim();
            const semestreLetivoValue = document.getElementById("semestreLetivo").value;
            const turnoValue = document.getElementById("turno").value;

            if (!descTurmaValue) {
                alert("Campo [Nome / Descrição] Preenchido incorretamente.");
                return; 
            }
            if (!anoLetivoValue || anoLetivoValue < 2024) {
                alert("Campo [Ano Letivo] Preenchido incorretamente.");
                return;
            }

            if (enviandoFormulario) return;
            enviandoFormulario = true;

            const btnSalvar = formCadastroTurma.querySelector("button[type='submit']");
            if (btnSalvar) {
                btnSalvar.disabled = true;
                btnSalvar.textContent = "Salvando...";
            }

            fetch('php/cadastrar_turma.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    descTurma: descTurmaValue,
                    anoLetivo: anoLetivoValue,
                    semestreLetivo: semestreLetivoValue,
                    turno: turnoValue
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    alert(data.mensagem); 
                    modalTurma.style.display = "none"; 
                    formCadastroTurma.reset(); 
                    if (typeof carregarTurmasParaFiltro === "function") {
                        carregarTurmasParaFiltro();
                    }
                } else {
                    alert("Atenção: " + data.mensagem); 
                }
            })
            .catch(error => {
                console.error('Erro de conexão:', error);
                alert('Erro ao tentar comunicar com o servidor.');
            })
            .finally(() => {
                enviandoFormulario = false;
                if (btnSalvar) {
                    btnSalvar.disabled = false;
                    btnSalvar.textContent = "Salvar Turma";
                }
            });
        });
    }

    // --- 5. MODAL 2: EDITAR OCORRÊNCIA ---
    const modalEditar = document.getElementById("modalEditar");
    const btnFecharEditar = modalEditar?.querySelector(".close_editar");
    const btnCancelarEditar = modalEditar?.querySelector(".close_editar_btn");

    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btnAbrirModalEditar') && modalEditar) {
            modalEditar.style.display = "block";
        }
    });

    if (btnFecharEditar) btnFecharEditar.addEventListener('click', () => modalEditar.style.display = "none");
    if (btnCancelarEditar) btnCancelarEditar.addEventListener('click', () => modalEditar.style.display = "none");

    // --- 6. FECHAR MODAIS CLICANDO FORA ---
    window.addEventListener('click', (e) => {
        if (modalTurma && e.target === modalTurma) modalTurma.style.display = "none";
        if (modalEditar && e.target === modalEditar) modalEditar.style.display = "none";
    });
    
});