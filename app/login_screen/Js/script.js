
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