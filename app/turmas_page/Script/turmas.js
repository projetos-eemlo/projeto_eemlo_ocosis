// Simulando as turmas que já existem no banco (Para testar o CA05 - Duplicidade)
let turmasBanco = [
    { desc_turma: "3° Reg 1", ano_letivo: "2026", turno: "Manhã" }
];

document.addEventListener('DOMContentLoaded', () => {
    // --- Lógica de Buscar Turma (Caixa Cinza) ---
    const btnBuscarTurmaFiltros = document.getElementById('btnBuscarTurmaFiltros');
    
    btnBuscarTurmaFiltros.addEventListener('click', () => {
        const turma = document.getElementById('filtroSelectTurma').value;
        // Aqui entrará a requisição AJAX/Fetch para o PHP buscar os dados da turma
        alert(`Buscando dados para: ${turma}\n(Integração com backend pendente)`);
    });

    // --- Lógica de Selecionar Aluno na Lista ---
    const alunos = document.querySelectorAll('.aluno_link');
    alunos.forEach(aluno => {
        aluno.addEventListener('click', (e) => {
            e.preventDefault();
            alunos.forEach(a => a.classList.remove('ativo'));
            aluno.classList.add('ativo');
        });
    });

    // --- Lógica de Pesquisa de Aluno (Acima da Lista) ---
    const inputPesquisaAluno = document.getElementById('inputPesquisaAluno');
    const btnBuscarAluno = document.getElementById('btnBuscarAluno');
    const itensListaAlunos = document.querySelectorAll('#listaAlunos li');

    function filtrarAlunos() {
        const termoBusca = inputPesquisaAluno.value.toLowerCase();
        
        itensListaAlunos.forEach(li => {
            const textoAluno = li.textContent.toLowerCase();
            if (textoAluno.includes(termoBusca)) {
                li.style.display = ""; 
            } else {
                li.style.display = "none"; 
            }
        });
    }

    btnBuscarAluno.addEventListener('click', filtrarAlunos);
    inputPesquisaAluno.addEventListener('keyup', filtrarAlunos);

    // --- Lógica do Modal de Cadastro de Turma ---
    const modal = document.getElementById("modalTurma");
    const btnAbrir = document.getElementById("btnNovaTurma");
    const btnFechar = document.querySelector(".fechar_modal");
    const form = document.getElementById("formCadastroTurma");

    // Abrir o Modal
    btnAbrir.addEventListener('click', () => {
        modal.style.display = "block";
    });

    // Fechar no X
    btnFechar.addEventListener('click', () => {
        modal.style.display = "none";
    });

    // Fechar clicando no fundo escuro
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    // Validar e Salvar Formulário
    form.addEventListener('submit', (e) => {
        e.preventDefault(); 

        const descTurma = document.getElementById('descTurma').value.trim();
        const anoLetivo = document.getElementById('anoLetivo').value;
        const semestreLetivo = document.getElementById('semestreLetivo').value;
        const turno = document.getElementById('turno').value;
        const capacidade = document.getElementById('capacidade').value;
        const status = document.getElementById('status').value;

        // RN05 - Aviso de erro de preenchimento
        if (descTurma === "") {
            alert("Campo Nome / Descrição Preenchido incorretamente, confira as informações devidamente.");
            return;
        }

        // RN07 - Capacidade Máxima
        if (capacidade <= 0) {
            alert("Campo Capacidade Preenchido incorretamente, a capacidade deve ser maior que 0.");
            return;
        }

        // Prepara os dados para enviar ao PHP
        const dadosTurma = {
            descTurma: descTurma,
            anoLetivo: anoLetivo,
            semestreLetivo: semestreLetivo,
            turno: turno,
            capacidade: capacidade,
            status: status
        };

        // Faz a requisição para o PHP (agora na mesma pasta)
        fetch('cadastrar_turma.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dadosTurma)
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                alert(data.mensagem); 
                form.reset();
                modal.style.display = "none";
            } else {
                alert("Erro: " + data.mensagem); 
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            alert('Erro ao conectar com o servidor.');
        });
    });
});