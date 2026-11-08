<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireSeller();
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
            deleteManagedProduct($db, $id, (int)$_SESSION['user_id']);
            header('Location: /seller/products.php?saved=deleted');
            exit;
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Não foi possível excluir o produto. Ele pode estar vinculado a um pedido.';
        }
    }
}
$stmt = $db->prepare('SELECT id, name, price, stock FROM products WHERE seller_id = ? ORDER BY id DESC');
$stmt->execute([(int)$_SESSION['user_id']]);
$products = $stmt->fetchAll();
$pageTitle = 'Meus produtos';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="admin-layout">
            <?php $managementIsAdmin = false; require __DIR__ . '/../includes/management-sidebar.php'; ?>
            <section class="admin-content">
                <div class="section-heading"><div><span class="eyebrow">Seu catálogo</span><h1>Meus produtos</h1></div><a class="btn btn-primary" href="/seller/product-form.php">Adicionar produto</a></div>
                <?php if (($_GET['saved'] ?? '') === '1'): ?><p class="notice">Produto salvo com sucesso.</p><?php elseif (($_GET['saved'] ?? '') === 'deleted'): ?><p class="notice">Produto excluído.</p><?php endif; ?>
                <?php if ($error !== ''): ?><p class="form-error" role="alert"><?= escape($error) ?></p><?php endif; ?>
                <?php if (!$products): ?><p class="empty-state">Você ainda não cadastrou produtos.</p><?php else: ?>
                    <div class="table-wrap"><table class="management-table"><thead><tr><th>Produto</th><th>Preço</th><th>Estoque</th><th>Ações</th></tr></thead><tbody>
                    <?php foreach ($products as $product): ?><tr>
                        <td><?= escape($product['name']) ?></td><td><?= formatPrice($product['price']) ?></td><td><?= (int)$product['stock'] ?></td>
                        <td class="table-actions"><a class="btn btn-outline" href="/seller/product-form.php?id=<?= (int)$product['id'] ?>">Editar</a>
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
