<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = getDatabaseConnection();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf'] ?? null);
    $action = (string)($_POST['action'] ?? '');
    $name = trim((string)($_POST['name'] ?? ''));
    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            $error = 'Categoria inválida.';
        } else {
            try {
                $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
                $stmt->execute([$id]);
                header('Location: /admin/categories.php?saved=deleted');
                exit;
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Não foi possível excluir a categoria. Remova ou reclassifique seus produtos primeiro.';
            }
        }
    } elseif (($action === 'add' || $action === 'update') && $name !== '' && mb_strlen($name) <= 100) {
        $slug = slugify($name, '');
        if ($slug === '') {
            $error = 'O nome da categoria precisa conter letras ou números.';
        } else {
            try {
                if ($action === 'add') {
                    $stmt = $db->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
                    $stmt->execute([$name, $slug]);
                } else {
                    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                    if ($id === false || $id < 1) {
                        throw new InvalidArgumentException('Categoria inválida.');
                    }
                    $stmt = $db->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?');
                    $stmt->execute([$name, $slug, $id]);
                }
                header('Location: /admin/categories.php?saved=1');
                exit;
            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Não foi possível salvar. O nome da categoria talvez já esteja em uso.';
            }
        }
    } else {
        $error = 'Informe um nome de categoria com até 100 caracteres.';
    }
}
$categories = $db->query(
    'SELECT c.id, c.name, c.slug, COUNT(p.id) AS product_count
     FROM categories c LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id, c.name, c.slug ORDER BY c.name'
)->fetchAll();
$pageTitle = 'Categorias';
require __DIR__ . '/../includes/header.php';
?>
<section class="section"><div class="container"><div class="admin-layout">
    <?php $managementIsAdmin = true; require __DIR__ . '/../includes/management-sidebar.php'; ?>
    <section class="admin-content">
        <span class="eyebrow">Organização do catálogo</span><h1>Categorias</h1>
        <?php if (($_GET['saved'] ?? '') === '1'): ?><p class="notice">Categoria salva.</p><?php elseif (($_GET['saved'] ?? '') === 'deleted'): ?><p class="notice">Categoria excluída.</p><?php endif; ?>
        <?php if ($error !== ''): ?><p class="form-error" role="alert"><?= escape($error) ?></p><?php endif; ?>
        <form class="inline-form" method="post">
            <input type="hidden" name="csrf" value="<?= escape(csrfToken()) ?>"><input type="hidden" name="action" value="add">
            <label class="sr-only" for="new-category">Nome da nova categoria</label><input id="new-category" name="name" maxlength="100" placeholder="Nova categoria" required>
            <button class="btn btn-primary" type="submit">Adicionar</button>
        </form>
        <?php if (!$categories): ?><p class="empty-state">Nenhuma categoria cadastrada.</p><?php else: ?>
        <div class="category-admin-list"><?php foreach ($categories as $category): ?>
            <div class="category-admin-row">
                <form class="inline-form" method="post"><input type="hidden" name="csrf" value="<?= escape(csrfToken()) ?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
                    <label class="sr-only" for="category-<?= (int)$category['id'] ?>">Nome da categoria</label><input id="category-<?= (int)$category['id'] ?>" name="name" maxlength="100" required value="<?= escape($category['name']) ?>">
                    <button class="btn btn-outline" type="submit">Salvar</button>
                </form>
                <span class="muted"><?= (int)$category['product_count'] ?> produto(s)</span>
                <form method="post" onsubmit="return confirm('Excluir esta categoria?')"><input type="hidden" name="csrf" value="<?= escape(csrfToken()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$category['id'] ?>"><button class="btn btn-outline" type="submit">Excluir</button></form>
            </div>
        <?php endforeach; ?></div>
        <?php endif; ?>
    </section>
</div></div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
