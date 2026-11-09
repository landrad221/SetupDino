# SetupDino 🎮

Loja virtual de acessórios, periféricos e móveis gamer, desenvolvida com **PHP, MySQL, HTML, CSS e JavaScript puro**.

> **Slogan:** “Você está pedindo um SetupDino.”

## Documentação

Consulte o [guia completo da aplicação](docs/APLICACAO.md) para conhecer a arquitetura, as páginas, os perfis de acesso, os painéis de administração e vendedor, o banco de dados, as funcionalidades e as limitações atuais.

## Funcionalidades

- Página inicial com categorias e produtos em destaque.
- Catálogo com busca, filtro de categoria e ordenação.
- Página de detalhes de produto e indicação de estoque.
- Carrinho no navegador usando `localStorage`.
- Cadastro de clientes, login e sessões PHP.
- Checkout demonstrativo com registro de pedidos e baixa de estoque.
- Painel administrativo para gerenciar produtos, categorias, contas e status dos pedidos.
- Painel do vendedor para cadastrar e gerenciar somente os próprios produtos.
- Upload de imagens de produto em JPG, PNG, WebP ou GIF, com limite de 5 MB.

## Requisitos

- PHP 8.1 ou superior, com as extensões `pdo_mysql`, `fileinfo` e `mbstring`.
- MySQL 8 ou MariaDB compatível.
- Composer para instalar `vlucas/phpdotenv`, se `vendor/` ainda não existir.
- XAMPP no Windows ou servidor PHP configurado para desenvolvimento local.

## Instalação no Windows com XAMPP

O script [scripts/setup-xampp.ps1](scripts/setup-xampp.ps1) instala o projeto em uma instalação existente do XAMPP.

1. Inicie o MySQL pelo painel do XAMPP.
2. No PowerShell, abra a pasta do projeto e execute:

   ```powershell
   Set-ExecutionPolicy -Scope Process Bypass
   .\scripts\setup-xampp.ps1
   ```

3. Para um XAMPP instalado fora de `C:\xampp`, informe o caminho:

   ```powershell
   .\scripts\setup-xampp.ps1 -XamppPath 'D:\xampp'
   ```

   Para uma porta MySQL diferente de 3306, acrescente, por exemplo, `-MySqlPort 3307`.

4. Informe a senha do MySQL `root` (pode ficar vazia se a conta local não tiver senha) e os dados da conta administradora.
<<<<<<< HEAD
5. Inicie o Apache e acesse `http://localhost/SetupDino/`.
=======
5. Inicie o Apache e acesse o endereço informado pelo script: `http://localhost/` se o projeto já estiver diretamente em `htdocs`, ou `http://localhost/SetupDino/` se ele for copiado para lá.
>>>>>>> master

O script verifica os requisitos do PHP e a conexão ao banco. Em um banco novo, cria o esquema, importa dados de exemplo e configura a conta de banco da aplicação. Em um banco existente, não reimporta os dados de exemplo e pede confirmação antes de aplicar a migração de vendedores. Se houver uma instalação anterior no `htdocs`, o script pede confirmação e faz backup do `.env` antes de substituí-lo.

**Faça backup do banco antes de atualizar uma instalação existente.** O XAMPP é para desenvolvimento local; não exponha o servidor à internet.

## Instalação manual

Para criar um banco novo, importe o esquema e os dados de exemplo:

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

Crie um usuário MySQL dedicado à aplicação, com uma senha própria:

```sql
CREATE USER IF NOT EXISTS 'SetupDino_app'@'localhost'
  IDENTIFIED BY 'SUBSTITUA_POR_UMA_SENHA_FORTE';
GRANT SELECT, INSERT, UPDATE, DELETE ON SetupDino.*
  TO 'SetupDino_app'@'localhost';
```

Copie `.env.example` para `.env` e ajuste as credenciais:

