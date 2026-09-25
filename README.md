# 🎨 Zebra de Touca - Catálogo Digital & Calculadora de Orçamentos
### Projeto Integrador II - UNIVESP

---

## 📌 Sobre o Projeto

Este projeto foi desenvolvido como parte do **Projeto Integrador II - UNIVESP** (Universidade Virtual do Estado de São Paulo).

O sistema é um **Catálogo Digital Dinâmico e Web Full-Stack** concebido sob medida para atender a uma microempreendedora do ramo de **papelaria personalizada** (*Zebra de Touca*), especializada na confecção sob encomenda de topos de bolo, lembrancinhas, convites, tags, caixas cenário e impressões personalizadas para festas e datas comemorativas.

---

## 🎯 O Problema Real da Empreendedora

Antes da criação do catálogo digital, a proprietária enfrentava gargalos diários no processo de atendimento e vendas:
- **Sobrecarga e Perda de Tempo:** Para apresentar seus trabalhos, a artesã precisava vasculhar manualmente fotos antigas na galeria do celular.
- **Atrito no Atendimento:** O cliente precisava fazer múltiplas perguntas para entender tamanhos, opções de papéis, acabamentos e estimativas de preço.
- **Perda de Vendas:** A demora em localizar fotos de referência e responder ao cliente abria margem para desistência ou orçamentos inconclusivos.

---

## 💡 A Solução Desenvolvida

O sistema substitui o envio manual e desordenado de fotos por uma experiência web moderna, rápida e intuitiva:
1. **Vitrine Categorizada ("Pastas Virtuais"):** Organização dos produtos por categorias temáticas (Topos de Bolo, Caixas Cenário, Lembrancinhas, Convites), facilitando a navegação e filtragem.
2. **Página Detalhada do Produto:** Exibição de fotos reais, dimensões indicadas, descrição completa dos materiais e indicação de destaques (*Mais Vendidos*, *Lançamentos*).
3. **API REST Interna:** Regras de negócio e cálculo de orçamento centralizados no backend com **Slim Framework 4**, garantindo segurança no valor final e evitando manipulação no frontend.
4. **Registro de Orçamentos:** Cada simulação confirmada pelo cliente é gravada na tabela `orcamentos` do MySQL com número de identificação único.
5. **Link Direto para o WhatsApp:** Ao finalizar o pedido, o sistema gera um link direto (`https://wa.me/55...`) que já abre o aplicativo ou WhatsApp Web da artesã com a mensagem pré-formatada contendo todos os detalhes e o número do orçamento.
6. **Armazenamento Local de Fotos:** As fotos dos produtos são gerenciadas e armazenadas localmente no próprio servidor (`public/uploads/`), com endpoint dedicado para upload e validação de formato e tamanho.
7. **Acessibilidade e Usabilidade:** Recursos nativos para pessoas com baixa visão ou dificuldades de leitura, incluindo redimensionamento de fontes (A-, A, A+), modo de alto contraste e descrições textuais (`alt`) nas imagens.

---

## 🚀 Requisitos da UNIVESP Atendidos

| Requisito do PI II | Implementação no Projeto |
| :--- | :--- |
| **Servidor / Back-End** | PHP 8+ com **Slim Framework 4** e arquitetura RESTful organizada em Controllers, Models e Services |
| **Banco de Dados Relacional** | MySQL com modelagem relacional (`categorias`, `produtos`, `produto_imagens`, `orcamentos`), chaves estrangeiras e integridade |
| **Testes Automatizados** | Suíte de testes unitários com **PHPUnit 10** validando regras de cálculo de orçamento e upload de arquivos |
| **Front-End Interativo** | JavaScript (ES6+ Vanilla) integrado à API REST via `fetch()`, HTML5 Semântico e CSS3 com design responsivo |
| **Acessibilidade Web (e-MAG / WCAG)** | Barra com controles de zoom de texto (A-, A, A+), modo de alto contraste e textos alternativos ricos para leitores de tela |
| **Armazenamento de Arquivos** | Armazenamento local de fotos em `public/uploads/` com validação de tipo (JPEG/PNG/WEBP) e tamanho máximo (5MB) |
| **Comunicação com a Artesã** | Geração de link direto do WhatsApp (`wa.me`) com mensagem estruturada contendo o código do orçamento gravado |
| **Gerenciamento de Pacotes** | Composer com autoloading no padrão PSR-4 |
| **Controle de Versão** | Versionamento e gestão de código com Git e repositório GitHub |

