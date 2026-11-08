# Documentação da aplicação SetupDino

## 1. Visão geral

SetupDino é uma loja virtual de acessórios, periféricos e móveis gamer. A aplicação usa PHP para renderizar as páginas e executar as regras no servidor, MySQL/MariaDB para persistência, JavaScript para interações no navegador e CSS para a apresentação visual.

O fluxo básico de uma requisição é:

```text
Navegador
  │ solicita uma página ou envia um formulário
  ▼
Apache + PHP
  ├─ carrega as configurações de .env
  ├─ verifica sessão, perfil e token CSRF quando aplicável
  ├─ executa consultas preparadas pelo PDO
  └─ monta a resposta HTML
  ▼
MySQL/MariaDB
  │ devolve os dados
  ▼
Navegador
  ├─ apresenta HTML e CSS
  └─ executa JavaScript para menu, filtros e carrinho
```

O JavaScript pode melhorar a experiência, mas dados enviados pelo navegador não são considerados confiáveis. No fechamento do pedido, o PHP consulta preço e estoque atuais no banco.

## 2. Tecnologias e requisitos

- PHP 8.1 ou superior.
- MySQL 8 ou MariaDB compatível.
- Extensões PHP: `pdo_mysql`, `fileinfo` e `mbstring`.
- Composer e dependências do arquivo `composer.json` (`vlucas/phpdotenv`).
- Apache para hospedagem local via XAMPP ou um servidor web configurado para PHP.

O carrinho também usa `localStorage` do navegador. Não há integração com gateway de pagamento.

## 3. Instalação no XAMPP (Windows)

O script `scripts/setup-xampp.ps1` automatiza a instalação em uma instalação existente do XAMPP.

1. Faça uma cópia de segurança do banco e do projeto se já houver uma instalação.
2. Inicie o MySQL pelo painel do XAMPP.
3. Abra o PowerShell na pasta do projeto. Se necessário, permita scripts somente nessa janela e execute:

   ```powershell
   Set-ExecutionPolicy -Scope Process Bypass
   .\scripts\setup-xampp.ps1
   ```

4. Se o XAMPP estiver em outro local, informe a pasta:

   ```powershell
   .\scripts\setup-xampp.ps1 -XamppPath 'D:\xampp'
   ```

   Para uma porta MySQL diferente de 3306, use também `-MySqlPort 3307`, substituindo pelo valor configurado.

5. Informe a senha do MySQL `root` (pode ficar vazia se a conta local não tiver senha) e os dados da conta administradora.
6. Se já existir `htdocs\SetupDino`, o script pede confirmação antes de atualizar os arquivos. Ele não sobrescreve os arquivos de dados `.env` durante a cópia e faz uma cópia de segurança do `.env` antes de criar a nova configuração.
7. Se o banco SetupDino já existir, o script não importa novamente os dados de exemplo. Quando necessário, pede confirmação para aplicar a migração de vendedores.
8. Inicie o Apache e acesse `http://localhost/SetupDino/`.

O script verifica a versão do PHP, as extensões requeridas e a conexão ao MySQL. Composer precisa estar disponível no `PATH` quando a pasta `vendor/` não estiver presente. A aplicação deve permanecer local; não exponha o XAMPP à internet pública.

## 4. Instalação manual

### Banco novo

Importe o esquema e os dados de exemplo:

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

Crie um usuário MySQL da aplicação com senha própria e permissões limitadas ao banco:

```sql
CREATE USER IF NOT EXISTS 'SetupDino_app'@'localhost'
  IDENTIFIED BY 'SUBSTITUA_POR_UMA_SENHA_FORTE';
GRANT SELECT, INSERT, UPDATE, DELETE ON SetupDino.*
  TO 'SetupDino_app'@'localhost';
```

Copie `.env.example` para `.env` e preencha as configurações:

```dotenv
DB_HOST=localhost
DB_PORT=3306
DB_NAME=SetupDino
DB_USER=SetupDino_app
DB_PASSWORD=SUBSTITUA_PELA_SENHA_DO_USUARIO_MYSQL
```

Instale as dependências PHP com Composer, se necessário, e inicie o servidor local a partir da raiz:

```bash
composer install
php -S localhost:8000
```

