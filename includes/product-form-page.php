<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/product-management.php';

<<<<<<< HEAD
$db = getDatabaseConnection();
=======
$db = getStorage();
>>>>>>> master
$productId = null;
if (isset($_GET['id'])) {
    $validatedId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($validatedId === false || $validatedId === null || $validatedId < 1) {
        http_response_code(400);
        exit('Identificador de produto inválido.');
    }
    $productId = $validatedId;
}
$product = null;
$error = '';
if ($productId !== null) {
<<<<<<< HEAD
    $sql = 'SELECT * FROM products WHERE id = ?';
    $params = [$productId];
    if (!$productFormAdmin) {
        $sql .= ' AND seller_id = ?';
        $params[] = (int)$_SESSION['user_id'];
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $product = $stmt->fetch();
=======
    $product = $db->getManagedProduct($productId, $productFormAdmin ? null : (int)$_SESSION['user_id']);
>>>>>>> master
    if (!$product) {
        http_response_code(404);
        exit('Produto não encontrado ou sem permissão para editá-lo.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf'] ?? null);
    try {
        saveManagedProduct($db, (int)$_SESSION['user_id'], $productFormAdmin, $productId, $_POST, $_FILES['image'] ?? []);
        header('Location: ' . $productFormBase . '/products.php?saved=1');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Não foi possível salvar o produto. Confira os dados e tente novamente.';
    }
}

<<<<<<< HEAD
$categories = $db->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
=======
$categories = $db->listCategories();
>>>>>>> master
$value = static fn(string $key, mixed $default = ''): mixed => $_POST[$key] ?? $product[$key] ?? $default;
$managementIsAdmin = $productFormAdmin;
$pageTitle = $productId === null ? 'Adicionar produto' : 'Editar produto';
require __DIR__ . '/header.php';
?>
<section class="section">
    <div class="container">
        <div class="admin-layout">
            <?php require __DIR__ . '/management-sidebar.php'; ?>
            <section class="admin-content">
                <h1><?= escape($pageTitle) ?></h1>
                <?php if ($error !== ''): ?><p class="form-error" role="alert"><?= escape($error) ?></p><?php endif; ?>
                <?php if (!$categories): ?><p class="notice">Cadastre uma categoria antes de adicionar produtos.</p><?php endif; ?>
                <form class="stack-form management-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= escape(csrfToken()) ?>">
                    <label>Nome do produto
                        <input name="name" maxlength="180" required value="<?= escape((string)$value('name')) ?>">
                    </label>
                    <label>Categoria
                        <select name="category_id" required>
                            <option value="">Selecione uma categoria</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int)$category['id'] ?>" <?= (string)$value('category_id') === (string)$category['id'] ? 'selected' : '' ?>><?= escape($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Descrição
                        <textarea name="description" rows="5" maxlength="15000" required><?= escape((string)$value('description')) ?></textarea>
                    </label>
                    <div class="form-grid">
                        <label>Preço (R$)
                            <input name="price" type="number" min="0" max="99999999.99" step="0.01" required value="<?= escape((string)$value('price')) ?>">
                        </label>
                        <label>Quantidade em estoque
                            <input name="stock" type="number" min="0" step="1" required value="<?= escape((string)$value('stock', '0')) ?>">
                        </label>
                    </div>
                    <label>Foto do produto (JPG, PNG, WebP ou GIF; até 5 MB)
                        <input name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                    </label>
                    <?php if (!empty($product['image'])): ?>
                        <div class="current-image">
                            <img src="<?= escape(asset('images/products/' . basename((string)$product['image']))) ?>" alt="">
                            <label><input type="checkbox" name="remove_image" value="1"> Remover foto atual</label>
                        </div>
                    <?php endif; ?>
                    <?php if ($productFormAdmin): ?>
                        <label class="checkbox-label"><input type="checkbox" name="featured" value="1" <?= ($_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['featured']) : !empty($product['featured'])) ? 'checked' : '' ?>> Exibir como produto em destaque</label>
                    <?php endif; ?>
                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit" <?= !$categories ? 'disabled' : '' ?>>Salvar produto</button>
                        <a class="btn btn-outline" href="<?= escape($productFormBase) ?>/products.php">Cancelar</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</section>
<?php require __DIR__ . '/footer.php'; ?>
