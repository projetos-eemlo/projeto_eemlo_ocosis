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
        const turno = document.getElementById('turno').value;
        const capacidade = document.getElementById('capacidade').value;

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

        // CA05 e RN08 - Prevenção de Duplicidade
        const duplicada = turmasBanco.find(t => 
            t.desc_turma.toLowerCase() === descTurma.toLowerCase() && 
            t.ano_letivo === anoLetivo
        );

        if (duplicada) {
            alert(`Aviso: A turma "${descTurma}" já está cadastrada para o ano de ${anoLetivo}!`);
            return;
        }

        // CA04 - Cadastro com Dados Válidos (Sucesso)
        turmasBanco.push({ desc_turma: descTurma, ano_letivo: anoLetivo, turno: turno });
        
        alert("Turma registrada com sucesso!");
        
        form.reset();
        modal.style.display = "none";
    });
});