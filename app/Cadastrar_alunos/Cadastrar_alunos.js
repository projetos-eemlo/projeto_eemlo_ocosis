/* =========================================
   Pequena Melhoria: Feedback Visual de Ação
   ========================================= */

document.addEventListener('DOMContentLoaded', function() {
    
    const btnSalvar = document.getElementById('btn-salvar');
    const containerAlunos = document.querySelector('.alunos');

    if (btnSalvar) {
        btnSalvar.addEventListener('click', function() {
            // Demonstração de feedback visual ao salvar
            exibirMensagemConfirmacao('✅ Dados salvos com sucesso!');
        });
    }

    // Função para criar e exibir a mensagem temporária
    function exibirMensagemConfirmacao(texto) {
        // Remove mensagem existente, se houver
        const mensagemExistente = document.querySelector('.confirm-message');
        if (mensagemExistente) {
            mensagemExistente.remove();
        }

        // Cria o elemento da mensagem
        const mensagem = document.createElement('div');
        mensagem.className = 'confirm-message';
        mensagem.innerHTML = `<p>${texto}</p>`;
        
        // Adiciona ao contêiner de alunos
        containerAlunos.appendChild(mensagem);
        
        // Ativa a animação (fade-in) no próximo ciclo
        requestAnimationFrame(() => {
            mensagem.style.opacity = '1';
        });

        // Remove a mensagem após 3 segundos (fade-out e remoção)
        setTimeout(() => {
            mensagem.style.opacity = '0';
            setTimeout(() => {
                mensagem.remove();
            }, 500); // Tempo para fade-out completar
        }, 3000);
    }
});