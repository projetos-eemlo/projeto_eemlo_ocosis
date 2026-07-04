document.addEventListener('DOMContentLoaded', function() {
    
    const btnEditar = document.getElementById('btn-editar');
    const btnUpload = document.getElementById('btn-upload');
    const btnCadastrar = document.getElementById('btn-cadastrar');
    const btnExcluir = document.getElementById('btn-excluir');
    
    const containerProfessores = document.getElementById('container-professores');
    const checkboxTodos = document.getElementById('professores-todos');
    const badgeCsv = document.getElementById('csv-info-badge');
    
    const modalCadastrarConfirmacao = document.getElementById('modal-cadastrar-confirmacao');
    const btnCancelarCadastro = document.getElementById('btn-cancelar-cadastro');
    const btnConfirmarCadastro = document.getElementById('btn-confirmar-cadastro');
    const listaProfessoresCadastro = document.getElementById('lista-professores-cadastro');

    const modalExcluirConfirmacao = document.getElementById('modal-excluir-confirmacao');
    const btnCancelarExcluir = document.getElementById('btn-cancelar-excluir');
    const btnConfirmarExcluir = document.getElementById('btn-confirmar-excluir');
    const textoExcluirConfirmacao = document.getElementById('texto-excluir-confirmacao');

    let checkboxesProfessores = []; 
    let isModoCSV = false;
    let countdownExcluir; 

    function atualizarEstadoBotoes() {
        const algumMarcado = document.querySelectorAll('.check-professor:checked').length > 0;

        if (btnCadastrar) btnCadastrar.disabled = !(algumMarcado && isModoCSV);
        if (btnExcluir) btnExcluir.disabled = !algumMarcado;
    }

    containerProfessores.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('check-professor')) {
            atualizarEstadoBotoes();
        }
    });

    // Puxar os professores que já estão no banco assim que a página carrega
    async function carregarProfessoresDoBanco() {
        const formData = new FormData();
        formData.append('acao', 'listar_professores');

        try {
            const response = await fetch('cadastrar_professores.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.sucesso) {
                isModoCSV = false;
                if (badgeCsv) badgeCsv.style.display = 'none';
                renderizarProfessores(result.dados, false);
            } else {
                exibirMensagem('❌ ' + result.erro);
            }
        } catch (error) {
            exibirMensagem('❌ Erro de comunicação com o servidor PHP.');
        }
    }

    carregarProfessoresDoBanco();

    // ==========================================
    // MODO EDIÇÃO E SELEÇÃO
    // ==========================================
    if (btnEditar) {
        btnEditar.addEventListener('click', () => {
            if (containerProfessores.classList.contains('modo-edicao')) {
                limparTelaECancelar(false); 
            } else {
                const itensNaTela = containerProfessores.querySelectorAll('.professor-item').length;
                if (itensNaTela > 0) {
                    containerProfessores.classList.add('modo-edicao');
                    btnEditar.textContent = 'Salvar'; 
                    btnEditar.style.backgroundColor = 'var(--color-danger)'; 
                    btnEditar.style.color = 'white';
                }
            }
        });
    }

    if (checkboxTodos) {
        checkboxTodos.addEventListener('change', function(e) {
            const isChecked = e.target.checked;
            checkboxesProfessores.forEach(cb => cb.checked = isChecked);
            atualizarEstadoBotoes();
        });
    }

    function limparTelaECancelar(forcarLimpezaTotal = false) {
        containerProfessores.classList.remove('modo-edicao');
        btnEditar.textContent = 'Editar';
        btnEditar.style.backgroundColor = '#ffffff'; 
        btnEditar.style.color = 'var(--color-primary)';
        
        if (checkboxTodos) checkboxTodos.checked = false;
        checkboxesProfessores.forEach(cb => cb.checked = false); 
        
        if (forcarLimpezaTotal) {
            carregarProfessoresDoBanco();
        } else if (isModoCSV && !document.querySelectorAll('.professor-item').length) {
            carregarProfessoresDoBanco();
        }
        
        atualizarEstadoBotoes();
    }

    // ==========================================
    // UPLOAD CSV
    // ==========================================
    const inputFile = document.createElement('input');
    inputFile.type = 'file';
    inputFile.accept = '.csv';
    inputFile.style.display = 'none';
    document.body.appendChild(inputFile);

    if (btnUpload) btnUpload.addEventListener('click', () => inputFile.click());

    inputFile.addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('acao', 'upload_csv');
        formData.append('arquivo_csv', file);

        exibirMensagem('A processar ficheiro CSV...', false);

        try {
            const response = await fetch('cadastrar_professores.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.sucesso) {
                isModoCSV = true; 
                if (badgeCsv) badgeCsv.style.display = 'inline-block';
                renderizarProfessores(result.dados, true); 
                exibirMensagem('✅ Professores extraídos do CSV. Selecione e guarde.');
            } else {
                exibirMensagem('❌ ' + result.erro);
            }
        } catch (error) {
            exibirMensagem('❌ Erro ao conectar com o PHP.');
        }
        this.value = ''; 
    });

    // Coloca o tracinho antes do último número visualmente
    function formatarMaspParaTela(masp) {
        let value = masp.replace(/\D/g, '');
        if (value.length > 7) {
            value = value.replace(/^(\d{7})(\d)/, '$1-$2');
        }
        return value;
    }

    function renderizarProfessores(professores, forcarEdicao) {
        const itensAntigos = containerProfessores.querySelectorAll('.professor-item');
        itensAntigos.forEach(item => item.remove());

        professores.forEach(prof => {
            const div = document.createElement('div');
            div.className = 'professor-item';
            const maspFormatado = formatarMaspParaTela(prof.masp);
            
            div.innerHTML = `
                <input type="checkbox" name="professor" class="check-professor" 
                       value="${prof.masp}" 
                       data-nome="${prof.nome}">
                <span><strong>${prof.nome}</strong> — MASP: ${maspFormatado}</span>
            `;
            containerProfessores.appendChild(div);
        });

        checkboxesProfessores = document.querySelectorAll('.check-professor');
        
        if (forcarEdicao) {
            containerProfessores.classList.add('modo-edicao');
            btnEditar.textContent = 'Salvar'; 
            btnEditar.style.backgroundColor = 'var(--color-danger)'; 
            btnEditar.style.color = 'white';
        } else {
            containerProfessores.classList.remove('modo-edicao');
            btnEditar.textContent = 'Editar';
            btnEditar.style.backgroundColor = '#ffffff'; 
            btnEditar.style.color = 'var(--color-primary)';
            if (checkboxTodos) checkboxTodos.checked = false;
        }

        atualizarEstadoBotoes();
    }

    // ==========================================
    // CADASTRAR (MANDA DO ECRÃ PARA O BANCO)
    // ==========================================
    if (btnCadastrar) {
        btnCadastrar.addEventListener('click', function() {
            listaProfessoresCadastro.innerHTML = '';
            let algumMarcado = false;

            checkboxesProfessores.forEach(cb => {
                if (cb.checked) {
                    algumMarcado = true;
                    const nome = cb.getAttribute('data-nome');
                    const masp = formatarMaspParaTela(cb.value);
                    listaProfessoresCadastro.innerHTML += `<div><strong>${nome}</strong> <br><small style="color: #666;">MASP: ${masp}</small></div>`;
                }
            });

            if (!algumMarcado) return; 

            modalCadastrarConfirmacao.classList.remove('hidden');
        });
    }

    if (btnCancelarCadastro) {
        btnCancelarCadastro.addEventListener('click', () => {
            modalCadastrarConfirmacao.classList.add('hidden');
        });
    }

    if (btnConfirmarCadastro) {
        btnConfirmarCadastro.addEventListener('click', async function() {
            const profsParaSalvar = [];
            
            checkboxesProfessores.forEach(cb => {
                if (cb.checked) {
                    profsParaSalvar.push({
                        masp: cb.value, // Envia sem traço para o banco
                        nome: cb.getAttribute('data-nome')
                    });
                }
            });

            modalCadastrarConfirmacao.classList.add('hidden');
            exibirMensagem('A guardar na base de dados...', false); 
            
            const formData = new FormData();
            formData.append('acao', 'salvar_professores_csv');
            formData.append('professores', JSON.stringify(profsParaSalvar)); 

            try {
                const response = await fetch('cadastrar_professores.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.sucesso) {
                    exibirMensagem('✅ ' + result.mensagem); 
                    
                    checkboxesProfessores.forEach(cb => {
                        if (cb.checked) {
                            const item = cb.parentElement;
                            item.classList.add('salvo-sucesso'); 
                            setTimeout(() => item.remove(), 700);
                        }
                    });

                    setTimeout(() => { limparTelaECancelar(true); }, 750);

                } else {
                    exibirMensagem('❌ ' + result.erro); 
                }
            } catch (error) {
                exibirMensagem('❌ Erro de comunicação ao guardar.'); 
            }
        });
    }

    // ==========================================
    // EXCLUIR PROFESSORES (COM TIMER)
    // ==========================================
    if (btnExcluir) {
        btnExcluir.addEventListener('click', function() {
            const marcados = document.querySelectorAll('.check-professor:checked');
            if (marcados.length === 0) return;

            if (isModoCSV) {
                marcados.forEach(cb => cb.parentElement.remove());
                
                checkboxesProfessores = document.querySelectorAll('.check-professor');
                if (checkboxTodos) checkboxTodos.checked = false;
                atualizarEstadoBotoes();
                
                if (checkboxesProfessores.length === 0) limparTelaECancelar(true);
                
                exibirMensagem('✅ Professor(es) removido(s) da lista com sucesso.');
            } else {
                let tempoRestante = 5;
                btnConfirmarExcluir.disabled = true;
                btnConfirmarExcluir.textContent = `Confirmar (${tempoRestante}s)`;
                
                if (marcados.length > 1) {
                    textoExcluirConfirmacao.textContent = `Tem a certeza que deseja excluir estes ${marcados.length} professores do banco de dados?`;
                } else {
                    textoExcluirConfirmacao.textContent = "Tem a certeza que deseja excluir este professor do banco de dados?";
                }

                modalExcluirConfirmacao.classList.remove('hidden');

                countdownExcluir = setInterval(() => {
                    tempoRestante--;
                    if (tempoRestante > 0) {
                        btnConfirmarExcluir.textContent = `Confirmar (${tempoRestante}s)`;
                    } else {
                        clearInterval(countdownExcluir);
                        btnConfirmarExcluir.textContent = "Confirmar";
                        btnConfirmarExcluir.disabled = false;
                    }
                }, 1000);
            }
        });
    }

    if (btnCancelarExcluir) {
        btnCancelarExcluir.addEventListener('click', () => {
            clearInterval(countdownExcluir); 
            modalExcluirConfirmacao.classList.add('hidden');
        });
    }

    if (btnConfirmarExcluir) {
        btnConfirmarExcluir.addEventListener('click', async () => {
            const marcados = document.querySelectorAll('.check-professor:checked');
            const maspsArray = [];

            marcados.forEach(cb => maspsArray.push(cb.value));

            modalExcluirConfirmacao.classList.add('hidden');
            exibirMensagem('A apagar do banco de dados...', false);

            const formData = new FormData();
            formData.append('acao', 'excluir_professores');
            formData.append('masps', JSON.stringify(maspsArray));

            try {
                const response = await fetch('cadastrar_professores.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.sucesso) {
                    exibirMensagem('✅ ' + result.mensagem);
                    
                    marcados.forEach(cb => {
                        const item = cb.parentElement;
                        item.style.backgroundColor = 'var(--color-danger)';
                        item.style.transition = 'all 0.5s ease';
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.9) translateX(-30px)';
                        setTimeout(() => item.remove(), 500);
                    });

                    setTimeout(() => {
                        containerProfessores.classList.remove('modo-edicao');
                        btnEditar.textContent = 'Editar';
                        btnEditar.style.backgroundColor = '#ffffff'; 
                        btnEditar.style.color = 'var(--color-primary)';
                        if (checkboxTodos) checkboxTodos.checked = false;

                        checkboxesProfessores = document.querySelectorAll('.check-professor');
                        atualizarEstadoBotoes();

                        if (checkboxesProfessores.length === 0) limparTelaECancelar(true);
                    }, 550);

                } else {
                    exibirMensagem('❌ ' + result.erro);
                }
            } catch (error) {
                exibirMensagem('❌ Erro de comunicação ao excluir.');
            }
        });
    }

    function exibirMensagem(texto, autoApagar = true) {
        const mensagemExistente = document.querySelector('.confirm-message');
        if (mensagemExistente) mensagemExistente.remove();

        const mensagem = document.createElement('div');
        mensagem.className = 'confirm-message';
        mensagem.innerHTML = `<p>${texto}</p>`;
        document.body.appendChild(mensagem);
        
        requestAnimationFrame(() => mensagem.style.opacity = '1');

        if (autoApagar) {
            setTimeout(() => {
                mensagem.style.opacity = '0';
                setTimeout(() => mensagem.remove(), 500);
            }, 3000);
        }
    }
});