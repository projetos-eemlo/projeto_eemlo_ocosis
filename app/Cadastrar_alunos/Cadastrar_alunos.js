document.addEventListener('DOMContentLoaded', function() {
    
    const btnEditar = document.getElementById('btn-editar');
    const btnUpload = document.getElementById('btn-upload');
    const btnSalvar = document.getElementById('btn-salvar');
    const containerAlunos = document.getElementById('container-alunos');
    const checkboxTodos = document.getElementById('aluno-todos');
    let checkboxesAlunos = document.querySelectorAll('.check-aluno'); 
    
    const selectTurmas = document.getElementById('turmas');
    const modalPeriodo = document.getElementById('modal-periodo');
    const containerOpcoesPeriodo = document.getElementById('opcoes-periodo');
    const fecharModalBtn = document.getElementById('fechar-modal');
    
    // Elemento visual da mini div
    const infoTurmaBadge = document.getElementById('turma-selecionada-info');

    let todasTurmasDoBanco = [];
    let idTurmaDefinitiva = null; 

    // --- FUNÇÕES DA NOVA DIV VISUAL ---
    function exibirInfoTurma(turma) {
        const sem = turma.semestre_letivo ? `${turma.semestre_letivo}ºSem` : '-';
        const tri = turma.trimestre_letivo ? `${turma.trimestre_letivo}ºTri` : '-';
        
        // Formatação exata solicitada:
        infoTurmaBadge.textContent = `Cadastro/Transferência para a: ${turma.desc_turma}\\${turma.ano_letivo}\\ ${sem}\\${tri}`;
        
        infoTurmaBadge.classList.add('visivel');
    }

    function ocultarInfoTurma() {
        infoTurmaBadge.textContent = '';
        infoTurmaBadge.classList.remove('visivel');
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

        const versoes = todasTurmasDoBanco.filter(t => t.desc_turma === nomeSelecionado);

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

    // --- INPUT ESCONDIDO E ALTERNAR MODO ---
    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.accept = '.csv';
    inputFile.style.display = 'none';
    document.body.appendChild(inputFile);

    function alternarModoEdicao(forcarAtivo = false) {
        if (forcarAtivo) containerAlunos.classList.add('modo-edicao');
        else containerAlunos.classList.toggle('modo-edicao');
        
        if (containerAlunos.classList.contains('modo-edicao')) {
            btnEditar.textContent = 'Cancelar';
            btnEditar.style.backgroundColor = 'var(--color-danger)'; 
            btnEditar.style.color = 'white';
        } else {
            btnEditar.textContent = 'Editar';
            btnEditar.style.backgroundColor = '#ffffff'; 
            btnEditar.style.color = 'var(--color-primary)';
            
            if (checkboxTodos) checkboxTodos.checked = false;
            checkboxesAlunos.forEach(cb => cb.checked = false);
        }
    }

    if (btnEditar) btnEditar.addEventListener('click', () => alternarModoEdicao(false));

    // --- UPLOAD CSV ---
    if (btnUpload) btnUpload.addEventListener('click', () => inputFile.click());

    inputFile.addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('acao', 'upload_csv');
        formData.append('arquivo_csv', file);

        exibirMensagem('Processando arquivo...', false);

        try {
            const response = await fetch('Backend.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.sucesso) {
                renderizarAlunos(result.dados);
                exibirMensagem('✅ Alunos extraídos do CSV. Selecione e salve.');
            } else {
                exibirMensagem('❌ ' + result.erro);
            }
        } catch (error) {
            exibirMensagem('❌ Erro ao conectar com o servidor PHP.');
        }
        this.value = ''; 
    });

    // --- RENDERIZAR NA TELA ---
    function renderizarAlunos(alunos) {
        const itensAntigos = containerAlunos.querySelectorAll('.aluno-item');
        itensAntigos.forEach(item => item.remove());

        alunos.forEach(aluno => {
            const div = document.createElement('div');
            div.className = 'aluno-item';
            div.innerHTML = `
                <input type="checkbox" name="aluno" class="check-aluno" 
                       value="${aluno.simade}" 
                       data-nome="${aluno.nome}" 
                       data-nascimento="${aluno.nascimento}">
                <span><strong>${aluno.nome}</strong> — SIMADE: ${aluno.simade} | Nasc: ${aluno.nascimento}</span>
            `;
            containerAlunos.appendChild(div);
        });

        checkboxesAlunos = document.querySelectorAll('.check-aluno');
        
        alternarModoEdicao(true);
    }

    // --- SELECIONAR TODOS ---
    if (checkboxTodos) {
        checkboxTodos.addEventListener('change', function(e) {
            const isChecked = e.target.checked;
            checkboxesAlunos.forEach(cb => cb.checked = isChecked);
        });
    }

    // --- SALVAR NO BANCO DE DADOS ---
    if (btnSalvar) {
        btnSalvar.addEventListener('click', async function() {
            if (!idTurmaDefinitiva) {
                exibirMensagem('⚠️ Atenção: Você precisa selecionar uma turma e confirmar o período!');
                return;
            }

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

            if (alunosParaSalvar.length === 0) {
                exibirMensagem('⚠️ Atenção: Selecione pelo menos um aluno na lista!');
                return;
            }

            exibirMensagem('Salvando no banco...', false);
            
            const formData = new FormData();
            formData.append('acao', 'salvar_alunos_csv');
            formData.append('id_turma', idTurmaDefinitiva); 
            formData.append('alunos', JSON.stringify(alunosParaSalvar)); 

            try {
                const response = await fetch('Backend.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.sucesso) {
                    exibirMensagem('✅ ' + result.mensagem, false); 
                    checkboxesAlunos.forEach(cb => {
                        if (cb.checked) cb.parentElement.remove();
                    });
                } else {
                    exibirMensagem('❌ ' + result.erro, false);
                }
            } catch (error) {
                exibirMensagem('❌ Erro de comunicação ao salvar.');
            }
        });
    }

    // --- FUNÇÃO AUXILIAR DE MENSAGENS ---
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
});