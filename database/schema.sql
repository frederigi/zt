-- ====================================================================
-- BANCO DE DADOS: Catalogo Digital de Papelaria Personalizada
-- Projeto Integrador (TCC)
-- ====================================================================

-- 1. Criacao do banco de dados (se ainda nao existir)
CREATE DATABASE IF NOT EXISTS papelaria_catalogo
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE papelaria_catalogo;

-- ====================================================================
-- TABELA 1: categorias
-- Guarda as categorias dos produtos para organizar o catalogo tipo "pastas"
-- ====================================================================
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,            -- Nome legivel: "Topos de Bolo", "Convites"
    slug VARCHAR(100) NOT NULL UNIQUE,     -- Versao sem acentos para filtros/URLs: "topos-de-bolo"
    descricao TEXT NULL                    -- Explicacao breve do tipo de produto
) ENGINE=InnoDB;

-- ====================================================================
-- TABELA 2: produtos
-- Guarda os dados principais de cada peca feita pela artesã
-- ====================================================================
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,             -- Chave que liga o produto à sua categoria
    titulo VARCHAR(150) NOT NULL,          -- Nome do produto (ex: "Topo de Bolo Jardim Encantado")
    descricao TEXT NULL,                   -- Detalhes do acabamento, tipos de papel, etc.
    tamanho VARCHAR(100) NOT NULL,         -- Dimensoes recomendadas (ex: "Bolo de 15cm a 20cm")
    preco_base DECIMAL(10, 2) NOT NULL,    -- Preco inicial por unidade
    destaque ENUM('padrao', 'mais_vendido', 'destaque', 'lancamento') DEFAULT 'padrao',
    visualizacoes INT NOT NULL DEFAULT 0,  -- Contador simples para o painel de interesse
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Ligacao com a tabela categorias: se a categoria for excluida, impede para nao deixar produto sem categoria
    CONSTRAINT fk_produtos_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ====================================================================
-- TABELA 3: produto_imagens
-- Guarda de 2 a 4 fotos por produto, suas URLs na nuvem e o texto de acessibilidade
-- ====================================================================
CREATE TABLE IF NOT EXISTS produto_imagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,               -- Chave que liga a foto ao produto
    url_imagem VARCHAR(255) NOT NULL,      -- Caminho local (/uploads/foto.jpg) ou link externo
    texto_alt VARCHAR(255) NOT NULL,       -- Descricao para deficientes visuais (Acessibilidade)
    eh_capa TINYINT(1) NOT NULL DEFAULT 0, -- 1 para a foto principal da vitrine, 0 para fotos de detalhes
    ordem INT NOT NULL DEFAULT 1,          -- Ordem de exibicao das fotos no carrossel/galeria

    -- Se o produto for excluido, todas as fotos dele sao apagadas automaticamente (CASCADE)
    CONSTRAINT fk_imagens_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ====================================================================
-- TABELA 4: orcamentos
-- Guarda os pedidos e simulacoes feitas pelos clientes no site
-- ====================================================================
CREATE TABLE IF NOT EXISTS orcamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,               -- Produto escolhido pelo cliente
    quantidade INT NOT NULL,               -- Quantas pecas a pessoa quer
    acabamentos TEXT,                      -- Lista dos opcionais selecionados (ex: lamicote, 3D)
    nome_homenageado VARCHAR(150),         -- Nome da pessoa aniversariante / evento
    idade VARCHAR(20),                     -- Idade ou data comemorativa
    total DECIMAL(10, 2) NOT NULL,         -- Preco total calculado no momento da simulacao
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Data e hora que o cliente pediu

    -- Amarra o orcamento ao produto
    CONSTRAINT fk_orcamentos_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ====================================================================
-- DADOS INICIAIS (SEED) PARA TESTES
-- Cadastrando categorias e produtos reais de papelaria personalizada
-- ====================================================================

-- Inserindo categorias reais
INSERT INTO categorias (id, nome, slug, descricao) VALUES
(1, 'Topos de Bolo', 'topos-de-bolo', 'Topos personalizados em camadas de papel, shaker e lamicote'),
(2, 'Lembrancinhas e Caixas', 'lembrancinhas-e-caixas', 'Caixas milk, pirâmide, sushi e bolsinhas para doces'),
(3, 'Convites e Cartões', 'convites-e-cartoes', 'Convites para aniversários, batizados, casamentos e chá de bebê'),
(4, 'Etiquetas e Tags', 'etiquetas-e-tags', 'Tags de agradecimento, adesivos de vinil e rótulos personalizados');

