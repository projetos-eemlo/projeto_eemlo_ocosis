document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");

    form.addEventListener("submit", (event) => {
        // 1. Pegando os valores dos campos
        const nome = document.getElementById("nome").value;
        const senha = document.getElementById("senha").value;

        // 2. Validações simples
        if (nome.length < 3) {
            alert("O nome deve ter pelo menos 3 caracteres.");
            event.preventDefault(); // Impede o envio
            return;
        }

        if (senha.length < 8) {
            alert("Sua senha é muito curta. Use pelo menos 8 caracteres.");
            event.preventDefault(); // Impede o envio
            return;
        }

        // Se passar por aqui, o formulário segue o curso normal
        console.log("Cadastro realizado com sucesso para: " + nome);

        
    });
});