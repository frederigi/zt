<?php
// ====================================================================
// GERENCIADOR DE FOTOS DO PRODUTO (GALERIA)
// ====================================================================
// Permite que a artesã envie novas fotos da peca, defina a foto de capa
// e preencha a descricao de acessibilidade (alt text).

$produtoId = isset($_GET['produto_id']) ? (int)$_GET['produto_id'] : 0;

if ($produtoId <= 0) {
    header('Location: produtos.php');
    exit;
}

$tituloPagina = 'Gerenciar Fotos';
require_once __DIR__ . '/includes/header_admin.php';

// Busca os dados do produto
$stmtProd = $pdo->prepare("SELECT id, titulo FROM produtos WHERE id = :id");
$stmtProd->execute([':id' => $produtoId]);
$produto = $stmtProd->fetch();

if (!$produto) {
    echo "<div class='alerta-msg alerta-erro'>Produto não encontrado.</div>";
    require_once __DIR__ . '/includes/footer_admin.php';
    exit;
}

$msgSucesso = $_GET['sucesso'] ?? '';
$msgErro = $_GET['erro'] ?? '';

// -------------------------------------------------------------
// AÇÃO 1: DEFINIR FOTO COMO CAPA
// -------------------------------------------------------------
if (isset($_GET['acao']) && $_GET['acao'] === 'definir_capa' && isset($_GET['foto_id'])) {
    $fotoId = (int)$_GET['foto_id'];
    // Tira a capa de todas as fotos desse produto
    $pdo->prepare("UPDATE produto_imagens SET eh_capa = 0 WHERE produto_id = :prod_id")->execute([':prod_id' => $produtoId]);
    // Define a foto escolhida como capa
    $pdo->prepare("UPDATE produto_imagens SET eh_capa = 1 WHERE id = :id AND produto_id = :prod_id")->execute([':id' => $fotoId, ':prod_id' => $produtoId]);
    header("Location: fotos.php?produto_id={$produtoId}&sucesso=" . urlencode("Foto definida como capa com sucesso!"));
    exit;
}

// -------------------------------------------------------------
// AÇÃO 2: EXCLUIR UMA FOTO
// -------------------------------------------------------------
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir_foto' && isset($_GET['foto_id'])) {
    $fotoId = (int)$_GET['foto_id'];
    $stmtFoto = $pdo->prepare("SELECT url_imagem FROM produto_imagens WHERE id = :id AND produto_id = :prod_id");
    $stmtFoto->execute([':id' => $fotoId, ':prod_id' => $produtoId]);
    $fotoParaExcluir = $stmtFoto->fetchColumn();

    if ($fotoParaExcluir) {
        // Se a foto estiver salva na pasta uploads local, tenta apagar o arquivo do disco
        if (str_starts_with($fotoParaExcluir, '/uploads/')) {
            $arquivoFisico = __DIR__ . '/../../public' . $fotoParaExcluir;
            if (file_exists($arquivoFisico)) {
                @unlink($arquivoFisico);
            }
        }
        $pdo->prepare("DELETE FROM produto_imagens WHERE id = :id")->execute([':id' => $fotoId]);
    }

    header("Location: fotos.php?produto_id={$produtoId}&sucesso=" . urlencode("Foto removida com sucesso!"));
    exit;
}

// -------------------------------------------------------------
// AÇÃO 3: UPLOAD DE NOVA FOTO (VIA FORMULÁRIO POST)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $arquivo = $_FILES['foto'];
    $textoAlt = trim($_POST['texto_alt'] ?? '');
    $ehCapa = !empty($_POST['eh_capa']) ? 1 : 0;

    // Se nao digitou o texto de acessibilidade, preenche um padrao
    if (empty($textoAlt)) {
        $textoAlt = 'Foto detalhada do produto ' . $produto['titulo'];
    }

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $msgErro = 'Erro ao enviar o arquivo da foto.';
    } elseif ($arquivo['size'] > (5 * 1024 * 1024)) {
        $msgErro = 'A foto é muito pesada. O tamanho máximo permitido é de 5MB.';
    } else {
        // Valida a extensao real do arquivo
        $tiposAceitos = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        $mimeReal = mime_content_type($arquivo['tmp_name']);

        if (!isset($tiposAceitos[$mimeReal])) {
            $msgErro = 'Formato inválido! Envie uma foto em JPG, PNG ou WEBP.';
        } else {
            $ext = $tiposAceitos[$mimeReal];
            $pastaDestino = __DIR__ . '/../../public/uploads';

            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0755, true);
            }

            $nomeUnico = uniqid('prod_', true) . '.' . $ext;
            $caminhoFinal = $pastaDestino . DIRECTORY_SEPARATOR . $nomeUnico;

            if (move_uploaded_file($arquivo['tmp_name'], $caminhoFinal)) {
                $caminhoBanco = '/uploads/' . $nomeUnico;

                // Se a artesã marcou essa como capa, zera as outras
                if ($ehCapa === 1) {
                    $pdo->prepare("UPDATE produto_imagens SET eh_capa = 0 WHERE produto_id = :prod_id")->execute([':prod_id' => $produtoId]);
                } else {
                    // Se for a primeira foto do produto, vira capa automaticamente
                    $totalFotos = (int)$pdo->prepare("SELECT COUNT(*) FROM produto_imagens WHERE produto_id = :prod_id")->execute([':prod_id' => $produtoId]);
                    if ($totalFotos === 0) {
                        $ehCapa = 1;
                    }
                }

                // Salva no banco de dados
                $sqlInsert = "INSERT INTO produto_imagens (produto_id, url_imagem, texto_alt, eh_capa, ordem) 
                              VALUES (:prod_id, :url, :alt, :capa, 1)";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':prod_id' => $produtoId,
                    ':url'     => $caminhoBanco,
                    ':alt'     => $textoAlt,
                    ':capa'    => $ehCapa
                ]);

                header("Location: fotos.php?produto_id={$produtoId}&sucesso=" . urlencode("Foto adicionada com sucesso!"));
                exit;
            } else {
                $msgErro = 'Não foi possível salvar o arquivo na pasta uploads.';
            }
        }
    }
}

