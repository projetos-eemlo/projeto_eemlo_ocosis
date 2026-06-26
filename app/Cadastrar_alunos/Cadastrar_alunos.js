document.addEventListener('DOMContentLoaded', function() {
    
    const btnEditar = document.getElementById('btn-editar');
    const btnUpload = document.getElementById('btn-upload');
    const btnSalvar = document.getElementById('btn-salvar');
    const btnExcluir = document.getElementById('btn-excluir');
    const inputPesquisa = document.getElementById('input-pesquisa'); 
    const containerAlunos = document.getElementById('container-alunos');
    const checkboxTodos = document.getElementById('aluno-todos');
    let checkboxesAlunos = document.querySelectorAll('.check-aluno'); 
    
    const selectTurmas = document.getElementById('turmas');
    const modalPeriodo = document.getElementById('modal-periodo');
    const containerOpcoesPeriodo = document.getElementById('opcoes-periodo');
    const fecharModalBtn = document.getElementById('fechar-modal');
    
    const infoTurmaBadge = document.getElementById('turma-selecionada-info');

    let todasTurmasDoBanco = [];
    let idTurmaDefinitiva = null; 
    let isModoCSV = false;
    let debounceTimeout; 

    // ==========================================
    // LÓGICA DE ESTADO DOS BOTÕES
    // ==========================================
    function atualizarEstadoBotoes() {
        const algumAlunoMarcado = document.querySelectorAll('.check-aluno:checked').length > 0;
        const turmaConfirmada = idTurmaDefinitiva !== null;

        if (btnSalvar) {
            btnSalvar.disabled = !(algumAlunoMarcado && turmaConfirmada);
        }

        if (btnExcluir) {
            if (isModoCSV) {
                btnExcluir.disabled = true;
            } else {
                btnExcluir.disabled = !algumAlunoMarcado;
            }
        }
    }

    containerAlunos.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('check-aluno')) {
            atualizarEstadoBotoes();
        }
    });

    // --- FUNÇÕES DA DIV VISUAL ---
    function exibirInfoTurma(turma) {
        const sem = turma.semestre_letivo ? `${turma.semestre_letivo}ºSem` : '-';
        const tri = turma.trimestre_letivo ? `${turma.trimestre_letivo}ºTri` : '-';
        
        infoTurmaBadge.textContent = `Cadastro/Transferência: ${turma.desc_turma}\\${turma.ano_letivo}\\${sem}\\${tri}`;
        infoTurmaBadge.classList.add('visivel');
        atualizarEstadoBotoes();
    }

    function ocultarInfoTurma() {
        infoTurmaBadge.textContent = '';
        infoTurmaBadge.classList.remove('visivel');
        atualizarEstadoBotoes();
    }

    // --- CARREGAR E AGRUPAR TURMAS ---
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

    // --- LÓGICA DO MODAL DE TURMAS ---
    selectTurmas.addEventListener('change', function(e) {
        idTurmaDefinitiva = null; 
        ocultarInfoTurma(); 

        const nomeSelecionado = e.target.value;
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
                    modalPeriodo.classList.add('hidden'); 
                    exibirInfoTurma(v); 
                    exibirMensagem(`✅ Turma confirmada.`);
                });
                
                containerOpcoesPeriodo.appendChild(btn);
            });
            modalPeriodo.classList.remove('hidden');

        } else if (versoes.length === 1) {
            idTurmaDefinitiva = versoes[0].id_turma;
            exibirInfoTurma(versoes[0]); 
            exibirMensagem(`✅ Turma confirmada.`);
        }
    });

    if (fecharModalBtn) {
        fecharModalBtn.addEventListener('click', () => {
            modalPeriodo.classList.add('hidden');
            selectTurmas.value = ""; 
            idTurmaDefinitiva = null;
            ocultarInfoTurma();
        });
    }

    // ==========================================
    // FUNÇÃO: LIMPAR TELA E CANCELAR OPERAÇÕES
    // ==========================================
    function limparTelaECancelar() {
        containerAlunos.classList.remove('modo-edicao');
        btnEditar.textContent = 'Editar';
        btnEditar.style.backgroundColor = '#ffffff'; 
        btnEditar.style.color = 'var(--color-primary)';
        
        const itensAntigos = containerAlunos.querySelectorAll('.aluno-item');
        itensAntigos.forEach(item => item.remove());
        
        if (checkboxTodos) checkboxTodos.checked = false;
        checkboxesAlunos = document.querySelectorAll('.check-aluno'); 
        
        if (inputPesquisa) inputPesquisa.value = ''; 
        isModoCSV = false; 
        atualizarEstadoBotoes();
    }

    // ==========================================
    // LÓGICA DO BOTÃO EDITAR / CANCELAR
    // ==========================================
    if (btnEditar) {
        btnEditar.addEventListener('click', () => {
            if (containerAlunos.classList.contains('modo-edicao')) {
                // Se já está editando, o clique significa CANCELAR e LIMPAR TELA
                limparTelaECancelar();
            } else {
                // Se NÃO está editando, o clique ativa as CAIXINHAS para selecionar
                const itensNaTela = containerAlunos.querySelectorAll('.aluno-item').length;
                if (itensNaTela > 0) {
                    containerAlunos.classList.add('modo-edicao');
                    btnEditar.textContent = 'Cancelar';
                    btnEditar.style.backgroundColor = 'var(--color-danger)'; 
                    btnEditar.style.color = 'white';
                }
            }
        });
    }

    // ==========================================
    // LÓGICA DE PESQUISA DINÂMICA (LIVE SEARCH)
    // ==========================================
    if (inputPesquisa) {
        inputPesquisa.addEventListener('input', function(e) {
            const termo = e.target.value.trim();

            clearTimeout(debounceTimeout);

            if (termo.length === 0) {
                limparTelaECancelar();
                return;
            }

            debounceTimeout = setTimeout(async () => {
                const formData = new FormData();
                formData.append('acao', 'pesquisar_alunos');
                formData.append('termo', termo);

                try {
                    const response = await fetch('Backend.php', { method: 'POST', body: formData });
                    const result = await response.json();

                    if (result.sucesso) {
                        isModoCSV = false; 
                        
                        if (result.dados.length > 0) {
                            // false = Renderiza no modo Consulta (sem caixinhas abertas)
                            renderizarAlunos(result.dados, false); 
                        } else {
                            limparTelaECancelar(); 
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
                if (inputPesquisa) inputPesquisa.value = ''; 
                // true = Renderiza já forçando a edição (caixinhas abertas)
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
        
        // Define o visual com base na origem (Pesquisa vs CSV)
        if (forcarEdicao) {
            containerAlunos.classList.add('modo-edicao');
            btnEditar.textContent = 'Cancelar';
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
    // LÓGICA DE SALVAMENTO COM ANIMAÇÃO VISUAL
    // ==========================================
    if (btnSalvar) {
        btnSalvar.addEventListener('click', async function() {
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
                            limparTelaECancelar(); 
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