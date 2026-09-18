// ====================================================================
// JAVASCRIPT PRINCIPAL - ZEBRA DE TOUCA
// ====================================================================
// Aqui fica todo o comportamento do nosso site:
// 1. Ferramentas de acessibilidade (tamanho da fonte e alto contraste)
// 2. Troca de fotos na galeria de produtos
// 3. Calculadora de orcamento chamando nossa nova API REST
// 4. Envio do pedido direto para o WhatsApp da artesã

document.addEventListener('DOMContentLoaded', () => {

    // -------------------------------------------------------------
    // 1. ACESSIBILIDADE: MODO ALTO CONTRASTE
    // -------------------------------------------------------------
    const btnContraste = document.getElementById('btn-alto-contraste');
    
    // Se a pessoa ja tinha ligado o alto contraste antes, liga de novo
    if (localStorage.getItem('modo-alto-contraste') === 'ativo') {
        document.body.classList.add('alto-contraste');
    }

    if (btnContraste) {
        btnContraste.addEventListener('click', () => {
            // Liga ou desliga a classe no corpo da pagina
            document.body.classList.toggle('alto-contraste');

            // Salva o que a pessoa escolheu pra lembrar na proxima pagina
            const ativo = document.body.classList.contains('alto-contraste');
            localStorage.setItem('modo-alto-contraste', ativo ? 'ativo' : 'inativo');
        });
    }

    // -------------------------------------------------------------
    // 2. ACESSIBILIDADE: TAMANHO DO TEXTO (A+ / A-)
    // -------------------------------------------------------------
    const btnAumentar = document.getElementById('btn-aumentar-fonte');
    const btnDiminuir = document.getElementById('btn-diminuir-fonte');
    const btnResetar  = document.getElementById('btn-resetar-fonte');

    let tamanhoFonte = parseInt(localStorage.getItem('tamanho-fonte')) || 16;

    function aplicarFonte(tamanho) {
        // Nao deixa a fonte ficar nem muito pequena nem gigante a ponto de quebrar a tela
        if (tamanho >= 14 && tamanho <= 22) {
            tamanhoFonte = tamanho;
            document.documentElement.style.fontSize = tamanhoFonte + 'px';
            localStorage.setItem('tamanho-fonte', tamanhoFonte);
        }
    }

    if (tamanhoFonte !== 16) {
        aplicarFonte(tamanhoFonte);
    }

    if (btnAumentar) {
        btnAumentar.addEventListener('click', () => aplicarFonte(tamanhoFonte + 2));
    }
    if (btnDiminuir) {
        btnDiminuir.addEventListener('click', () => aplicarFonte(tamanhoFonte - 2));
    }
    if (btnResetar) {
        btnResetar.addEventListener('click', () => aplicarFonte(16));
    }

    // -------------------------------------------------------------
    // 3. GALERIA DE FOTOS: TROCAR A IMAGEM AO CLICAR NA MINIATURA
    // -------------------------------------------------------------
    const fotoGrande = document.getElementById('foto-grande-destaque');
    const miniaturas = document.querySelectorAll('.miniatura-item');

    if (fotoGrande && miniaturas.length > 0) {
        miniaturas.forEach(miniatura => {
            miniatura.addEventListener('click', () => {
                // Tira a marquinha de selecionado de todas as miniaturas
                miniaturas.forEach(m => m.classList.remove('ativa'));
                // Marca a miniatura clicada
                miniatura.classList.add('ativa');

                const novaUrl = miniatura.getAttribute('data-url');
                const novoAlt = miniatura.getAttribute('data-alt');

                // Faz um efeito bem suave de transicao apagando e acendendo a foto
                fotoGrande.style.opacity = '0.3';
                setTimeout(() => {
                    fotoGrande.src = novaUrl;
                    fotoGrande.alt = novoAlt;
                    fotoGrande.style.opacity = '1';
                }, 150);
            });
        });
    }

    // -------------------------------------------------------------
    // 4. CALCULADORA DE ORÇAMENTO (COMUNICANDO COM A API REST)
    // -------------------------------------------------------------
    const cardCalculadora = document.querySelector('.card-calculadora');

    if (cardCalculadora) {
        const inputQtd = document.getElementById('input-quantidade');
        const btnMais = document.getElementById('btn-mais-qtd');
        const btnMenos = document.getElementById('btn-menos-qtd');
        const checkboxes = document.querySelectorAll('.checkbox-opcional');
        const inputPersonalizacao = document.getElementById('input-personalizacao');
        const totalExibicao = document.getElementById('total-orcamento-exibicao');
        const btnWhatsapp = document.getElementById('btn-enviar-whatsapp');

        // Pega o ID e o preco base do produto direto dos dados colocados no HTML
        const produtoId = parseInt(cardCalculadora.dataset.produtoId) || 0;
        const precoBase = parseFloat(cardCalculadora.dataset.precoBase) || 0;

        // Funcao que descobre quais checkboxes estao marcados
        function getAcabamentosSelecionados() {
            const selecionados = [];
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    // Pega o id do checkbox (ex: opt-lamicote)
                    selecionados.push(cb.id);
                }
            });
            return selecionados;
        }

        // Faz a chamada na nossa API para calcular o preco exato
        let timeoutCalculo = null;
        async function chamarApiSimulacao() {
            let quantidade = parseInt(inputQtd.value) || 1;
            if (quantidade < 1) {
                quantidade = 1;
                inputQtd.value = 1;
            }

            const acabamentos = getAcabamentosSelecionados();

            try {
                // Tenta chamar a rota da API do Slim
                const resposta = await fetch('public/index.php/api/simular_orcamento', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        produtoId: produtoId,
                        precoBase: precoBase,
                        quantidade: quantidade,
                        acabamentos: acabamentos
                    })
                }).catch(() => {
                    // Se falhar a rota public/..., tenta rota direta /api/... (caso o servidor ja aponte pra public)
                    return fetch('/api/simular_orcamento', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            produtoId: produtoId,
                            precoBase: precoBase,
                            quantidade: quantidade,
                            acabamentos: acabamentos
                        })
                    });
                });

                if (resposta && resposta.ok) {
                    const dados = await resposta.json();
                    if (dados.sucesso) {
                        // Atualiza o valor formatado bonitinho na tela (R$ 00,00)
                        totalExibicao.textContent = 'R$ ' + dados.total.toLocaleString('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        cardCalculadora.dataset.ultimoTotal = dados.total;
                        return;
                    }
                }
            } catch (e) {
                // Se der algum erro de rede ou o servidor estiver fora,
                // a gente faz o calculo basico local pra nao deixar o cliente na mao!
                let extras = 0;
                checkboxes.forEach(cb => {
                    if (cb.checked) extras += parseFloat(cb.dataset.preco) || 0;
                });
                const totalFallback = (precoBase + extras) * quantidade;
                totalExibicao.textContent = 'R$ ' + totalFallback.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                cardCalculadora.dataset.ultimoTotal = totalFallback;
            }
        }

        // Adiciona um pequeno delay de 100ms pra nao disparar chamadas a cada letrinha digitada
        function agendarRecalculo() {
            clearTimeout(timeoutCalculo);
            timeoutCalculo = setTimeout(chamarApiSimulacao, 80);
        }

        // Botoes de aumentar e diminuir quantidade
        if (btnMais) {
            btnMais.addEventListener('click', () => {
                inputQtd.value = (parseInt(inputQtd.value) || 1) + 1;
                agendarRecalculo();
            });
        }

        if (btnMenos) {
            btnMenos.addEventListener('click', () => {
                let valor = parseInt(inputQtd.value) || 1;
                if (valor > 1) {
                    inputQtd.value = valor - 1;
                    agendarRecalculo();
                }
            });
        }

        if (inputQtd) {
            inputQtd.addEventListener('input', agendarRecalculo);
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', agendarRecalculo);
        });

        // ---------------------------------------------------------
        // 5. REGISTRAR ORÇAMENTO E ENVIAR PARA O WHATSAPP
        // ---------------------------------------------------------
        if (btnWhatsapp) {
            btnWhatsapp.addEventListener('click', async (evento) => {
                // Impede o link de abrir vazio antes de salvar no banco
                evento.preventDefault();

                const textoOriginal = btnWhatsapp.innerHTML;
                btnWhatsapp.style.pointerEvents = 'none';
                btnWhatsapp.style.opacity = '0.7';
                btnWhatsapp.innerHTML = '⏳ Gerando orçamento...';

                const quantidade = parseInt(inputQtd.value) || 1;
                const acabamentos = getAcabamentosSelecionados();
                const personalizacao = inputPersonalizacao ? inputPersonalizacao.value.trim() : '';
                const totalCalculado = parseFloat(cardCalculadora.dataset.ultimoTotal) || 0;

                try {
                    // Chama a rota que salva no banco e devolve o link prontinho do WhatsApp
                    let resposta = await fetch('public/index.php/api/registrar_orcamento', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            produtoId: produtoId,
                            quantidade: quantidade,
                            acabamentos: acabamentos,
                            nome_homenageado: personalizacao,
                            total: totalCalculado
                        })
                    }).catch(() => null);

                    // Tenta a rota /api/... se a primeira falhar
                    if (!resposta || !resposta.ok) {
                        resposta = await fetch('/api/registrar_orcamento', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                produtoId: produtoId,
                                quantidade: quantidade,
                                acabamentos: acabamentos,
                                nome_homenageado: personalizacao,
                                total: totalCalculado
                            })
                        }).catch(() => null);
                    }

                    if (resposta && resposta.ok) {
                        const resultado = await resposta.json();
                        if (resultado.sucesso && resultado.link_whatsapp) {
                            // Redireciona o usuario direto pro WhatsApp com o pedido montado!
                            window.location.href = resultado.link_whatsapp;
                            return;
                        }
                    }

                    // Se nao conseguiu resposta da API por algum motivo de conexao,
                    // abre o WhatsApp com a mensagem basica pelo link tradicional
                    abrirWhatsappFallback(quantidade, acabamentos, personalizacao, totalCalculado);

                } catch (e) {
                    abrirWhatsappFallback(quantidade, acabamentos, personalizacao, totalCalculado);
                } finally {
                    setTimeout(() => {
                        btnWhatsapp.innerHTML = textoOriginal;
                        btnWhatsapp.style.pointerEvents = 'auto';
                        btnWhatsapp.style.opacity = '1';
                    }, 2000);
                }
            });
        }

        // Funcao reserva para abrir o WhatsApp se o servidor estiver sem internet
        function abrirWhatsappFallback(quantidade, acabamentos, personalizacao, total) {
            let msg = `Olá! Gostaria de encomendar na *Zebra de Touca*:\n\n`;
            msg += `🔢 *Quantidade:* ${quantidade}\n`;
            if (personalizacao) {
                msg += `✍️ *Personalização:* ${personalizacao}\n`;
            }
            msg += `💰 *Orçamento estimado:* R$ ${total.toFixed(2)}\n\n`;
            msg += `Você teria disponibilidade para essa produção?`;

            const link = `https://wa.me/5511999999999?text=` + encodeURIComponent(msg);
            window.location.href = link;
        }

        // Executa o primeiro calculo assim que entra na pagina
        agendarRecalculo();
    }
});