// Busca as fotos ja cadastradas desse produto
$stmtFotos = $pdo->prepare("SELECT * FROM produto_imagens WHERE produto_id = :prod_id ORDER BY eh_capa DESC, ordem ASC");
$stmtFotos->execute([':prod_id' => $produtoId]);
$fotosExistentes = $stmtFotos->fetchAll();
?>

<div class="admin-header-pagina">
    <div>
        <h1>📷 Fotos da Peça</h1>
        <p style="color: var(--admin-suave); font-size: 0.95rem;">
            Produto: <strong><?= htmlspecialchars($produto['titulo']) ?></strong>
        </p>
    </div>
    <div>
        <a href="produtos.php" class="btn-admin btn-admin-secundario">
            &larr; Voltar para Peças
        </a>
    </div>
</div>

<?php if (!empty($msgSucesso)): ?>
    <div class="alerta-msg alerta-sucesso">
        <?= htmlspecialchars($msgSucesso) ?>
    </div>
<?php endif; ?>

<?php if (!empty($msgErro)): ?>
    <div class="alerta-msg alerta-erro">
        <?= htmlspecialchars($msgErro) ?>
    </div>
<?php endif; ?>

<!-- FORMULARIO DE UPLOAD DE NOVA FOTO -->
<div class="card-painel" style="max-width: 800px;">
    <div class="card-painel-cabecalho">
        <h2>➕ Adicionar Nova Foto</h2>
    </div>

    <form action="fotos.php?produto_id=<?= $produtoId ?>" method="POST" enctype="multipart/form-data" class="form-admin">
        <div class="form-grupo">
            <label for="foto">Escolha a Imagem (JPG, PNG ou WEBP até 5MB): *</label>
            <input 
                type="file" 
                id="foto" 
                name="foto" 
                class="form-controle" 
                accept="image/jpeg,image/png,image/webp" 
                required
            >
        </div>

        <div class="form-grupo">
            <label for="texto_alt">Descrição da Foto para Acessibilidade (Deficientes Visuais): *</label>
            <input 
                type="text" 
                id="texto_alt" 
                name="texto_alt" 
                class="form-controle" 
                placeholder="Ex: Foto do topo de bolo Jardim Encantado com flores rosa e lamicote dourado"
                required
            >
            <span style="font-size: 0.8rem; color: var(--admin-suave);">
                💡 Requisito da faculdade: descreva a imagem para que leitores de tela possam ler para pessoas cegas.
            </span>
        </div>

        <div class="form-grupo">
            <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer;">
                <input type="checkbox" name="eh_capa" value="1" <?= count($fotosExistentes) === 0 ? 'checked' : '' ?>>
                <span>Definir esta foto como a <strong>capa principal</strong> da vitrine</span>
            </label>
        </div>

        <button type="submit" class="btn-admin btn-admin-primario">
            📤 Enviar Foto
        </button>
    </form>
</div>

<!-- GALERIA DAS FOTOS JÁ CADASTRADAS -->
<div class="card-painel">
    <div class="card-painel-cabecalho">
        <h2>Fotos Atuais (<?= count($fotosExistentes) ?> cadastradas)</h2>
    </div>

    <?php if (count($fotosExistentes) > 0): ?>
        <div class="grid-fotos-admin">
            <?php foreach ($fotosExistentes as $f): ?>
                <?php
                $urlFoto = $f['url_imagem'];
                if (str_starts_with($urlFoto, 'http')) {
                    $urlFinal = $urlFoto;
                } elseif (str_starts_with($urlFoto, '/uploads/')) {
                    $urlFinal = '../public' . $urlFoto;
                } else {
                    $urlFinal = '../' . $urlFoto;
                }
                ?>
                <div class="card-foto-item">
                    <img src="<?= htmlspecialchars($urlFinal) ?>" alt="<?= htmlspecialchars($f['texto_alt']) ?>">
                    
                    <div class="card-foto-corpo">
                        <div>
                            <?php if ((int)$f['eh_capa'] === 1): ?>
                                <span class="badge-capa">⭐ Foto de Capa</span>
                            <?php endif; ?>
                            <p style="margin-top: 6px; font-size: 0.78rem; color: var(--admin-texto);">
                                <?= htmlspecialchars($f['texto_alt']) ?>
                            </p>
                        </div>

                        <div style="display: flex; gap: 6px; margin-top: 8px;">
                            <?php if ((int)$f['eh_capa'] !== 1): ?>
                                <a href="fotos.php?produto_id=<?= $produtoId ?>&acao=definir_capa&foto_id=<?= $f['id'] ?>" class="btn-admin btn-admin-secundario btn-admin-sm" title="Tornar capa">
                                    ⭐ Tornar Capa
                                </a>
                            <?php endif; ?>
                            
                            <a href="fotos.php?produto_id=<?= $produtoId ?>&acao=excluir_foto&foto_id=<?= $f['id'] ?>" class="btn-admin btn-admin-perigo btn-admin-sm" onclick="return confirm('Deseja excluir esta foto?');" title="Excluir foto">
                                🗑️ Excluir
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="padding: 30px; text-align: center; color: var(--admin-suave);">
            Nenhuma foto cadastrada para este produto ainda. Envie a primeira foto no formulário acima!
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer_admin.php'; ?>