```dotenv
DB_HOST=localhost
DB_PORT=3306
DB_NAME=SetupDino
DB_USER=SetupDino_app
DB_PASSWORD=SUBSTITUA_PELA_SENHA_DO_USUARIO_MYSQL
```

<<<<<<< HEAD
=======
Para uma demonstração local sem MySQL, altere `STORAGE_DRIVER=csv` no `.env`. Os arquivos CSV são criados fora da pasta pública do Apache por padrão, em `setupdino-csv` ao lado de `htdocs`; `CSV_STORAGE_PATH` pode apontar para outro diretório. Esse modo usa um conjunto de dados separado e não migra registros existentes do MySQL. A primeira conta cadastrada recebe acesso de administrador. Os CSVs contêm dados de contas e pedidos, portanto mantenha esse modo apenas em ambiente local/de demonstração e faça cópias de segurança dos arquivos.

>>>>>>> master
Instale as dependências, se necessário, e inicie o servidor local:

```bash
composer install
php -S localhost:8000
```

Acesse `http://localhost:8000/`. O servidor embutido do PHP é somente para desenvolvimento e não deve ser usado publicamente.

### Atualização de um banco existente

Se o banco foi criado antes do suporte a vendedores, faça backup e execute uma única vez:

```bash
mysql -u root -p < database/migrations/001_seller_products.sql
```

Não execute essa migração em um banco novo criado pelo `database/schema.sql` atual nem mais de uma vez.

## Primeiro acesso e endereços

Para criar o primeiro administrador manualmente, registre uma conta e altere seu perfil no MySQL:

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'seu-email@example.com';
```

Saia e entre novamente para atualizar a sessão. O script XAMPP também permite criar a conta administradora durante a instalação.

| Endereço | Acesso |
|---|---|
| `/` | Página inicial |
| `/products.php` | Catálogo |
| `/product.php?id=1` | Detalhes de um produto |
| `/cart.php` | Carrinho |
| `/register.php` e `/login.php` | Cadastro e autenticação |
| `/checkout.php` | Checkout demonstrativo; requer login |
| `/admin/` | Painel administrativo |
| `/seller/` | Painel do vendedor |

Para habilitar um vendedor, ele precisa ter uma conta cadastrada. Um administrador pode mudar o perfil da conta em **Administração → Clientes e vendedores**. Consulte o [guia completo](docs/APLICACAO.md) para detalhes sobre as permissões e cada área do site.

## Estrutura do projeto

- `admin/`: administração de produtos, categorias, contas e pedidos.
- `seller/`: área do vendedor e gestão dos próprios produtos.
- `assets/`: estilos, JavaScript e imagens.
- `config/`: conexão PDO e configuração do ambiente.
- `database/`: esquema, dados de demonstração e migrações.
- `docs/`: documentação detalhada em português.
- `includes/`: autenticação, layout, validação e lógica compartilhada.
- `scripts/`: automação de instalação para XAMPP.
- `src/`: repositório de produtos e serviços de carrinho/pedidos.
- `index.php` e outros arquivos PHP da raiz: páginas da loja.

## Segurança e limitações

- Nunca compartilhe ou versione `.env`; o arquivo já é ignorado pelo Git.
- O carrinho é armazenado no navegador, mas os preços e o estoque são verificados novamente no servidor ao criar o pedido.
- O checkout é demonstrativo: não cobra pagamento nem calcula frete.
- Formulários protegidos usam token CSRF; senhas são armazenadas como hashes.
- Imagens enviadas são verificadas quanto ao tipo real, tamanho e dimensões. O servidor precisa poder gravar em `assets/images/products/`.
- A aplicação ainda não possui gateway de pagamento, recuperação de senha, e-mail transacional, comissão de vendedores ou aprovação de anúncios.
- Para produção, configure HTTPS, raiz pública restrita, backups e uma política de armazenamento seguro para os arquivos enviados. XAMPP e o servidor embutido do PHP não devem ser expostos à internet.
