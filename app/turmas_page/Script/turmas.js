document.addEventListener('DOMContentLoaded', () => {
    
    const tbody = document.getElementById('tabelaAlunosBody');
    const contador = document.getElementById('contadorAlunos');

    // --- 1. BUSCAR ALUNOS NO BANCO AO CARREGAR A PÁGINA ---
    function carregarAlunos() {
        fetch('php/listar_todos_alunos.php')
        .then(response => response.json())
        .then(data => {
            if(!tbody) return; 
            tbody.innerHTML = ''; 
            
            if (data.sucesso && data.dados.length > 0) {
                data.dados.forEach(aluno => {
                    let statusDot = aluno.total_ocorrencias > 0 ? "red" : "clear";
                    let badgeOcorrencia = aluno.total_ocorrencias > 0 ? 
                        `<span class="badge badge-orange">${aluno.total_ocorrencias} ocorrência(s)</span>` : 
                        `<span class="text-muted">—</span>`;

                    let turmaNome = aluno.desc_turma ? aluno.desc_turma : "Sem Turma";

                    const tr = document.createElement('tr');
                    
                    // O PULO DO GATO: Usamos a tag <a> igual no pendentes.php, 
                    // passando o aluno.id_aluno direto no href!
                    tr.innerHTML = `
                        <td>${aluno.num_simade}</td>
                        <td><span class="status-dot ${statusDot}"></span> ${aluno.nome_aluno}</td>
                        <td class="col-turma"><span class="badge badge-blue">${turmaNome}</span></td>
                        <td>${badgeOcorrencia}</td>
                        <td>
                            <a href="../visualizar_relatorio/perfil.php?id=${aluno.id_aluno}" class="btn-acao btn-ver-perfil" style="text-decoration: none; display: inline-block; text-align: center;">Ver Perfil</a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                
                if(contador) contador.textContent = `${data.dados.length} alunos encontrados`;
            } else {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Nenhum aluno encontrado no banco.</td></tr>';
                if(contador) contador.textContent = "0 alunos encontrados";
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            if(tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Erro ao carregar dados do servidor.</td></tr>';
        });
    }

    // --- 2. BUSCAR TURMAS PARA O FILTRO ---
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
                    option.value = turma.id_turma; 
                    option.textContent = turma.desc_turma;
                    filtroTurma.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Erro ao carregar lista de turmas:', error));
    }

    // Ação do Filtro
    if (filtroTurma) {
        filtroTurma.addEventListener('change', (e) => {
            if (e.target.value === 'todas') {
                carregarAlunos(); 
            } else {
                carregarAlunosPorTurma(e.target.value); 
            }
        });
    }

    // --- 3. CARREGAR ALUNOS DA TURMA SELECIONADA ---
    function carregarAlunosPorTurma(idTurma) {
        if (!idTurma) {
            if(tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Selecione uma turma para ver os alunos.</td></tr>';
            return;
        }

        fetch(`php/buscar_alunos.php?id_turma=${idTurma}`)
            .then(response => response.json())
            .then(data => {
                if(!tbody) return;
                tbody.innerHTML = ""; 
                
                const alunos = data.dados ? data.dados : data; 

                if (!alunos || alunos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Nenhum aluno cadastrado nesta turma.</td></tr>';
                    if(contador) contador.textContent = "0 alunos encontrados";
                    return;
                }

                alunos.forEach(aluno => {
                    const row = document.createElement("tr");
                    
                    // AQUI TAMBÉM: Tag <a> injetando o ID direto na URL
                    row.innerHTML = `
                        <td>${aluno.num_simade}</td>
                        <td><span class="status-dot clear"></span> ${aluno.nome_aluno}</td>
                        <td class="col-turma"><span class="badge badge-blue">Filtrado</span></td>
                        <td><span class="text-muted">—</span></td>
                        <td>
                            <a href="../visualizar_relatorio/perfil.php?id=${aluno.id_aluno}" class="btn-acao btn-ver-perfil" style="text-decoration: none; display: inline-block; text-align: center;">Ver Perfil</a>
                        </td>
                    `;
                    tbody.appendChild(row);
                });

                if(contador) contador.textContent = `${alunos.length} alunos encontrados`;
            })
            .catch(error => {
                console.error("Erro ao carregar alunos:", error);
                if(tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Erro ao carregar os alunos.</td></tr>';
            });
    }

    // Inicializa a tela
    carregarAlunos();
    carregarTurmasParaFiltro();


    // ====================================================================
    // A PARTIR DAQUI SÃO SÓ OS MODAIS QUE VOCÊ JÁ TINHA (INTACTOS)
    // ====================================================================

    // --- 4. MODAL CADASTRAR TURMA ---
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

            if (!descTurmaValue || !anoLetivoValue || anoLetivoValue < 2024) {
                alert("Campos preenchidos incorretamente.");
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
                    carregarTurmasParaFiltro();
                } else {
                    alert("Atenção: " + data.mensagem); 
                }
            })
            .catch(error => console.error('Erro de conexão:', error))
            .finally(() => {
                enviandoFormulario = false;
                if (btnSalvar) {
                    btnSalvar.disabled = false;
                    btnSalvar.textContent = "Salvar Turma";
                }
            });
        });
    }

    // --- 5. FECHAR MODAIS CLICANDO FORA ---
    window.addEventListener('click', (e) => {
        if (modalTurma && e.target === modalTurma) modalTurma.style.display = "none";
    });
});w