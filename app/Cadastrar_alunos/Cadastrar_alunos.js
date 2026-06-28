document.addEventListener('DOMContentLoaded', function() {
    
    const btnEditar = document.getElementById('btn-editar');
    const btnUpload = document.getElementById('btn-upload');
    const btnCadastrar = document.getElementById('btn-cadastrar');
    const btnExcluir = document.getElementById('btn-excluir');
    const btnTrocarTurma = document.getElementById('btn-trocar-turma'); 
    
    const inputPesquisa = document.getElementById('input-pesquisa'); 
    const containerAlunos = document.getElementById('container-alunos');
    const checkboxTodos = document.getElementById('aluno-todos');
    let checkboxesAlunos = document.querySelectorAll('.check-aluno'); 
    
    const selectTurmas = document.getElementById('turmas');
    const modalPeriodo = document.getElementById('modal-periodo');
    const containerOpcoesPeriodo = document.getElementById('opcoes-periodo');
    const fecharModalBtn = document.getElementById('fechar-modal');
    
    const infoTurmaBadge = document.getElementById('turma-selecionada-info');

    // Elementos de Transferência
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
    const containerNaoTransferidos = document.getElementById('container-nao-transferidos');
    const listaAlunosNaoTransferidos = document.getElementById('lista-alunos-nao-transferidos');

    // Elementos do Modal de Exclusão
    const modalExcluirConfirmacao = document.getElementById('modal-excluir-confirmacao');
    const btnCancelarExcluir = document.getElementById('btn-cancelar-excluir');
    const btnConfirmarExcluir = document.getElementById('btn-confirmar-excluir');
    const textoExcluirConfirmacao = document.getElementById('texto-excluir-confirmacao');

    let todasTurmasDoBanco = [];
    let idTurmaDefinitiva = null; 
    let turmaDefinitivaObj = null; 
    let isModoCSV = false;
    let debounceTimeout; 
    let countdownExcluir; 

    // ==========================================
    // LÓGICA DE ESTADO DOS BOTÕES
    // ==========================================
    function atualizarEstadoBotoes() {
        const algumAlunoMarcado = document.querySelectorAll('.check-aluno:checked').length > 0;
        const turmaConfirmada = idTurmaDefinitiva !== null;

        if (btnCadastrar) {
            // CORREÇÃO DEFINITIVA: O botão Cadastrar agora SÓ ativa se os alunos vierem do CSV (isModoCSV === true)
            btnCadastrar.disabled = !(algumAlunoMarcado && turmaConfirmada && isModoCSV);
        }

        if (btnExcluir) btnExcluir.disabled = !algumAlunoMarcado;
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
    // LÓGICA DE TRANSFERÊNCIA DE TURMAS 
    // ==========================================
    if (btnTrocarTurma) {
        btnTrocarTurma.addEventListener('click', () => {
            const anosUnicos = [...new Set(todasTurmasDoBanco.map(t => t.ano_letivo).filter(a => a))].sort((a, b) => b - a);
            filtroAno.innerHTML = '<option value="">Selecione o Ano Letivo</option>';
            anosUnicos.forEach(ano => {
                filtroAno.innerHTML += `<option value="${ano}">${ano}</option>`;
            });
            
            filtroSemestre.innerHTML = `
                <option value="">Selecione o Semestre (Opcional)</option>
                <option value="1">1º Semestre</option>
                <option value="2">2º Semestre</option>
            `;
            
            filtroTrimestre.innerHTML = `
                <option value="">Selecione o Trimestre (Opcional)</option>
                <option value="1">1º Trimestre</option>
                <option value="2">2º Trimestre</option>
                <option value="3">3º Trimestre</option>
            `;

            atualizarCascata(); 
            btnAvancarTransferencia.disabled = true;
            modalTransferenciaFiltros.classList.remove('hidden');
        });
    }

    function atualizarCascata() {
        const ano = filtroAno.value;
        const sem = filtroSemestre.value;
        const tri = filtroTrimestre.value;

        let turmasFiltradas = todasTurmasDoBanco;
        
        if (ano) turmasFiltradas = turmasFiltradas.filter(t => String(t.ano_letivo) === ano);
        if (sem) turmasFiltradas = turmasFiltradas.filter(t => String(t.semestre_letivo) === sem);
        if (tri) turmasFiltradas = turmasFiltradas.filter(t => String(t.trimestre_letivo) === tri);

        filtroTurmaDestino.innerHTML = '<option value="">Selecione a Turma de Destino</option>';
        turmasFiltradas.forEach(t => {
            const descSem = t.semestre_letivo ? ` | ${t.semestre_letivo}º Sem` : '';
            const descTri = t.trimestre_letivo ? ` | ${t.trimestre_letivo}º Tri` : '';
            filtroTurmaDestino.innerHTML += `<option value="${t.id_turma}">${t.desc_turma} (${t.turno})${descSem}${descTri}</option>`;
        });

        verificarAvanço();
    }

    function verificarAvanço() {
        btnAvancarTransferencia.disabled = filtroTurmaDestino.value === "";
    }

    [filtroAno, filtroSemestre, filtroTrimestre].forEach(el => {
        el.addEventListener('change', atualizarCascata);
    });

    filtroTurmaDestino.addEventListener('change', verificarAvanço);

    btnCancelarFiltros.addEventListener('click', () => {
        modalTransferenciaFiltros.classList.add('hidden');
    });

    btnAvancarTransferencia.addEventListener('click', () => {
        modalTransferenciaFiltros.classList.add('hidden');
        listaAlunosTransferencia.innerHTML = '';
        listaAlunosNaoTransferidos.innerHTML = '';
        const idTurmaNova = filtroTurmaDestino.value;
        let qtdTransferir = 0;
        let qtdNaoTransferir = 0;

        checkboxesAlunos.forEach(cb => {
            if (cb.checked) {
                const nome = cb.getAttribute('data-nome');
                const simade = cb.value;
                const idTurmaAtual = cb.getAttribute('data-id-turma');

                if (idTurmaAtual === idTurmaNova) {
                    listaAlunosNaoTransferidos.innerHTML += `<div style="color: var(--color-danger);">• <strong>${nome}</strong> <br><small>SIMADE: ${simade}</small></div>`;
                    qtdNaoTransferir++;
                } else {
                    listaAlunosTransferencia.innerHTML += `<div><strong>${nome}</strong> <br><small style="color: #666;">SIMADE: ${simade}</small></div>`;
                    qtdTransferir++;
                }
            }
        });

        if (qtdNaoTransferir > 0) containerNaoTransferidos.classList.remove('hidden');
        else containerNaoTransferidos.classList.add('hidden');

        if (qtdTransferir === 0) {
            listaAlunosTransferencia.innerHTML = '<div style="color: #666; font-style: italic;">Nenhum aluno elegível para transferência.</div>';
            btnConfirmarTransferencia.disabled = true;
        } else {
            btnConfirmarTransferencia.disabled = false;
        }

        const turmaTexto = filtroTurmaDestino.options[filtroTurmaDestino.selectedIndex].text;
        textoConfirmacaoTurma.textContent = `A transferir para: ${turmaTexto}`;
        modalTransferenciaConfirmacao.classList.remove('hidden');
    });

    btnCancelarTransferencia.addEventListener('click', () => {
        modalTransferenciaConfirmacao.classList.add('hidden');
    });

    btnConfirmarTransferencia.addEventListener('click', async () => {
        const idTurmaNova = filtroTurmaDestino.value;
        const turmaTextoLimpo = filtroTurmaDestino.options[filtroTurmaDestino.selectedIndex].text.split('(')[0].trim(); 
        const alunosSimadeArray = [];

        checkboxesAlunos.forEach(cb => {
            if (cb.checked && cb.getAttribute('data-id-turma') !== idTurmaNova) {
                alunosSimadeArray.push(cb.value);
            }
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
                exibirMensagem('✅ ' + result.mensagem); 
                
                checkboxesAlunos.forEach(cb => {
                    if (cb.checked && cb.getAttribute('data-id-turma') !== idTurmaNova) {
                        const itemDoAluno = cb.parentElement;
                        itemDoAluno.classList.add('transferido-sucesso');
                        cb.setAttribute('data-id-turma', idTurmaNova);
                        const span = itemDoAluno.querySelector('span');
                        const nome = cb.getAttribute('data-nome');
                        const simade = cb.value;
                        const nascimento = cb.getAttribute('data-nascimento');
                        
                        span.innerHTML = `<strong>${nome}</strong> — SIMADE: ${simade} | Nasc: ${nascimento} | Atual: ${turmaTextoLimpo}`;

                        setTimeout(() => itemDoAluno.remove(), 1500); 
                    }
                });

                setTimeout(() => {
                    limparTelaECancelar(false); 
                }, 1500);

            } else {
                exibirMensagem('❌ ' + result.erro); 
            }
        } catch (error) {
            exibirMensagem('❌ Erro de comunicação ao transferir.'); 
        }
    });

    // ==========================================
    // FUNCIONALIDADE DE EXCLUSÃO (CORRIGIDA)
    // ==========================================
    if (btnExcluir) {
        btnExcluir.addEventListener('click', function() {
            const marcados = document.querySelectorAll('.check-aluno:checked');
            if (marcados.length === 0) return;

            if (isModoCSV) {
                marcados.forEach(cb => {
                    const itemDoAluno = cb.parentElement;
                    itemDoAluno.remove();
                });
                
                checkboxesAlunos = document.querySelectorAll('.check-aluno');
                if (checkboxTodos) checkboxTodos.checked = false;
                atualizarEstadoBotoes();
                
                if (checkboxesAlunos.length === 0) {
                    limparTelaECancelar(true);
                }
                
                exibirMensagem('✅ Registro(s) removido(s) da lista com sucesso.');
            } else {
                let tempoRestante = 5;
                
                btnConfirmarExcluir.disabled = true;
                btnConfirmarExcluir.textContent = `Confirmar (${tempoRestante}s)`;
                
                if (marcados.length > 1) {
                    textoExcluirConfirmacao.textContent = `Você tem certeza que deseja excluir esses ${marcados.length} alunos? Eles serão apagados do banco e se precisar deles de volta terá que fazer o cadastro com as informações necessárias.`;
                } else {
                    textoExcluirConfirmacao.textContent = "Você tem certeza que deseja excluir esse aluno? Ele será apagado do banco e se precisar dele de volta terá que fazer o cadastro com as informações necessárias.";
                }

                modalExcluirConfirmacao.classList.remove('hidden');

                countdownExcluir = setInterval(() => {
                    tempoRestante--;
                    if (tempoRestante > 0) {
                        btnConfirmarExcluir.textContent = `Confirmar (${tempoRestante}s)`;
                    } else {
                        clearInterval(countdownExcluir);
                        btnConfirmarExcluir.textContent = "Confirmar";
                        btnConfirmarExcluir.disabled = false;
                    }
                }, 1000);
            }
        });
    }

    if (btnCancelarExcluir) {
        btnCancelarExcluir.addEventListener('click', () => {
            clearInterval(countdownExcluir); 
            modalExcluirConfirmacao.classList.add('hidden');
        });
    }

    if (btnConfirmarExcluir) {
        btnConfirmarExcluir.addEventListener('click', async () => {
            const marcados = document.querySelectorAll('.check-aluno:checked');
            const alunosSimadeArray = [];

            marcados.forEach(cb => alunosSimadeArray.push(cb.value));

            modalExcluirConfirmacao.classList.add('hidden');
            exibirMensagem('A apagar do banco de dados...', false);

            const formData = new FormData();
            formData.append('acao', 'excluir_alunos');
            formData.append('alunos_simade', JSON.stringify(alunosSimadeArray));

            try {
                const response = await fetch('Backend.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.sucesso) {
                    exibirMensagem('✅ ' + result.mensagem);
                    
                    // Remove cirurgicamente apenas os alunos selecionados via animação
                    marcados.forEach(cb => {
                        const itemDoAluno = cb.parentElement;
                        itemDoAluno.style.backgroundColor = 'var(--color-danger)';
                        itemDoAluno.style.transition = 'all 0.5s ease';
                        itemDoAluno.style.opacity = '0';
                        itemDoAluno.style.transform = 'scale(0.9) translateX(-30px)';
                        
                        setTimeout(() => itemDoAluno.remove(), 500);
                    });

                    // CORREÇÃO: Fecha o modo de edição e mantém quem sobrou na tela de forma limpa!
                    setTimeout(() => {
                        containerAlunos.classList.remove('modo-edicao');
                        btnEditar.textContent = 'Editar';
                        btnEditar.style.backgroundColor = '#ffffff'; 
                        btnEditar.style.color = 'var(--color-primary)';
                        if (checkboxTodos) checkboxTodos.checked = false;

                        checkboxesAlunos = document.querySelectorAll('.check-aluno');
                        atualizarEstadoBotoes();

                        // Só dá o reset total caso a tela tenha ficado 100% vazia de fato
                        if (checkboxesAlunos.length === 0) {
                            limparTelaECancelar(true);
                        }
                    }, 550);

                } else {
                    exibirMensagem('❌ ' + result.erro);
                }
            } catch (error) {
                exibirMensagem('❌ Erro de comunicação ao excluir.');
            }
        });
    }

    // ==========================================
    // BUSCAR ALUNOS DA TURMA
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
                       data-nascimento="${aluno.nascimento}"
                       data-id-turma="${aluno.id_turma || ''}">
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