document.addEventListener('DOMContentLoaded', () => {
    
    // --- Lógica de Buscar Turma (Caixa Cinza) Integrada com o Banco ---
    const btnBuscarTurmaFiltros = document.getElementById('btnBuscarTurmaFiltros');
    const listaAlunos = document.getElementById('listaAlunos');
    
    btnBuscarTurmaFiltros.addEventListener('click', () => {
        const turma = document.getElementById('filtroSelectTurma').value;
        
        // Coloca uma mensagem de carregamento enquanto o PHP busca
        listaAlunos.innerHTML = '<li>Carregando alunos...</li>';

        // Faz a requisição GET passando a turma na URL
        fetch(`buscar_alunos.php?turma=${encodeURIComponent(turma)}`)
        .then(response => response.json())
        .then(data => {
            listaAlunos.innerHTML = ''; // Limpa o "carregando"

            if (data.sucesso) {
                if (data.dados.length > 0) {
                    // Percorre o array de alunos que veio do banco e cria os <li>
                    data.dados.forEach(aluno => {
                        const li = document.createElement('li');
                        li.innerHTML = `<a href="#" class="aluno_link">➔ ${aluno.nome_aluno}</a>`;
                        listaAlunos.appendChild(li);
                    });

                    // Chama a função para garantir que os alunos novos fiquem clicáveis
                    reatribuirCliquesAlunos();
                } else {
                    listaAlunos.innerHTML = '<li>Nenhum aluno encontrado nesta turma.</li>';
                }
            } else {
                alert("Erro: " + data.mensagem);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            listaAlunos.innerHTML = '<li>Erro ao comunicar com o servidor.</li>';
        });
    });

    // --- Lógica de Selecionar Aluno na Lista ---
    // Transformada em função para ser chamada após carregar os alunos do banco
    function reatribuirCliquesAlunos() {
        const alunosLista = document.querySelectorAll('.aluno_link');
        alunosLista.forEach(aluno => {
            aluno.addEventListener('click', (e) => {
                e.preventDefault();
                alunosLista.forEach(a => a.classList.remove('ativo'));
                aluno.classList.add('ativo');
            });
        });
    }

    // Chama a função uma vez no início para os alunos que já vierem no HTML
    reatribuirCliquesAlunos();

    // --- Lógica de Pesquisa de Aluno (Acima da Lista) ---
    const inputPesquisaAluno = document.getElementById('inputPesquisaAluno');
    const btnBuscarAluno = document.getElementById('btnBuscarAluno');

    function filtrarAlunos() {
        const termoBusca = inputPesquisaAluno.value.toLowerCase();
        // Precisa buscar os itens de lista atualizados caso tenham mudado
        const itensListaAlunos = document.querySelectorAll('#listaAlunos li');
        
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

    // Validar e Salvar Formulário com Integração ao Banco de Dados
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

        // Faz a requisição para o PHP
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