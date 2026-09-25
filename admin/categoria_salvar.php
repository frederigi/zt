<?php
// ====================================================================
// SALVAR CATEGORIA NO BANCO (INSERT / UPDATE)
// ====================================================================
// Processa o formulario admin/categorias.php e gera o slug amigavel.

require_once __DIR__ . '/includes/auth.php';
exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: categorias.php');
    exit;
}

$id        = (int)($_POST['id'] ?? 0);
$nome      = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if (empty($nome)) {
    $msg = urlencode('O nome da categoria é obrigatório.');
    header("Location: categorias.php?erro={$msg}");
    exit;
}

// Funcao simples pra transformar o nome legivel em link amigavel (slug)
// Exemplo: "Topos de Bolo e Velas" vira "topos-de-bolo-e-velas"
function gerarSlug(string $texto): string {
    $texto = mb_strtolower($texto, 'UTF-8');
    // Troca letras com acentos por letras normais
    $mapaAcentos = [
        'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ü'=>'u','ç'=>'c',
        'Á'=>'a','À'=>'a','Ã'=>'a','Â'=>'a','É'=>'e','Ê'=>'e','Í'=>'i','Ó'=>'o','Ô'=>'o','Õ'=>'o','Ú'=>'u','Ü'=>'u','Ç'=>'c'
    ];
    $texto = strtr($texto, $mapaAcentos);
    // Troca qualquer caractere que nao seja letra ou numero por traco
    $texto = preg_replace('/[^a-z0-9]+/i', '-', $texto);
    // Tira tracos sobrando nas pontas
    return trim($texto, '-');
}

$slug = gerarSlug($nome);

try {
    // Confere se o slug ja existe em outra categoria pra nao dar erro de duplicidade UNIQUE
    $stmtCheck = $pdo->prepare("SELECT id FROM categorias WHERE slug = :slug AND id != :id");
    $stmtCheck->execute([':slug' => $slug, ':id' => $id]);
    if ($stmtCheck->fetch()) {
        $slug .= '-' . rand(1, 99); // adiciona um numero se já existir
    }

    if ($id > 0) {
        // Atualiza a categoria existente
        $stmt = $pdo->prepare("UPDATE categorias SET nome = :nome, slug = :slug, descricao = :descricao WHERE id = :id");
        $stmt->execute([
            ':nome'      => $nome,
            ':slug'      => $slug,
            ':descricao' => $descricao,
            ':id'        => $id
        ]);
        $msg = urlencode("Categoria '{$nome}' atualizada com sucesso!");
    } else {
        // Cadastra uma categoria nova
        $stmt = $pdo->prepare("INSERT INTO categorias (nome, slug, descricao) VALUES (:nome, :slug, :descricao)");
        $stmt->execute([
            ':nome'      => $nome,
            ':slug'      => $slug,
            ':descricao' => $descricao
        ]);
        $msg = urlencode("Categoria '{$nome}' criada com sucesso!");
    }

    header("Location: categorias.php?sucesso={$msg}");
    exit;

} catch (Throwable $e) {
    $msg = urlencode("Erro ao salvar categoria: " . $e->getMessage());
    header("Location: categorias.php?erro={$msg}");
    exit;
}
