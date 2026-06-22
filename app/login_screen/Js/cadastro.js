document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");

    form.addEventListener("submit", (event) => {
        // Aqui pega os valores dos campos 
        const nome = document.getElementById("nome").value;
        const senha = document.getElementById("senha").value;

        //  Validações 
        if (nome.length < 3) {
            alert("O nome deve ter pelo menos 3 caracteres.");
            event.preventDefault(); 
            return;
        }

        if (senha.length < 8) {
            alert("Sua senha é muito curta. Use pelo menos 8 caracteres.");
            event.preventDefault(); 
            return;
        }

        
        console.log("Cadastro realizado com sucesso para: " + nome);

        
    });
    
});
 