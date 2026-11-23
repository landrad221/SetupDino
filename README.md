# SetupDino

O SetupDino é uma loja em PHP para periféricos e móveis gamers. Inclui catálogo e carrinho para clientes, autenticação de contas, finalização de compra e áreas separadas de administração e venda.

## Funcionalidades

- Cadastro, login, logout e acesso baseado em função
- Catálogo de produtos com filtros por categoria e texto
- Produtos em destaque e páginas detalhadas
- Carrinho no navegador com limite de estoque
- Checkout e confirmação de pedido
- Painel administrativo para produtos, categorias, clientes, vendedores e pedidos
- Painel do vendedor para gerenciar produtos próprios
- Implementações de armazenamento com CSV ou PDO

## Estrutura do projeto

| Caminho | Finalidade |
| --- | --- |
| `index.php` | Página inicial pública da loja |
| `products.php`, `product.php` | Catálogo e detalhes de produto |
| `cart.php`, `checkout.php`, `order-success.php` | Fluxo de compra e pedido |
| `login.php`, `register.php`, `logout.php` | Fluxo de conta do cliente |
| `includes/` | Layout compartilhado, autenticação e funções de apresentação |
| `admin/` | Páginas do administrador |
| `seller/` | Páginas do vendedor |
| `src/` | Classes e interfaces de persistência, repositório e pedidos |
| `config/database.php` | Configuração do armazenamento e carregamento do ambiente |
| `assets/css/` | Estilos público e administrativo |
| `assets/js/` | Comportamentos do cabeçalho, carrinho, catálogo e administração |
| `assets/images/` | Marca compartilhada e imagens de produtos |
| `vendor/` | Dependências Composer de terceiros; não são modificadas |

## Requisitos

- PHP 8.1 ou superior
- Servidor web local apontando para a raiz do repositório

## Instalação

1. Extraia o conteudo na raiz do diretório do apache
   ```

2. Execute o servidor e abra no endereco correspondente. Ex : http://localhost

## Configuração do armazenamento

O armazenamento padrão é `CsvStorage`.

- Defina `CSV_STORAGE_PATH` no arquivo `.env` ou no ambiente do processo.
- Caminhos relativos são resolvidos a partir da pasta do projeto.
- Sem `CSV_STORAGE_PATH`, é usado `<pai-da-pasta-publica>/setupdino-csv`.
- Os arquivos CSV devem permitir escrita pelo processo PHP.

Em instalações com banco, implemente ou selecione uma implementação de `AppStorage` e atualize `config/database.php`. `PdoStorage` está disponível para conexões PDO.

## Arquivos CSV

O diretório CSV contém registros como:

- `users.csv`
- `categories.csv`
- `products.csv`
- `orders.csv`
- `order_items.csv`
- `order_addresses.csv`

Mantenha esses arquivos fora da pasta pública ou bloqueie o acesso HTTP direto. O backup deve incluir todo o diretório CSV.

## Segurança

- Sessões usam modo estrito, cookies HTTP-only, SameSite Lax e Secure em HTTPS.
- Formulários que alteram estado usam tokens CSRF.
- Senhas são protegidas pelas funções modernas de hash do PHP.
- Valores vindos do banco ou CSV são escapados como HTML antes da saída.
- Operações PDO usam consultas preparadas.
- Páginas administrativas e de vendedores exigem validação do perfil no servidor.


## Convenções de contribuição

- Mantenha apresentação reutilizável e controle de acesso em `includes/`.
- Mantenha persistência por meio de `AppStorage`.
- Use `ProductRepository` e `OrderService` nas operações de domínio.
- Escape saída não confiável com `escape()`.
- Escreva comentários sobre intenção, invariantes e comportamento não óbvio.
- Não edite arquivos em `vendor/`.

## Autores

- Lucas Araújo de Andrade       - RA - 202602192527
- Acacio Brito Dos Santos       - RA - 202601518461
- Vitor Daniel Silva dos Santos - RA – 202602795787
- Thauany Riatto                - RA - 202603589102 
- Fabiano Miranda da Silva      - RA - 202602189372

