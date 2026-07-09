document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const maspInput = document.getElementById('masp');
    const passwordInput = document.getElementById('password'); 
    const errorMessage = document.getElementById('error-msg');

    // ==========================================
    // MOSTRAR/OCULTAR SENHA
    // ==========================================
    const toggleButton = document.querySelector('.btn-show-password');
    if (toggleButton && passwordInput) {
        toggleButton.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = 'Ocultar';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = 'Mostrar';
            }
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
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('erro')) {
        if (errorMessage) {
            errorMessage.style.display = 'block';
            errorMessage.textContent = 'MASP ou senha incorretos.';
        }
    }

    if (form) {
        form.addEventListener('submit', function(event) {
            const maspDigitado = maspInput ? maspInput.value.trim() : '';
            const senhaDigitada = passwordInput ? passwordInput.value : '';

            if (maspDigitado === "" || senhaDigitada === "") {
                event.preventDefault(); 
                if (errorMessage) {
                    errorMessage.textContent = "Preencha todos os campos.";
                    errorMessage.style.display = 'block';
                }
            }
        });
    }
});