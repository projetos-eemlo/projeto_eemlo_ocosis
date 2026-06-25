
const USUARIO_CORRETO = "admin@teste.com";
const SENHA_CORRETA = "1234";


document.addEventListener('DOMContentLoaded', function() {
    
    const form = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const senhaInput = document.getElementById('senha');
    const mensagemErro = document.getElementById('mensagem-erro');

   
    if (!form || !emailInput || !senhaInput || !mensagemErro) {
        console.error("Erro: Um ou mais elementos do HTML não foram encontrados pelos IDs.");
        return;
    }

    
    form.addEventListener('submit', function(event) {
        event.preventDefault(); 

        const usuarioDigitado = emailInput.value.trim();
        const senhaDigitada = senhaInput.value;

        
        if (usuarioDigitado === USUARIO_CORRETO && senhaDigitada === SENHA_CORRETA) {
            mensagemErro.style.display = 'none';
            alert('Login efetuado com sucesso!');
           
        } else {
           
            mensagemErro.textContent = "Usuário ou senha incorretos";
            mensagemErro.style.display = 'block';
        }
    });
});
        const togglePasswordBtn = document.querySelector('.toggle-password');
        const passwordInput = document.querySelector('#password');
        if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener('click', () => {
                const isPasswordVisible = passwordInput.type === 'text';
                passwordInput.type = isPasswordVisible ? 'password' : 'text';
                togglePasswordBtn.textContent = isPasswordVisible ? 'Mostrar' : 'Ocultar';
            });
        }
    
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.btn-show-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = 'Ocultar';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = 'Mostrar';
            }
        }

        document.getElementById('login-form').addEventListener('submit', function(event) {
            event.preventDefault();

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('login-error');

            const validEmail = 'usuario@exemplo.com';
            const validPassword = 'senha123';

            if (email !== validEmail || password !== validPassword) {
                errorMessage.textContent = 'Gmail ou senha incorreta';
                return;
            }

            errorMessage.textContent = '';
            alert('Login realizado com sucesso!');
        });
