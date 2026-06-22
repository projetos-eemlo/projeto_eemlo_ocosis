document.addEventListener('DOMContentLoaded', function() {
    
    // --- ELEMENTOS DO DOM ---
    const btnEditar = document.getElementById('btn-editar');
    const btnUpload = document.getElementById('btn-upload');
    const containerAlunos = document.getElementById('container-alunos');
    
    const checkboxTodos = document.getElementById('aluno-todos');
    const checkboxesAlunos = document.querySelectorAll('.check-aluno');
    
    const selectTurmas = document.getElementById('turmas');
    const modalPeriodo = document.getElementById('modal-periodo');
    const fecharModalBtn = document.getElementById('fechar-modal');
    const botoesPeriodo = document.querySelectorAll('.btn-periodo');

    // --- FUNÇÃO DE MODO DE EDIÇÃO (MOSTRAR CHECKBOXES) ---
    function alternarModoEdicao() {
        containerAlunos.classList.toggle('modo-edicao');
        
        // Verifica se a classe foi adicionada ou removida para mudar o texto
        if (containerAlunos.classList.contains('modo-edicao')) {
            btnEditar.textContent = 'Cancelar';
            btnEditar.style.backgroundColor = 'var(--color-danger)'; // Fica vermelho ao cancelar
            btnEditar.style.color = 'white';
        } else {
            btnEditar.textContent = 'Editar';
            btnEditar.style.backgroundColor = '#ffffff'; // Volta ao normal
            btnEditar.style.color = 'var(--color-primary)';
            
            // Ao cancelar a edição, desmarca todos os checkboxes por garantia
            checkboxTodos.checked = false;
            checkboxesAlunos.forEach(cb => cb.checked = false);
        }
    }

    // O botão "Editar" alterna o modo
    if (btnEditar) {
        btnEditar.addEventListener('click', alternarModoEdicao);
    }

    // O botão "Upload CSV" também ativa o modo edição (mostra os checkboxes) se já não estiver ativo
    if (btnUpload) {
        btnUpload.addEventListener('click', function() {
            if (!containerAlunos.classList.contains('modo-edicao')) {
                alternarModoEdicao();
            }
            // Aqui você pode adicionar a lógica real de Upload de CSV no futuro
        });
    }

    // --- FUNÇÃO SELECIONAR TODOS ---
    if (checkboxTodos) {
        checkboxTodos.addEventListener('change', function(e) {
            const isChecked = e.target.checked;
            checkboxesAlunos.forEach(cb => {
                cb.checked = isChecked;
            });
        });
    }

    // --- LÓGICA DO MODAL DE TURMAS ---
    
    // Quando escolhe uma turma no select, abre o modal
    if (selectTurmas) {
        selectTurmas.addEventListener('change', function(e) {
            if (e.target.value !== "") {
                modalPeriodo.classList.remove('hidden');
            }
        });
    }

    // Fecha o modal pelo botão Cancelar
    if (fecharModalBtn) {
        fecharModalBtn.addEventListener('click', function() {
            modalPeriodo.classList.add('hidden');
            selectTurmas.value = ""; // Reseta o select para o estado padrão
        });
    }

    // Simula a escolha de um período dentro do modal
    if (botoesPeriodo) {
        botoesPeriodo.forEach(botao => {
            botao.addEventListener('click', function() {
                const escolha = this.textContent;
                // Fecha o modal
                modalPeriodo.classList.add('hidden');
                
                // Exibe uma mensagem provisória com a escolha (aproveitando a função do código anterior)
                exibirMensagem(`Turma selecionada: ${escolha}`);
            });
        });
    }

    // Função auxiliar para exibir notificação na tela
    function exibirMensagem(texto) {
        const mensagemExistente = document.querySelector('.confirm-message');
        if (mensagemExistente) mensagemExistente.remove();

        const mensagem = document.createElement('div');
        mensagem.className = 'confirm-message';
        mensagem.innerHTML = `<p>${texto}</p>`;
        
        containerAlunos.appendChild(mensagem);
        
        requestAnimationFrame(() => {
            mensagem.style.opacity = '1';
        });

        setTimeout(() => {
            mensagem.style.opacity = '0';
            setTimeout(() => mensagem.remove(), 500);
        }, 3000);
    }
});