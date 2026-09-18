<?php
// ====================================================================
// CABEÇALHO PADRÃO (HEADER)
// Inclui os botões de acessibilidade, topo da página e menu de navegação
// ====================================================================

// Define o título padrão caso a página não tenha definido um específico
if (!isset($tituloPagina)) {
    $tituloPagina = 'Catálogo de Papelaria Personalizada';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Catálogo digital de papelaria personalizada da Zebra de Touca: topos de bolo, lembrancinhas, caixas cenário e convites sob encomenda.">
    <title><?= htmlspecialchars($tituloPagina) ?> - Zebra de Touca</title>
    
    <!-- Folha de estilo principal -->
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>

    <!-- 1. BARRA SUPERIOR DE ACESSIBILIDADE (Requisito da Faculdade) -->
    <div class="barra-acessibilidade" role="region" aria-label="Ferramentas de acessibilidade">
        <div class="container barra-acessibilidade-conteudo">
            <span>Acessibilidade:</span>
            <button type="button" id="btn-diminuir-fonte" class="btn-acessibilidade" title="Diminuir tamanho do texto" aria-label="Diminuir texto">A-</button>
            <button type="button" id="btn-resetar-fonte" class="btn-acessibilidade" title="Tamanho normal do texto" aria-label="Tamanho normal do texto">A</button>
            <button type="button" id="btn-aumentar-fonte" class="btn-acessibilidade" title="Aumentar tamanho do texto" aria-label="Aumentar texto">A+</button>
            <button type="button" id="btn-alto-contraste" class="btn-acessibilidade" title="Alternar modo de alto contraste" aria-label="Alto contraste">🌓 Alto Contraste</button>
        </div>
    </div>

    <!-- 2. CABEÇALHO COM LOGOTIPO E MENU -->
    <header class="cabecalho-principal">
        <div class="container cabecalho-conteudo">
            <a href="index.php" class="logo-link" title="Zebra de Touca - Página Inicial">
                <img 
                    src="img/logo.jpeg" 
                    alt="Logotipo Zebra de Touca - Papelaria personalizada e impressões 3D" 
                    class="logo-imagem"
                >
            </a>

            <nav aria-label="Navegação principal">
                <ul class="menu-navegacao">
                    <li><a href="index.php">Catálogo</a></li>
                    <li><a href="admin/index.php">Painel da Artesã</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- 3. INÍCIO DO CONTEÚDO PRINCIPAL -->
    <main class="conteudo-principal container" id="conteudo-principal">
