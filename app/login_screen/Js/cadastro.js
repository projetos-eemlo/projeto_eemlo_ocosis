document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const nomeInput = document.getElementById("nome");
    const passwordInput = document.getElementById("senha"); 
    const cargoSelect = document.getElementById("cargo");
    const btnCadastrar = document.querySelector(".btn-cadastrar");

    
    form.addEventListener("submit", (event) => {
        const nome = nomeInput.value;
        const senha = passwordInput.value;

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

    
    const togglePasswordBtn = document.querySelector('.toggle-password');
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', () => {
            const isPasswordVisible = passwordInput.type === 'text';
            passwordInput.type = isPasswordVisible ? 'password' : 'text';
            togglePasswordBtn.textContent = isPasswordVisible ? 'Mostrar' : 'Ocultar';
        });
    }

    // === CÓDIGO DO BOTÃO QUE MUDA A COR (RN04) ===
    function validarFormularioPreenchido() {
        // Se os 3 campos tiverem valor preenchido
        if (nomeInput.value.trim() !== "" && passwordInput.value.trim() !== "" && cargoSelect.value !== "") {
            btnCadastrar.classList.remove("bloqueado");
            btnCadastrar.classList.add("ativo"); // Altera a cor do botão para cor verde
        } else {
            btnCadastrar.classList.remove("ativo");
            btnCadastrar.classList.add("bloqueado"); // Altera a cor do botão para cor cinza
        }
    }

    
    validarFormularioPreenchido();

   
    nomeInput.addEventListener("input", validarFormularioPreenchido);
    passwordInput.addEventListener("input", validarFormularioPreenchido);
    cargoSelect.addEventListener("change", validarFormularioPreenchido);
});