-- Inserindo produtos com detalhes reais
INSERT INTO produtos (id, categoria_id, titulo, descricao, tamanho, preco_base, destaque, visualizacoes) VALUES
(1, 1, 'Topo de Bolo Jardim das Borboletas 3D', 'Feito em papel fotográfico matte 230g, papel perolado e lamicote dourado. Borboletas aplicadas em relevo com efeito de camadas.', 'Ideal para bolos de 15cm a 20cm', 32.00, 'mais_vendido', 14),
(2, 1, 'Topo de Bolo Safari Menino Shaker', 'Camadas especiais com visor transparente contendo miçangas e glitter flutuante. Acompanha canudo dourado resistente.', 'Ideal para bolos de 20cm a 25cm', 38.00, 'destaque', 9),
(3, 2, 'Kit 10 Caixas Milk Fazendinha', 'Caixinhas decoradas com apliques em relevo 3D e laço pronto de cetim com chaton brilhante.', 'Medidas: 6,5cm x 6,5cm x 13cm', 45.00, 'lancamento', 5),
(4, 2, 'Kit 10 Caixas Pirâmide Bosque Encantado', 'Caixas cone com apliques duplos e acabamento com passamanaria delicada.', 'Medidas: 7cm x 7cm x 17cm', 42.00, 'padrao', 3),
(5, 3, 'Convite Pergaminho Pequeno Príncipe', 'Convite temático impresso em papel vergê especial, acompanha tag com o nome do convidado e fita.', 'Medidas: 14cm x 20cm (aberto)', 6.50, 'destaque', 8),
(6, 4, 'Cartela 30 Tags Redondas Floral', 'Tags em papel kraft ou branco fosco com furo para passar cordão ou fita de cetim.', 'Diâmetro: 5cm', 18.00, 'padrao', 2);

-- Inserindo fotos reais com textos alternativos (acessibilidade)
-- Nota: URLs de exemplo prontas para visualizacao enquanto o upload da nuvem e configurado
INSERT INTO produto_imagens (produto_id, url_imagem, texto_alt, eh_capa, ordem) VALUES
-- Fotos do Topo Jardim das Borboletas
(1, 'https://images.unsplash.com/photo-1535141192574-5d4897c13136?auto=format&fit=crop&w=600&q=80', 'Foto frontal do topo de bolo Jardim das Borboletas, em tons rosa e lilas com detalhes dourados', 1, 1),
(1, 'https://images.unsplash.com/photo-1519869325930-281384150729?auto=format&fit=crop&w=600&q=80', 'Detalhe aproximado das camadas das asas das borboletas em papel lamicote com efeito 3D', 0, 2),

-- Fotos do Topo Safari Shaker
(2, 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=600&q=80', 'Topo de bolo Safari com animais fofos e visor central redondo contendo lantejoulas', 1, 1),

-- Fotos da Caixa Milk Fazendinha
(3, 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=600&q=80', 'Caixa milk artesanal de papel com desenho de vaquinha e celeiro, fechada com fita de cetim vermelha', 1, 1),

-- Fotos da Caixa Pirâmide
(4, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80', 'Caixa em formato de cone piramide com folhas verdes e animais da floresta em papel relevo', 1, 1),

-- Fotos do Convite
(5, 'https://images.unsplash.com/photo-1512909006721-3d6018887383?auto=format&fit=crop&w=600&q=80', 'Convite infantil aberto com ilustracao da rosa e do pequeno principe sob fundo azul escuro estrelado', 1, 1),

-- Fotos das Tags
(6, 'https://images.unsplash.com/photo-1607344645866-009c320c5ab8?auto=format&fit=crop&w=600&q=80', 'Conjunto de tags redondas com estampa floral e frase com carinho furadas para colocacao de laco', 1, 1);

-- ====================================================================
-- TABELA 5: usuarios
-- Guarda os logins de acesso ao Painel da Artesã e Área Administrativa
-- ====================================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,            -- Nome completo da pessoa
    usuario VARCHAR(50) NOT NULL UNIQUE,   -- Login que usa para entrar (ex: superadmin)
    senha VARCHAR(255) NOT NULL,           -- Senha protegida com criptografia hash do PHP
    perfil ENUM('artesa', 'superadmin') NOT NULL DEFAULT 'artesa', -- Nivel de permissao
    ativo TINYINT(1) NOT NULL DEFAULT 1,   -- 1 para ativo, 0 se estiver bloqueado
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Inserindo usuarios padrao do sistema:
-- 1. superadmin (senha inicial: univesp)
-- 2. artesa (senha inicial: zebra123)
INSERT INTO usuarios (id, nome, usuario, senha, perfil, ativo) VALUES
(1, 'Administrador do Sistema', 'superadmin', '$2y$12$sB/aGRW.79K88cVyVJdNg.OvSdn5JH1eJ707ZBQfToLwFYSIPa0lm', 'superadmin', 1),
(2, 'Artesã Zebra de Touca', 'artesa', '$2y$12$ZaIut2EXP6oUeLvbCohzMeL0FYY8coc5RDkS6UDIh525Zcp8epUIK', 'artesa', 1)
ON DUPLICATE KEY UPDATE id=id;