Acesse `http://localhost:8000/`. O servidor embutido do PHP é somente para desenvolvimento e não deve ser publicado.

### Banco existente

Se o banco foi criado antes do suporte a vendedores, faça backup e aplique uma única vez:

```bash
mysql -u root -p < database/migrations/001_seller_products.sql
```

Essa migração adiciona o perfil `seller` e a associação entre produto e vendedor. Não a execute em um banco novo criado pelo `database/schema.sql` atual nem mais de uma vez.

## 5. Primeiro acesso administrativo e vendedores

Para criar o primeiro administrador manualmente:

1. Registre uma conta pela página `/register.php`.
2. No MySQL/phpMyAdmin, conceda o perfil administrativo:

   ```sql
   UPDATE users
   SET role = 'admin'
   WHERE email = 'seu-email@example.com';
   ```

3. Saia da conta e entre novamente para carregar o perfil atualizado na sessão.
4. Acesse `/admin/`.

No instalador XAMPP, o administrador é criado durante a configuração. Para habilitar um vendedor, ele deve primeiro possuir uma conta normal. Um administrador pode mudar o perfil para **Vendedor** em **Administração → Clientes e vendedores**. A mudança passa a valer na próxima autenticação do usuário.

## 6. Perfis e permissões

| Perfil | Acesso |
|---|---|
| Visitante | Página inicial, catálogo e detalhes dos produtos. |
| Cliente | Recursos de visitante, carrinho no próprio navegador, autenticação e criação de pedido demonstrativo. |
| Vendedor | Painel `/seller/`; cria, edita e remove apenas seus próprios produtos. |
| Administrador | Painel `/admin/`; administra produtos, categorias, perfis de cliente/vendedor e status dos pedidos. |

O vendedor não pode alterar o proprietário de um produto nem marcar produtos como destaque. Essas ações são reservadas ao administrador. Produtos criados por vendedores são publicados imediatamente.

## 7. Funcionalidades da loja

### Página inicial e catálogo

- `/` apresenta produtos em destaque e categorias.
- `/products.php` lista os produtos e permite busca, filtro de categoria e ordenação.
- Exemplos de parâmetros: `?search=keyboard&category=keyboards&sort=price_asc`.
- `/product.php?id=1` mostra detalhes, preço, estoque e opção para adicionar ao carrinho.
- Se não houver arquivo de imagem correspondente, a interface mostra um ícone substituto.

### Carrinho

`assets/js/cart.js` guarda os itens no `localStorage` do navegador. Cada navegador tem seu próprio carrinho; ele não é sincronizado entre dispositivos nem mantido como carrinho no servidor.

O carrinho permite adicionar e remover produtos e alterar quantidades. O preço apresentado antes da compra é uma estimativa. O servidor busca os preços e estoques atuais novamente ao criar o pedido.

### Cadastro, autenticação e pedidos

- `/register.php` cria uma conta de cliente com senha armazenada como hash.
- `/login.php` autentica e inicia uma sessão PHP.
- `/logout.php` encerra a sessão.
- `/checkout.php` exige autenticação, endereço de entrega e itens válidos.
- O pedido é salvo com status inicial `pending`; a quantidade em estoque é reduzida dentro da mesma transação.
- `/order-success.php?id=...` mostra somente um pedido pertencente ao usuário autenticado.

O checkout é demonstrativo: não existe cobrança, pagamento real, cálculo de frete ou integração com transportadora.

## 8. Painel administrativo

| Endereço | Função |
|---|---|
| `/admin/` | Resumo de produtos, pedidos, vendedores e clientes. |
| `/admin/products.php` | Lista, cria, edita e remove produtos. O administrador pode escolher destaque. |
| `/admin/product-form.php` | Formulário de inclusão/edição de produto e imagem. |
| `/admin/categories.php` | Cria, renomeia e remove categorias. Uma categoria usada por produtos não pode ser removida até que esses produtos sejam reclassificados ou removidos. |
| `/admin/customers.php` | Lista contas e alterna o perfil entre cliente e vendedor. |
| `/admin/orders.php` | Lista pedidos e atualiza o status. |

Status disponíveis: `pending` (pendente), `paid` (pago), `shipped` (enviado), `delivered` (entregue) e `cancelled` (cancelado). O painel não aplica um fluxo de transição ou pagamento automático.

