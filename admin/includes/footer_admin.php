    </main>

    <footer style="text-align: center; padding: 24px; color: var(--admin-suave); font-size: 0.85rem;">
        <p><strong>Painel da Artesã - Zebra de Touca</strong></p>
        <p>Projeto Integrador II - UNIVESP</p>
    </footer>

    <!-- Script para o botão de instalação do painel -->
    <?php if (($usuarioAtual['perfil'] ?? '') === 'artesa'): ?>
        <p id="ajuda-instalacao-painel" role="status" aria-live="polite" style="text-align: center; padding: 0 16px 16px;"></p>
        <script>
            (() => {
                const botao = document.getElementById('instalar-painel');
                const ajuda = document.getElementById('ajuda-instalacao-painel');

                if (!botao || !ajuda) return;

                let promptInstalacao = null;
                const appInstalado = window.matchMedia('(display-mode: standalone)').matches
                    || window.navigator.standalone === true;

                if (appInstalado) {
                    botao.hidden = true;
                    return;
                }

                window.addEventListener('beforeinstallprompt', (evento) => {
                    evento.preventDefault();
                    promptInstalacao = evento;
                });

                botao.addEventListener('click', async () => {
                    if (!promptInstalacao) {
                        ajuda.textContent = 'Para instalar, use a opção “Instalar app” no menu do navegador. No iPhone ou iPad, abra no Safari, toque em Compartilhar e escolha “Adicionar à Tela de Início”.';
                        return;
                    }

                    const evento = promptInstalacao;
                    promptInstalacao = null;
                    const resultado = await evento.prompt();

                    ajuda.textContent = resultado.outcome === 'accepted'
                        ? 'Instalação confirmada.'
                        : 'Instalação não concluída.';
                });

                window.addEventListener('appinstalled', () => {
                    botao.hidden = true;
                    ajuda.textContent = 'Painel instalado.';
                });
            })();
        </script>
    <?php endif; ?>

    <!-- FIM DO CONTEUDO -->
</body>
</html>