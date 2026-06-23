document.addEventListener('DOMContentLoaded', function() {
    
    // --- ELEMENTOS DO DOM ---
    const btnEditar = document.getElementById('btn-editar');
    const btnUpload = document.getElementById('btn-upload');
    const containerAlunos = document.getElementById('container-alunos');
    
    const checkboxTodos = document.getElementById('aluno-todos');
    let checkboxesAlunos = document.querySelectorAll('.check-aluno'); 
    
    const selectTurmas = document.getElementById('turmas');
    const modalPeriodo = document.getElementById('modal-periodo');
    const fecharModalBtn = document.getElementById('fechar-modal');
    const botoesPeriodo = document.querySelectorAll('.btn-periodo');

    // --- CRIA O INPUT FILE ESCONDIDO (Para o CSV) ---
    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.accept = '.csv';
    inputFile.style.display = 'none';
    document.body.appendChild(inputFile);

    // --- FUNÇÃO DE MODO DE EDIÇÃO (MOSTRAR CHECKBOXES) ---
    function alternarModoEdicao(forcarAtivo = false) {
        if (forcarAtivo) {
            containerAlunos.classList.add('modo-edicao');
        } else {
            containerAlunos.classList.toggle('modo-edicao');
        }
        
        if (containerAlunos.classList.contains('modo-edicao')) {
            btnEditar.textContent = 'Cancelar';
            btnEditar.style.backgroundColor = 'var(--color-danger)'; 
            btnEditar.style.color = 'white';
        } else {
            btnEditar.textContent = 'Editar';
            btnEditar.style.backgroundColor = '#ffffff'; 
            btnEditar.style.color = 'var(--color-primary)';
            
            // Desmarca tudo ao cancelar
            if (checkboxTodos) checkboxTodos.checked = false;
            checkboxesAlunos.forEach(cb => cb.checked = false);
        }
    }

    if (btnEditar) btnEditar.addEventListener('click', () => alternarModoEdicao(false));

    // --- LÓGICA DE UPLOAD CSV (Frontend -> PHP Universal) ---
    if (btnUpload) {
        btnUpload.addEventListener('click', function() {
            inputFile.click(); 
        });
    }

    inputFile.addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('acao', 'upload_csv');
        formData.append('arquivo_csv', file);

        exibirMensagem('Processando arquivo...', false);

        try {
            // Ajustado para coincidir com o nome exato do seu arquivo na imagem: Backend.php
            const response = await fetch('Backend.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();

            if (result.sucesso) {
                renderizarAlunos(result.dados);
                exibirMensagem('✅ Alunos carregados com sucesso!');
            } else {
                exibirMensagem('❌ ' + result.erro);
            }
        } catch (error) {
            console.error('Erro:', error);
            exibirMensagem('❌ Erro ao conectar com o servidor PHP. Verifique o WAMP.');
        }

        this.value = ''; 
    });

    // --- RENDERIZAR ALUNOS DO CSV NA TELA ---
    function renderizarAlunos(alunos) {
        const headerHTML = containerAlunos.querySelector('.aluno-header').outerHTML;
        containerAlunos.innerHTML = headerHTML;

        alunos.forEach(aluno => {
            const div = document.createElement('div');
            div.className = 'aluno-item';
            div.innerHTML = `
                <input type="checkbox" name="aluno" value="${aluno.simade}" class="check-aluno">
                <span><strong>${aluno.nome}</strong> — SIMADE: ${aluno.simade} | Nasc: ${aluno.nascimento}</span>
            `;
            containerAlunos.appendChild(div);
        });

        checkboxesAlunos = document.querySelectorAll('.check-aluno');
        
        const novoCheckboxTodos = document.getElementById('aluno-todos');
        if (novoCheckboxTodos) {
            novoCheckboxTodos.addEventListener('change', function(e) {
                const isChecked = e.target.checked;
                checkboxesAlunos.forEach(cb => cb.checked = isChecked);
            });
        }

        alternarModoEdicao(true);
    }

    // --- FUNÇÃO SELECIONAR TODOS (Inicial) ---
    if (checkboxTodos) {
        checkboxTodos.addEventListener('change', function(e) {
            const isChecked = e.target.checked;
            checkboxesAlunos.forEach(cb => {
                cb.checked = isChecked;
            });
        });
    }

    // --- LÓGICA DO MODAL DE TURMAS ---
    if (selectTurmas) {
        selectTurmas.addEventListener('change', function(e) {
            if (e.target.value !== "") {
                modalPeriodo.classList.remove('hidden');
            }
        });
    }

    if (fecharModalBtn) {
        fecharModalBtn.addEventListener('click', function() {
            modalPeriodo.classList.add('hidden');
            selectTurmas.value = ""; 
        });
    }

    if (botoesPeriodo) {
        botoesPeriodo.forEach(botao => {
            botao.addEventListener('click', function() {
                const escolha = this.textContent;
                modalPeriodo.classList.add('hidden');
                exibirMensagem(`Turma selecionada: ${escolha}`);
            });
        });
    }

    // --- FUNÇÃO DE MENSAGENS (NOTIFICAÇÕES) ---
    function exibirMensagem(texto, autoApagar = true) {
        const mensagemExistente = document.querySelector('.confirm-message');
        if (mensagemExistente) mensagemExistente.remove();

        const mensagem = document.createElement('div');
        mensagem.className = 'confirm-message';
        mensagem.innerHTML = `<p>${texto}</p>`;
        
        document.body.appendChild(mensagem);
        
        requestAnimationFrame(() => {
            mensagem.style.opacity = '1';
        });

        if (autoApagar) {
            setTimeout(() => {
                mensagem.style.opacity = '0';
                setTimeout(() => mensagem.remove(), 500);
            }, 3000);
        }
    }
});