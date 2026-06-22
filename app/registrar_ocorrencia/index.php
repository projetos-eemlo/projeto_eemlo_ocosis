<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Registrar Nova Ocorrência</title>
    </head>
<body>

    <div id="parte-superior-miguel">
        <h2>2. Registrar Nova Ocorrência</h2>
        <p><i>[O Miguel vai inserir os Dados do Aluno e Cabeçalho da Ocorrência aqui]</i></p>
    </div>

    <hr>

    <form id="form-ocorrencia">
        
        <div id="secao-infracoes">
            <label>Tipo(s) de Infração (Selecione um ou mais):</label>
            
            <div id="lista-infracoes">
                <label><input type="checkbox" name="infracao" value="Atraso"> Atraso</label>
                <label><input type="checkbox" name="infracao" value="Uso de celular"> Uso de celular</label>
                <label><input type="checkbox" name="infracao" value="Desrespeito"> Desrespeito</label>
                <label><input type="checkbox" name="infracao" value="Agressão física"> Agressão física</label>
                <label><input type="checkbox" name="infracao" value="Uniforme incorreto"> Uniforme incorreto</label>
                <label><input type="checkbox" name="infracao" value="Fuga da sala"> Fuga da sala</label>
                <label><input type="checkbox" name="infracao" value="Outros"> Outros</label>
            </div>

            <div class="adicionar-infracao-customizada">
                <input type="text" id="nova-infracao-texto" placeholder="Outro tipo de infração...">
                <button type="button" id="btn-adicionar-infracao">Adicionar à Lista</button>
            </div>
        </div>

        <br>

        <div id="secao-descricao">
            <label for="descricao-detalhada">Descrição Detalhada do Fato:</label><br>
            <textarea id="descricao-detalhada" rows="5" cols="50" placeholder="Descreva o que aconteceu..." required></textarea>
        </div>

        <br>

        <button type="button" id="btn-salvar-ocorrencia">Salvar Ocorrência</button>

    </form>

    <script>
        
        document.getElementById('btn-adicionar-infracao').addEventListener('click', function() {
            const inputNovaInfracao = document.getElementById('nova-infracao-texto');
            const valorNovaInfracao = inputNovaInfracao.value.trim();

            if (valorNovaInfracao !== "") {
                const novoLabel = document.createElement('label');
                const novoCheckbox = document.createElement('input');
                
                novoCheckbox.type = 'checkbox';
                novoCheckbox.name = 'infracao';
                novoCheckbox.value = valorNovaInfracao;
                novoCheckbox.checked = true;

                novoLabel.appendChild(novoCheckbox);
                novoLabel.appendChild(document.createTextNode(' ' + valorNovaInfracao));

                document.getElementById('lista-infracoes').appendChild(novoLabel);
                inputNovaInfracao.value = '';
            }
        });

        
        document.getElementById('btn-salvar-ocorrencia').addEventListener('click', function() {
            const checkboxesInfracao = document.querySelectorAll('input[name="infracao"]:checked');
            const descricao = document.getElementById('descricao-detalhada').value.trim();
            
            if (checkboxesInfracao.length === 0) {
                alert("Erro: Selecione pelo menos um Tipo de Infração.");
                return;
            }

            if (descricao === "") {
                alert("Erro: A Descrição Detalhada do Fato é obrigatória.");
                document.getElementById('descricao-detalhada').style.borderColor = "red";
                return;
            } else {
                document.getElementById('descricao-detalhada').style.borderColor = "";
            }

            const infracoesSelecionadas = Array.from(checkboxesInfracao).map(cb => cb.value);
            console.log("Infrações:", infracoesSelecionadas);
            console.log("Descrição:", descricao);
            alert("Ocorrência salva com sucesso! (Falta integrar com o Backend)"); 
        });
    </script>
</body>
</html>