## 9. Painel do vendedor e produtos

| Endereço | Função |
|---|---|
| `/seller/` | Resumo de produtos e unidades em estoque do vendedor conectado. |
| `/seller/products.php` | Lista e permite excluir apenas produtos próprios. |
| `/seller/product-form.php` | Cria e edita produtos próprios. |

Campos do produto: nome, categoria, descrição, preço, quantidade em estoque e imagem opcional. O nome gera um identificador amigável (slug); se já existir, é acrescentado um sufixo.

As imagens devem ser JPG, PNG, WebP ou GIF, com no máximo 5 MB e dimensões limitadas. São salvas em `assets/images/products/` com nomes aleatórios. O PHP valida o tipo real do arquivo, não somente a extensão. O servidor precisa ter permissão de escrita nesse diretório.

## 10. Modelo de dados

O esquema principal está em `database/schema.sql`; dados de exemplo estão em `database/seed.sql`.

| Tabela | Responsabilidade |
|---|---|
| `users` | Nome, e-mail, hash de senha e perfil (`customer`, `seller` ou `admin`). |
| `categories` | Categorias e seus slugs únicos. |
| `products` | Catálogo, preço, estoque, destaque, imagem e `seller_id` opcional. Produtos antigos sem vendedor têm `seller_id` nulo. |
| `orders` | Cliente, total, status e data do pedido. |
| `order_items` | Produtos, quantidades e preços registrados no pedido. |
| `order_addresses` | Endereço de entrega associado ao pedido. |

`config/database.php` carrega `.env` por `vlucas/phpdotenv` e cria uma conexão PDO reutilizável. As consultas que recebem dados da requisição usam parâmetros preparados.

## 11. Mapa do projeto

```text
SetupDino/
├── admin/             Páginas do painel administrativo
├── assets/
│   ├── css/           Estilos da loja e dos painéis
│   ├── images/        Imagens dos produtos
│   └── js/            Menu, filtros e carrinho no navegador
├── config/            Configuração de conexão com o banco
├── database/
│   ├── migrations/    Migrações para bancos existentes
│   ├── schema.sql     Estrutura do banco
│   └── seed.sql       Categorias e produtos de demonstração
├── docs/              Documentação detalhada
├── includes/          Autenticação, layout, formulários e gestão compartilhada
├── scripts/           Automação de instalação XAMPP
├── seller/            Painel do vendedor
├── src/               Consultas de produtos e serviços de carrinho/pedidos
├── .env               Configurações locais; não versionar
└── index.php          Página inicial
```

Páginas PHP na raiz incluem o catálogo, detalhes do produto, cadastro, login, carrinho e checkout.

## 12. Segurança, configuração e limitações

- Não compartilhe nem versione o arquivo `.env`; `.gitignore` já o exclui.
- O banco deve ser acessado por uma conta com apenas as permissões necessárias à aplicação.
- Senhas de usuários são salvas como hashes; o login regenera o identificador da sessão.
- Formulários administrativos, de vendedor e de checkout usam tokens CSRF.
- Valores exibidos são escapados e as operações SQL usam PDO.
- O carrinho local não é confiável: estoque, preço e dados do pedido são verificados no servidor.
- O upload valida tipo MIME, dimensões, tamanho e nome aleatório; configure também os limites de upload do PHP.
- O XAMPP e o servidor embutido do PHP são destinados a desenvolvimento local, não a hospedagem pública.
- A aplicação ainda não possui gateway de pagamento, recuperação de senha, e-mail transacional, cálculo de frete, regras de comissão ou fluxo de aprovação de produtos.

Antes de qualquer uso real, é necessário implantar em hospedagem configurada com HTTPS, raiz pública restrita, backups e política de segurança para arquivos enviados.

## 13. Verificações para desenvolvimento

Verifique a sintaxe de um arquivo PHP:

```bash
php -l caminho/do/arquivo.php
```

Verifique o JavaScript do carrinho:

```bash
node --check assets/js/cart.js
```

Confira se a conexão PDO consegue executar uma consulta simples usando a configuração local, sem imprimir credenciais:

```bash
php -r 'require "config/database.php"; echo getDatabaseConnection()->query("SELECT 1")->fetchColumn();'
```