---

## 🛠️ Tecnologias Utilizadas

- **Linguagem:** [PHP 8+](https://www.php.net/)
- **Framework Back-End:** [Slim Framework 4](https://www.slimframework.com/) + [Slim PSR-7](https://github.com/slimphp/Slim-Psr7)
- **Gerenciador de Dependências:** [Composer](https://getcomposer.org/) (Autoloading PSR-4)
- **Testes Automatizados:** [PHPUnit 10](https://phpunit.de/)
- **Banco de Dados:** [MySQL](https://www.mysql.com/) / MariaDB (com PDO e Prepared Statements)
- **Front-End:** 
  - HTML5 Semântico (acessibilidade e SEO)
  - CSS3 puro (Flexbox, Grid, variáveis CSS, temas claro e alto contraste)
  - JavaScript Vanilla (consumo da API REST via `fetch`, galeria de fotos e acessibilidade)
- **Servidor Web Suportado:** Apache / LiteSpeed (compatível com cPanel DDR Host)

---

## 🔌 Rotas da API REST

A API interna do sistema é centralizada no Slim Framework através do front controller `public/index.php`:

| Método | Rota | Descrição |
| :--- | :--- | :--- |
| **GET** | `/api/produtos` | Lista os produtos da vitrine (aceita `?categoria=slug` para filtro) |
| **GET** | `/api/produto/{id}` | Retorna os detalhes de um produto e sua galeria de fotos |
| **POST** | `/api/simular_orcamento` | Calcula o orçamento em tempo real com base no produto, quantidade e opcionais |
| **POST** | `/api/registrar_orcamento` | Salva o orçamento no banco e devolve o link do WhatsApp com o pedido gravado |
| **POST** | `/api/upload_imagem` | Valida e salva uma nova foto de produto na pasta local `public/uploads/` |

---

## 📂 Estrutura de Pastas do Projeto

```text
zt/
├── assets/
│   ├── css/
│   │   └── estilo.css             # Folha de estilos (responsividade, tema e alto contraste)
│   └── js/
│       └── main.js                # Lógica da galeria, calculadora (API REST) e acessibilidade
├── config/
│   ├── conexao.example.php        # Modelo para configuração das credenciais do banco
│   └── conexao.php                # Credenciais reais de conexão (ignorado no Git)
├── database/
│   └── schema.sql                 # Script SQL de criação das tabelas (com orçamentos) e dados iniciais
├── img/                           # Imagens e logotipos fixos da identidade visual
│   └── logo.png
├── includes/
│   ├── header.php                 # Cabeçalho com barra de acessibilidade
│   └── footer.php                 # Rodapé padrão do site
├── public/                        # Document root do servidor web
│   ├── index.php                  # Front Controller do Slim Framework 4
│   ├── .htaccess                  # Regras de reescrita de URL do Apache/LiteSpeed
│   └── uploads/                   # Armazenamento local das fotos dos produtos
│       └── .gitignore
├── src/                           # Código-fonte da aplicação (App\)
│   ├── Controllers/
│   │   ├── ProdutoController.php   # Endpoints de listagem e detalhes de produtos
│   │   ├── OrcamentoController.php # Endpoints de simulação e registro de orçamentos
│   │   └── UploadController.php    # Validação e upload de fotos
│   ├── Database/
│   │   └── Connection.php         # Conexão PDO segura (Singleton)
│   ├── Models/
│   │   ├── Produto.php            # Consultas de produtos e galeria
│   │   └── Orcamento.php          # Gravação de orçamentos no MySQL
│   ├── Services/
│   │   └── OrcamentoCalculator.php# Regras matemáticas isoladas para cálculo de preços
│   └── routes.php                 # Definição e mapeamento das rotas da API REST
├── tests/                         # Testes automatizados (Tests\)
│   ├── Unit/
│   │   ├── OrcamentoTest.php      # Testes unitários da calculadora de orçamento
│   │   └── UploadTest.php         # Testes unitários das validações de upload
│   └── bootstrap.php              # Inicialização do ambiente de testes
├── vendor/                        # Dependências gerenciadas pelo Composer
├── composer.json                  # Definição dos pacotes e scripts do projeto
├── phpunit.xml                    # Configuração do executor do PHPUnit
├── index.php                      # Catálogo principal com filtro de categorias
├── produto.php                    # Detalhe do produto, fotos e calculadora
├── .gitignore                     # Arquivos que não sobem para o GitHub
└── README.md                      # Documentação completa do projeto
```

---

## 💻 Como Rodar o Projeto Localmente

### 1. Pré-requisitos
- PHP 8.0 ou superior (com extensões `pdo_mysql`, `curl` e `zip` ativadas)
- MySQL / MariaDB
- Composer instalado

### 2. Instalação das dependências
Na raiz do projeto, execute:
```bash
composer install
```

### 3. Configuração do Banco de Dados
1. Crie o banco de dados no seu MySQL importando o arquivo:
   ```bash
   database/schema.sql
   ```
2. Copie o arquivo de exemplo de conexão e configure seus dados de acesso:
   ```bash
   cp config/conexao.example.php config/conexao.php
   ```
   Abra `config/conexao.php` e preencha `$host`, `$banco`, `$usuario` e `$senha`.

### 4. Execução dos Testes Automatizados
Para rodar a suíte de testes unitários com o PHPUnit:
```bash
composer test
# ou diretamente:
./vendor/bin/phpunit
```

### 5. Executando o Servidor Local
Você pode utilizar o servidor embutido do PHP:
```bash
php -S localhost:8000
```
Acesse no seu navegador:
- **Catálogo Web:** `http://localhost:8000/index.php`
- **API REST (Slim):** `http://localhost:8000/public/index.php/api/produtos`

*(Em ambiente Apache/XAMPP ou cPanel, basta apontar para a pasta do projeto normalmente).*

---

## 📱 Fluxo de Uso do Cliente

1. **Navegação no Catálogo:** O cliente acessa a página inicial, visualiza os destaques e filtra os produtos pela categoria desejada.
2. **Exploração do Produto:** Ao selecionar um item, acessa fotos de modelos já produzidos e lê detalhes técnicos (dimensões e materiais).
3. **Simulação do Orçamento:** Informa a quantidade e acabamentos desejados. O JavaScript consulta a API REST interna (`POST /api/simular_orcamento`) e o valor estimado é recalculado na hora.
4. **Finalização com Pedido Gravado:** Ao clicar em "Pedir pelo WhatsApp", o sistema registra o orçamento no banco (`POST /api/registrar_orcamento`) e redireciona o cliente para o WhatsApp da artesã via link direto (`wa.me`) com uma mensagem já pronta contendo o código do orçamento:
   > *"Olá! Gostaria de encomendar na Zebra de Touca:  
   > 🔖 Orçamento nº: #1  
   > 📌 Produto: Topo de Bolo Jardim das Borboletas 3D  
   > 🔢 Quantidade: 2 unidades  
   > ✨ Acabamentos: Papel Lamicote Dourado em relevo  
   > ✍️ Homenageado: Sofia (5 anos)  
   > 💰 Orçamento estimado no site: R$ 72,00  
   > Você teria disponibilidade para essa produção?"*

---

## 👥 Integrantes do Projeto (UNIVESP)

- **Universidade Virtual do Estado de São Paulo — UNIVESP**
- **Disciplina:** Projeto Integrador em Computação II (PI II)
- **Integrantes:** 
Flávio Fernandes dos Santos - Polo Américo Brasiliense

Andersom Leandro Gonçalez Frederigi - Polo Brotas

Jessika Moretti - Polo São Carlos

Regiane de Oliveira Gaspar - Polo Araraquara

Erica Gonçalves - Polo Matão

Jéssica Marina Goveia - Polo São Carlos

Leonardo Coelho Sanchez - Polo Brotas

Lucas Magno de Oliveira - Polo Araraquara

---

## 📄 Licença

Este projeto é desenvolvido para fins exclusivamente educacionais e acadêmicos.
