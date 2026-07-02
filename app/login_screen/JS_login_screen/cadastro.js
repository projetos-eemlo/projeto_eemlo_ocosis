document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("FormCadastro");
    const maspInput = document.getElementById("masp");
    const passwordInput = document.getElementById("senha"); 
    const cargoSelect = document.getElementById("cargo");
    const btnCadastrar = document.querySelector(".btn-cadastrar");

    // ==========================================
    // MOSTRAR/OCULTAR SENHA
    // ==========================================
    const togglePasswordBtn = document.querySelector('.toggle-password');
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', () => {
            const isPasswordVisible = passwordInput.type === 'text';
            passwordInput.type = isPasswordVisible ? 'password' : 'text';
            togglePasswordBtn.textContent = isPasswordVisible ? 'Mostrar' : 'Ocultar';
        });
    }

    // ==========================================
    // MÁSCARA DO MASP (9999999-9)
    // ==========================================
    if (maspInput) {
        maspInput.addEventListener("input", function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 7) {
                value = value.replace(/^(\d{7})(\d)/, '$1-$2');
            }
            e.target.value = value;
            validarFormularioPreenchido();
        });
    }

    if (form) {
        form.addEventListener("submit", (event) => {
            const masp = maspInput ? maspInput.value.trim() : '';
            const senha = passwordInput ? passwordInput.value : '';

            if (masp.length !== 9) {
                alert("O MASP deve estar completo (ex: 1234567-8).");
                event.preventDefault(); 
                return;
            }

            if (senha.length < 8) {
                alert("A sua senha é muito curta. Use pelo menos 8 caracteres.");
                event.preventDefault(); 
                return;
            }
        });
    }

    function validarFormularioPreenchido() {
        if (btnCadastrar && maspInput && passwordInput && cargoSelect) {
            if (maspInput.value.trim().length === 9 && passwordInput.value.trim() !== "" && cargoSelect.value !== "") {
                btnCadastrar.classList.remove("bloqueado");
                btnCadastrar.classList.add("ativo"); 
            } else {
                btnCadastrar.classList.remove("ativo");
                btnCadastrar.classList.add("bloqueado"); 
            }
        }
    }
    
    validarFormularioPreenchido();
   
    if(passwordInput) passwordInput.addEventListener("input", validarFormularioPreenchido);
    if(cargoSelect) cargoSelect.addEventListener("change", validarFormularioPreenchido);
});