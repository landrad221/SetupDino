<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/product-management.php';

$db = getDatabaseConnection();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf'] ?? null);
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        $error = 'Produto inválido.';
    } else {
        try {
            deleteManagedProduct($db, $id, null);
            header('Location: /admin/products.php?saved=deleted');
            exit;
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Não foi possível excluir o produto. Ele pode estar vinculado a um pedido.';
        }
    }
}
$products = $db->query(
    'SELECT p.id, p.name, p.price, p.stock, p.featured, c.name AS category_name, u.name AS seller_name
     FROM products p JOIN categories c ON c.id = p.category_id
     LEFT JOIN users u ON u.id = p.seller_id ORDER BY p.id DESC'
)->fetchAll();
$pageTitle = 'Produtos';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="admin-layout">
            <?php $managementIsAdmin = true; require __DIR__ . '/../includes/management-sidebar.php'; ?>
            <section class="admin-content">
                <div class="section-heading"><div><span class="eyebrow">Catálogo</span><h1>Produtos</h1></div><a class="btn btn-primary" href="/admin/product-form.php">Adicionar produto</a></div>
                <?php if (($_GET['saved'] ?? '') === '1'): ?><p class="notice">Produto salvo com sucesso.</p><?php elseif (($_GET['saved'] ?? '') === 'deleted'): ?><p class="notice">Produto excluído.</p><?php endif; ?>
                <?php if ($error !== ''): ?><p class="form-error" role="alert"><?= escape($error) ?></p><?php endif; ?>
                <?php if (!$products): ?><p class="empty-state">Ainda não há produtos cadastrados.</p><?php else: ?>
                    <div class="table-wrap"><table class="management-table"><thead><tr><th>Produto</th><th>Categoria</th><th>Vendedor</th><th>Preço</th><th>Estoque</th><th>Ações</th></tr></thead><tbody>
                    <?php foreach ($products as $product): ?><tr>
                        <td><?= escape($product['name']) ?><?= $product['featured'] ? ' · Destaque' : '' ?></td>
                        <td><?= escape($product['category_name']) ?></td>
                        <td><?= escape($product['seller_name'] ?? 'SetupDino') ?></td>
                        <td><?= formatPrice($product['price']) ?></td>
                        <td><?= (int)$product['stock'] ?></td>
                        <td class="table-actions"><a class="btn btn-outline" href="/admin/product-form.php?id=<?= (int)$product['id'] ?>">Editar</a>
                            <form method="post" onsubmit="return confirm('Excluir este produto?')"><input type="hidden" name="csrf" value="<?= escape(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int)$product['id'] ?>"><button class="btn btn-outline" type="submit">Excluir</button></form>
                        </td>
                    </tr><?php endforeach; ?>
                    </tbody></table></div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
