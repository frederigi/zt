<?php
// ====================================================================
// RODAPÉ PADRÃO (FOOTER)
// Fecha as tags principais e inclui os scripts do sistema
// ====================================================================
?>
    </main>
    <!-- FIM DO CONTEÚDO PRINCIPAL -->

    <!-- RODAPÉ -->
    <footer class="rodape-principal">
        <div class="container">
            <p><strong>Zebra de Touca - Papelaria Personalizada</strong></p>
            <p>Peças exclusivas feitas à mão com afeto para celebrar seus momentos especiais.</p>
            <p style="margin-top: 8px; font-size: 0.8rem; opacity: 0.8;">
                Projeto Integrador II - UNIVESP
            </p>
        </div>
    </footer>

    <!-- Script de acessibilidade e interatividade -->
    <script src="assets/js/main.js"></script>
    <!-- Registro do PWA público; indisponibilidade não interfere no site -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                const serviceWorkerUrl = new URL('service-worker.js', document.baseURI);
                navigator.serviceWorker.register(serviceWorkerUrl).catch(() => {
                    // O site continua funcionando normalmente se o registro falhar.
                });
            });
        }
    </script>
</body>
</html>
