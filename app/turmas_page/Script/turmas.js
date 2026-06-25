// Mock de banco de dados para testar duplicidade (CA05 e RN08)
let turmasCadastradas = [
    { nome: "1° Ano A", anoLetivo: "2026", turno: "Manha" }
];

document.addEventListener('DOMContentLoaded', () => {
    // ---------------------------------------------------------
    // 1. INTERATIVIDADE DA TELA (Layout HU0X)
    // ---------------------------------------------------------
    const alunos = document.querySelectorAll('.aluno_link');

    alunos.forEach(aluno => {
        aluno.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Remove o destaque visual de todos os alunos
            alunos.forEach(a => a.classList.remove('ativo'));
            
            // Aplica o destaque no aluno clicado
            aluno.classList.add('ativo');
        });
    });

    // ---------------------------------------------------------
    // 2. REGRAS DE NEGÓCIO DA SPRINT (RFU-006 / Modal de Cadastro)
    // ---------------------------------------------------------
    
    // Captura o formulário do modal (quando for adicionado ao HTML)
    const formTurma = document.getElementById("formTurma");
    
    if (formTurma) {
        formTurma.addEventListener('submit', function(e) {
            e.preventDefault();

            // Captura dos valores do formulário
            const nomeTurma = document.getElementById('nomeTurma').value.trim();
            const anoLetivo = document.getElementById('anoLetivo').value.trim();
            const turno = document.getElementById('turnoTurma').value;
            const capacidade = parseInt(document.getElementById('capacidade').value);
            const tipo = document.getElementById('tipoTurma').value;
            
            // RN05 - Aviso de erro do preenchimento (Mensagem exata da sprint)
            if (!nomeTurma) {
                alert("Campo Nome da Turma Preenchido incorretamente, conferira as informações devidamente");
                return;
            }
            if (!anoLetivo || anoLetivo.length !== 4) {
                alert("Campo Ano Letivo Preenchido incorretamente, conferira as informações devidamente");
                return;
            }
            
            // RN07 - Limite de Capacidade
            if (!capacidade || capacidade <= 0) {
                alert("Campo Capacidade Preenchido incorretamente, conferira as informações devidamente");
                return;
            }

            // CA05 e RN08 - Prevenção de Duplicidade (Nome da turma no mesmo ano letivo)
            const turmaDuplicada = turmasCadastradas.find(t => 
                t.nome.toLowerCase() === nomeTurma.toLowerCase() && 
                t.anoLetivo === anoLetivo
            );

            if (turmaDuplicada) {
                alert("Erro: Esta turma já está cadastrada para este ano letivo!");
                return;
            }

            // RN10 - Formato do Código da Turma (ex: ANO-TURMA-TURNO)
            // Remove espaços do nome da turma para gerar o código padrão
            const nomeFormatado = nomeTurma.replace(/\s+/g, '').toUpperCase();
            const turnoFormatado = turno.charAt(0).toUpperCase();
            const codigoTurma = `${anoLetivo}-${nomeFormatado}-${turnoFormatado}`;

            // CA04 - Cadastro de Turma com Dados Válidos
            const novaTurma = {
                codigo: codigoTurma,
                nome: nomeTurma,
                anoLetivo: anoLetivo,
                turno: turno,
                capacidade: capacidade,
                tipo: tipo,
                status: "Ativa" // RN11 - Status inicial padrão
            };

            turmasCadastradas.push(novaTurma);
            
            // Mensagem de sucesso
            alert(`Turma registrada com sucesso! Código gerado: ${codigoTurma}`);
            
            // Limpa o formulário após o cadastro
            formTurma.reset();
        });
    }
});