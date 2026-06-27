document.addEventListener('DOMContentLoaded', function() {
    
    const btnEditar = document.getElementById('btn-editar');
    const btnUpload = document.getElementById('btn-upload');
    const btnCadastrar = document.getElementById('btn-cadastrar');
    const btnExcluir = document.getElementById('btn-excluir');
    const btnTrocarTurma = document.getElementById('btn-trocar-turma'); // Novo Botão
    
    const inputPesquisa = document.getElementById('input-pesquisa'); 
    const containerAlunos = document.getElementById('container-alunos');
    const checkboxTodos = document.getElementById('aluno-todos');
    let checkboxesAlunos = document.querySelectorAll('.check-aluno'); 
    
    const selectTurmas = document.getElementById('turmas');
    const modalPeriodo = document.getElementById('modal-periodo');
    const containerOpcoesPeriodo = document.getElementById('opcoes-periodo');
    const fecharModalBtn = document.getElementById('fechar-modal');
    
    const infoTurmaBadge = document.getElementById('turma-selecionada-info');

    // Elementos do Modal de Transferência
    const modalTransferenciaFiltros = document.getElementById('modal-transferencia-filtros');
    const modalTransferenciaConfirmacao = document.getElementById('modal-transferencia-confirmacao');
    
    const filtroAno = document.getElementById('filtro-ano');
    const filtroSemestre = document.getElementById('filtro-semestre');
    const filtroTrimestre = document.getElementById('filtro-trimestre');
    const filtroTurmaDestino = document.getElementById('filtro-turma-destino');
    
    const btnCancelarFiltros = document.getElementById('btn-cancelar-filtros');
    const btnAvancarTransferencia = document.getElementById('btn-avancar-transferencia');
    const btnCancelarTransferencia = document.getElementById('btn-cancelar-transferencia');
    const btnConfirmarTransferencia = document.getElementById('btn-confirmar-transferencia');
    
    const listaAlunosTransferencia = document.getElementById('lista-alunos-transferencia');
    const textoConfirmacaoTurma = document.getElementById('texto-confirmacao-turma');

    let todasTurmasDoBanco = [];
    let idTurmaDefinitiva = null; 
    let turmaDefinitivaObj = null; 
    let isModoCSV = false;
    let debounceTimeout; 

    // ==========================================
    // LÓGICA DE ESTADO DOS BOTÕES
    // ==========================================
    function atualizarEstadoBotoes() {
        const algumAlunoMarcado = document.querySelectorAll('.check-aluno:checked').length > 0;
        const turmaConfirmada = idTurmaDefinitiva !== null;

        if (btnCadastrar) {
            btnCadastrar.disabled = !(algumAlunoMarcado && turmaConfirmada);
        }

        // Excluir e Trocar de Turma só funcionam se tiver aluno marcado E NÃO for do CSV
        if (btnExcluir) btnExcluir.disabled = isModoCSV ? true : !algumAlunoMarcado;
        if (btnTrocarTurma) btnTrocarTurma.disabled = isModoCSV ? true : !algumAlunoMarcado;
    }

    containerAlunos.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('check-aluno')) {
            atualizarEstadoBotoes();
        }
    });

    // --- FUNÇÕES DA DIV VISUAL E DA TURMA ---
    function atualizarTextoBadge() {
        if (!turmaDefinitivaObj) return;

        const sem = turmaDefinitivaObj.semestre_letivo ? `${turmaDefinitivaObj.semestre_letivo}ºSem` : '-';
        const tri = turmaDefinitivaObj.trimestre_letivo ? `${turmaDefinitivaObj.trimestre_letivo}ºTri` : '-';
        
        const infoBase = `${turmaDefinitivaObj.desc_turma} \\ ${turmaDefinitivaObj.ano_letivo} \\ ${sem} \\ ${tri}`;
        
        if (isModoCSV) {
            infoTurmaBadge.textContent = `Cadastrar para: ${infoBase}`;
        } else {
            infoTurmaBadge.textContent = infoBase;
        }

        infoTurmaBadge.classList.add('visivel');
        atualizarEstadoBotoes();
    }

    function limparEstadoTurma() {
        idTurmaDefinitiva = null;
        turmaDefinitivaObj = null;
        infoTurmaBadge.textContent = '';
        infoTurmaBadge.classList.remove('visivel');
        atualizarEstadoBotoes();
    }

    function resetarTurmaDropdown() {
        selectTurmas.value = "";
        limparEstadoTurma();
    }

    // --- CARREGAR E AGRUPAR TURMAS GERAIS ---
    async function carregarTurmas() {
        const formData = new FormData();
        formData.append('acao', 'listar_turmas');

        try {
            const response = await fetch('Backend.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.sucesso) {
                todasTurmasDoBanco = result.dados;
                const nomesUnicos = [...new Set(todasTurmasDoBanco.map(t => t.desc_turma))];
                
                selectTurmas.innerHTML = '<option value="">Selecione uma Turma</option>';
                nomesUnicos.forEach(nome => {
                    const option = document.createElement('option');
                    option.value = nome; 
                    option.textContent = nome;
                    selectTurmas.appendChild(option);
                });
            } else {
                exibirMensagem('❌ Erro ao carregar turmas: ' + result.erro);
            }
        } catch (error) {
            console.error(error);
        }
    }
    carregarTurmas();

    selectTurmas.addEventListener('change', function(e) {
        const nomeSelecionado = e.target.value;
        limparEstadoTurma(); 

        if (!nomeSelecionado) return;

        const versoesBrutas = todasTurmasDoBanco.filter(t => t.desc_turma === nomeSelecionado);
        const versoes = [];
        const chavesVistas = new Set();

        versoesBrutas.forEach(v => {
            const chave = `${v.ano_letivo}-${v.semestre_letivo}-${v.trimestre_letivo}-${v.turno}`;
            if (!chavesVistas.has(chave)) {
                chavesVistas.add(chave); 
                versoes.push(v); 
            }
        });

        if (versoes.length > 1) {
            containerOpcoesPeriodo.innerHTML = ''; 
            versoes.forEach(v => {
                const btn = document.createElement('button');
                btn.className = 'btn-periodo';
                const semestreTexto = v.semestre_letivo ? `${v.semestre_letivo}º Semestre` : 'Anual';
                btn.textContent = `${v.ano_letivo} / ${semestreTexto} (${v.turno})`;
                
                btn.addEventListener('click', () => {
                    idTurmaDefinitiva = v.id_turma;
                    turmaDefinitivaObj = v; 
                    modalPeriodo.classList.add('hidden'); 
                    atualizarTextoBadge(); 
                    exibirMensagem(`✅ Turma confirmada.`);
                    if (!isModoCSV) buscarAlunosDaTurma(idTurmaDefinitiva);
                });
                containerOpcoesPeriodo.appendChild(btn);
            });
            modalPeriodo.classList.remove('hidden');

        } else if (versoes.length === 1) {
            idTurmaDefinitiva = versoes[0].id_turma;
            turmaDefinitivaObj = versoes[0];
            atualizarTextoBadge(); 
            exibirMensagem(`✅ Turma confirmada.`);
            if (!isModoCSV) buscarAlunosDaTurma(idTurmaDefinitiva);
        }
    });

    if (fecharModalBtn) {
        fecharModalBtn.addEventListener('click', () => {
            modalPeriodo.classList.add('hidden');
            resetarTurmaDropdown(); 
        });
    }

    // ==========================================
    // LÓGICA DE TRANSFERÊNCIA DE TURMAS (CASCATA & MODAIS)
    // ==========================================
    
    // 1. Abrir Modal de Filtros e preparar Cascata
    if (btnTrocarTurma) {
        btnTrocarTurma.addEventListener('click', () => {
            // Preenche o ano letivo de forma única
            const anosUnicos = [...new Set(todasTurmasDoBanco.map(t => t.ano_letivo))].sort((a, b) => b - a);
            filtroAno.innerHTML = '<option value="">Selecione o Ano Letivo</option>';
            anosUnicos.forEach(ano => {
                filtroAno.innerHTML += `<option value="${ano}">${ano}</option>`;
            });
            
            // Reseta os outros
            filtroSemestre.innerHTML = '<option value="">Selecione o Semestre (Opcional)</option>';
            filtroTrimestre.innerHTML = '<option value="">Selecione o Trimestre (Opcional)</option>';
            filtroTurmaDestino.innerHTML = '<option value="">Selecione a Turma de Destino</option>';
            btnAvancarTransferencia.disabled = true;

            modalTransferenciaFiltros.classList.remove('hidden');
        });
    }

    // 2. Atualizar selects em cascata
    function atualizarCascata() {
        const ano = filtroAno.value;
        const sem = filtroSemestre.value;
        const tri = filtroTrimestre.value;

        // Turmas filtradas pelo que foi selecionado
        let turmasFiltradas = todasTurmasDoBanco;
        
        if (ano) turmasFiltradas = turmasFiltradas.filter(t => t.ano_letivo == ano);
        if (sem) turmasFiltradas = turmasFiltradas.filter(t => t.semestre_letivo == sem);
        if (tri) turmasFiltradas = turmasFiltradas.filter(t => t.trimestre_letivo == tri);

        // Preencher Semestre (Apenas se o Ano estiver selecionado)
        if (e.target.id === 'filtro-ano') {
            const semsUnicos = [...new Set(turmasFiltradas.map(t => t.semestre_letivo).filter(v => v))].sort();
            filtroSemestre.innerHTML = '<option value="">Selecione o Semestre (Opcional)</option>';
            semsUnicos.forEach(s => filtroSemestre.innerHTML += `<option value="${s}">${s}º Semestre</option>`);
            
            const trisUnicos = [...new Set(turmasFiltradas.map(t => t.trimestre_letivo).filter(v => v))].sort();
            filtroTrimestre.innerHTML = '<option value="">Selecione o Trimestre (Opcional)</option>';
            trisUnicos.forEach(t => filtroTrimestre.innerHTML += `<option value="${t}">${t}º Trimestre</option>`);
        }

        // Preencher Turma de Destino
        filtroTurmaDestino.innerHTML = '<option value="">Selecione a Turma de Destino</option>';
        turmasFiltradas.forEach(t => {
            filtroTurmaDestino.innerHTML += `<option value="${t.id_turma}">${t.desc_turma} - ${t.turno}</option>`;
        });

        verificarAvanço();
    }

    function verificarAvanço() {
        btnAvancarTransferencia.disabled = filtroTurmaDestino.value === "";
    }

    [filtroAno, filtroSemestre, filtroTrimestre].forEach(el => {
        el.addEventListener('change', (e) => atualizarCascata(e));
    });

    filtroTurmaDestino.addEventListener('change', verificarAvanço);

    // Cancelar Filtros
    btnCancelarFiltros.addEventListener('click', () => {
        modalTransferenciaFiltros.classList.add('hidden');
    });

    // 3. Avançar para Confirmação
    btnAvancarTransferencia.addEventListener('click', () => {
        modalTransferenciaFiltros.classList.add('hidden');
        
        // Pega os alunos marcados na tela
        listaAlunosTransferencia.innerHTML = '';
        checkboxesAlunos.forEach(cb => {
            if (cb.checked) {
                const nome = cb.getAttribute('data-nome');
                const simade = cb.value;
                listaAlunosTransferencia.innerHTML += `<div>• ${nome} <small>(SIMADE: ${simade})</small></div>`;
            }
        });

        // Pega o nome da turma selecionada no select
        const turmaTexto = filtroTurmaDestino.options[filtroTurmaDestino.selectedIndex].text;
        textoConfirmacaoTurma.textContent = `Os alunos acima serão transferidos para a turma: ${turmaTexto}`;

        modalTransferenciaConfirmacao.classList.remove('hidden');
    });

    // Cancelar Confirmação
    btnCancelarTransferencia.addEventListener('click', () => {
        modalTransferenciaConfirmacao.classList.add('hidden');
    });

    // 4. Efetivar Transferência no Banco de Dados
    btnConfirmarTransferencia.addEventListener('click', async () => {
        const idTurmaNova = filtroTurmaDestino.value;
        const alunosSimadeArray = [];

        checkboxesAlunos.forEach(cb => {
            if (cb.checked) alunosSimadeArray.push(cb.value);
        });

        modalTransferenciaConfirmacao.classList.add('hidden');
        exibirMensagem('A transferir alunos...', false);

        const formData = new FormData();
        formData.append('acao', 'transferir_alunos');
        formData.append('id_turma_destino', idTurmaNova);
        formData.append('alunos_simade', JSON.stringify(alunosSimadeArray));

        try {
            const response = await fetch('Backend.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.sucesso) {
                exibirMensagem('✅ ' + result.mensagem, false);
                
                // Mágica Visual: Some com os alunos transferidos
                let quantidadeSalva = 0;
                checkboxesAlunos.forEach(cb => {
                    if (cb.checked) {
                        quantidadeSalva++;
                        const itemDoAluno = cb.parentElement;
                        itemDoAluno.classList.add('salvo-sucesso'); 
                        
                        setTimeout(() => itemDoAluno.remove(), 700);
                    }
                });

                const todosForamSalvos = (quantidadeSalva === checkboxesAlunos.length);
                setTimeout(() => {
                    if (todosForamSalvos) {
                        limparTelaECancelar(true); 
                    } else {
                        checkboxesAlunos = document.querySelectorAll('.check-aluno');
                        if (checkboxTodos) checkboxTodos.checked = false;
                        atualizarEstadoBotoes(); 
                    }
                }, 750);

            } else {
                exibirMensagem('❌ ' + result.erro);
            }
        } catch (error) {
            exibirMensagem('❌ Erro de comunicação ao transferir.');
        }
    });


    // ==========================================
    // NOVA FUNÇÃO: BUSCAR ALUNOS DA TURMA
    // ==========================================
    async function buscarAlunosDaTurma(id_turma) {
        const formData = new FormData();
        formData.append('acao', 'listar_alunos_por_turma');
        formData.append('id_turma', id_turma);

        try {
            const response = await fetch('Backend.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.sucesso) {
                if (inputPesquisa) inputPesquisa.value = ''; 
                if (result.dados.length > 0) {
                    renderizarAlunos(result.dados, false); 
                } else {
                    const itensAntigos = containerAlunos.querySelectorAll('.aluno-item');
                    itensAntigos.forEach(item => item.remove());
                    
                    if (checkboxTodos) checkboxTodos.checked = false;
                    checkboxesAlunos = [];
                    atualizarEstadoBotoes();

                    exibirMensagem('Nenhum aluno cadastrado nesta turma.', true);
                }
            } else {
                exibirMensagem('❌ ' + result.erro);
            }
        } catch (error) {
            exibirMensagem('❌ Erro ao buscar os alunos da turma.');
        }
    }

    // ==========================================
    // FUNÇÃO: LIMPAR TELA E CANCELAR OPERAÇÕES
    // ==========================================
    function limparTelaECancelar(forcarLimpezaTotal = false) {
        containerAlunos.classList.remove('modo-edicao');
        btnEditar.textContent = 'Editar';
        btnEditar.style.backgroundColor = '#ffffff'; 
        btnEditar.style.color = 'var(--color-primary)';
        
        if (checkboxTodos) checkboxTodos.checked = false;
        checkboxesAlunos = document.querySelectorAll('.check-aluno'); 
        checkboxesAlunos.forEach(cb => cb.checked = false); 
        
        if (isModoCSV || forcarLimpezaTotal) {
            const itensAntigos = containerAlunos.querySelectorAll('.aluno-item');
            itensAntigos.forEach(item => item.remove());
            
            if (isModoCSV && inputPesquisa) inputPesquisa.value = ''; 
            if (isModoCSV) resetarTurmaDropdown(); 
            
            isModoCSV = false; 
        }
        
        atualizarEstadoBotoes();
    }

    // ==========================================
    // LÓGICA DO BOTÃO EDITAR / CANCELAR ("SALVAR")
    // ==========================================
    if (btnEditar) {
        btnEditar.addEventListener('click', () => {
            if (containerAlunos.classList.contains('modo-edicao')) {
                limparTelaECancelar(false); 
            } else {
                const itensNaTela = containerAlunos.querySelectorAll('.aluno-item').length;
                if (itensNaTela > 0) {
                    containerAlunos.classList.add('modo-edicao');
                    btnEditar.textContent = 'Salvar'; 
                    btnEditar.style.backgroundColor = 'var(--color-danger)'; 
                    btnEditar.style.color = 'white';
                }
            }
        });
    }

    // ==========================================
    // LÓGICA DE PESQUISA DINÂMICA (NOME + TURMA)
    // ==========================================
    if (inputPesquisa) {
        inputPesquisa.addEventListener('input', function(e) {
            const termo = e.target.value.trim();
            clearTimeout(debounceTimeout);

            if (termo.length === 0) {
                if (!isModoCSV && idTurmaDefinitiva !== null) {
                    buscarAlunosDaTurma(idTurmaDefinitiva);
                } else {
                    limparTelaECancelar(true);
                    resetarTurmaDropdown(); 
                }
                return;
            }

            debounceTimeout = setTimeout(async () => {
                const formData = new FormData();
                formData.append('acao', 'pesquisar_alunos');
                formData.append('termo', termo);

                if (!isModoCSV && idTurmaDefinitiva !== null) {
                    formData.append('id_turma', idTurmaDefinitiva);
                }

                try {
                    const response = await fetch('Backend.php', { method: 'POST', body: formData });
                    const result = await response.json();

                    if (result.sucesso) {
                        isModoCSV = false; 
                        
                        if (result.dados.length > 0) {
                            renderizarAlunos(result.dados, false); 
                        } else {
                            const itensAntigos = containerAlunos.querySelectorAll('.aluno-item');
                            itensAntigos.forEach(item => item.remove());
                            if (checkboxTodos) checkboxTodos.checked = false;
                            atualizarEstadoBotoes();

                            exibirMensagem('Nenhum aluno encontrado.', true);
                        }
                    } else {
                        exibirMensagem('❌ ' + result.erro);
                    }
                } catch (error) {
                    exibirMensagem('❌ Erro na pesquisa.');
                }
            }, 400); 
        });
    }

    // ==========================================
    // UPLOAD CSV
    // ==========================================
    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.accept = '.csv';
    inputFile.style.display = 'none';
    document.body.appendChild(inputFile);

    if (btnUpload) btnUpload.addEventListener('click', () => inputFile.click());

    inputFile.addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('acao', 'upload_csv');
        formData.append('arquivo_csv', file);

        exibirMensagem('A processar ficheiro...', false);

        try {
            const response = await fetch('Backend.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.sucesso) {
                isModoCSV = true; 
                atualizarTextoBadge(); 
                
                if (inputPesquisa) inputPesquisa.value = ''; 
                renderizarAlunos(result.dados, true); 
                exibirMensagem('✅ Alunos extraídos do CSV. Selecione e guarde.');
            } else {
                exibirMensagem('❌ ' + result.erro);
            }
        } catch (error) {
            exibirMensagem('❌ Erro ao conectar com o servidor PHP.');
        }
        this.value = ''; 
    });

    // ==========================================
    // RENDERIZAÇÃO INTELIGENTE DE ALUNOS NA TELA
    // ==========================================
    function renderizarAlunos(alunos, forcarEdicao) {
        const itensAntigos = containerAlunos.querySelectorAll('.aluno-item');
        itensAntigos.forEach(item => item.remove());

        alunos.forEach(aluno => {
            const div = document.createElement('div');
            div.className = 'aluno-item';
            
            const infoTurmaContexto = aluno.desc_turma ? ` | Atual: ${aluno.desc_turma}` : '';

            div.innerHTML = `
                <input type="checkbox" name="aluno" class="check-aluno" 
                       value="${aluno.simade}" 
                       data-nome="${aluno.nome}" 
                       data-nascimento="${aluno.nascimento}">
                <span><strong>${aluno.nome}</strong> — SIMADE: ${aluno.simade} | Nasc: ${aluno.nascimento}${infoTurmaContexto}</span>
            `;
            containerAlunos.appendChild(div);
        });

        checkboxesAlunos = document.querySelectorAll('.check-aluno');
        
        if (forcarEdicao) {
            containerAlunos.classList.add('modo-edicao');
            btnEditar.textContent = 'Salvar'; 
            btnEditar.style.backgroundColor = 'var(--color-danger)'; 
            btnEditar.style.color = 'white';
        } else {
            containerAlunos.classList.remove('modo-edicao');
            btnEditar.textContent = 'Editar';
            btnEditar.style.backgroundColor = '#ffffff'; 
            btnEditar.style.color = 'var(--color-primary)';
            if (checkboxTodos) checkboxTodos.checked = false;
        }

        atualizarEstadoBotoes();
    }

    if (checkboxTodos) {
        checkboxTodos.addEventListener('change', function(e) {
            const isChecked = e.target.checked;
            checkboxesAlunos.forEach(cb => cb.checked = isChecked);
            atualizarEstadoBotoes();
        });
    }

    // ==========================================
    // LÓGICA DE CADASTRO COM ANIMAÇÃO VISUAL
    // ==========================================
    if (btnCadastrar) {
        btnCadastrar.addEventListener('click', async function() {
            if (!idTurmaDefinitiva) return;

            const alunosParaSalvar = [];
            checkboxesAlunos.forEach(cb => {
                if (cb.checked) {
                    alunosParaSalvar.push({
                        simade: cb.value,
                        nome: cb.getAttribute('data-nome'),
                        nascimento: cb.getAttribute('data-nascimento')
                    });
                }
            });

            if (alunosParaSalvar.length === 0) return; 

            exibirMensagem('A guardar na base de dados...', false);
            
            const formData = new FormData();
            formData.append('acao', 'salvar_alunos_csv');
            formData.append('id_turma', idTurmaDefinitiva); 
            formData.append('alunos', JSON.stringify(alunosParaSalvar)); 

            try {
                const response = await fetch('Backend.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.sucesso) {
                    exibirMensagem('✅ ' + result.mensagem, false); 
                    
                    let quantidadeSalva = 0;

                    checkboxesAlunos.forEach(cb => {
                        if (cb.checked) {
                            quantidadeSalva++;
                            const itemDoAluno = cb.parentElement;
                            itemDoAluno.classList.add('salvo-sucesso'); 
                            
                            setTimeout(() => {
                                itemDoAluno.remove();
                            }, 700);
                        }
                    });

                    const todosForamSalvos = (quantidadeSalva === checkboxesAlunos.length);

                    setTimeout(() => {
                        if (todosForamSalvos) {
                            limparTelaECancelar(true); 
                        } else {
                            checkboxesAlunos = document.querySelectorAll('.check-aluno');
                            if (checkboxTodos) checkboxTodos.checked = false;
                            atualizarEstadoBotoes(); 
                        }
                    }, 750);

                } else {
                    exibirMensagem('❌ ' + result.erro, false);
                }
            } catch (error) {
                exibirMensagem('❌ Erro de comunicação ao guardar.');
            }
        });
    }

    function exibirMensagem(texto, autoApagar = true) {
        const mensagemExistente = document.querySelector('.confirm-message');
        if (mensagemExistente) mensagemExistente.remove();

        const mensagem = document.createElement('div');
        mensagem.className = 'confirm-message';
        mensagem.innerHTML = `<p>${texto}</p>`;
        document.body.appendChild(mensagem);
        
        requestAnimationFrame(() => mensagem.style.opacity = '1');

        if (autoApagar) {
            setTimeout(() => {
                mensagem.style.opacity = '0';
                setTimeout(() => mensagem.remove(), 500);
            }, 3000);
        }
    }
    
    atualizarEstadoBotoes(); 
});