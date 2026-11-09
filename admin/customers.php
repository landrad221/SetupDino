<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = getStorage();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf'] ?? null);
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $role = (string)($_POST['role'] ?? '');
    if ($id === false || $id < 1 || !in_array($role, ['customer', 'seller'], true)) {
        $error = 'Selecione uma conta e um tipo de acesso válidos.';
    } else {
        $db->updateUserRole($id, $role);
        header('Location: /admin/customers.php?saved=1');
        exit;
    }
}
$users = $db->listUsers();
$pageTitle = 'Clientes e vendedores';
require __DIR__ . '/../includes/header.php';
?>
<section class="section"><div class="container"><div class="admin-layout">
    <?php $managementIsAdmin = true; require __DIR__ . '/../includes/management-sidebar.php'; ?>
    <section class="admin-content">
        <span class="eyebrow">Contas da loja</span><h1>Clientes e vendedores</h1>
        <p class="muted">Para habilitar um vendedor, crie primeiro uma conta normal na loja e altere o tipo de acesso aqui.</p>
        <?php if (($_GET['saved'] ?? '') === '1'): ?><p class="notice">Acesso atualizado. A alteração será aplicada na próxima vez que a conta entrar.</p><?php endif; ?>
        <?php if ($error !== ''): ?><p class="form-error" role="alert"><?= escape($error) ?></p><?php endif; ?>
        <?php if (!$users): ?><p class="empty-state">Nenhuma conta cadastrada.</p><?php else: ?>
        <div class="table-wrap"><table class="management-table"><thead><tr><th>Nome</th><th>E-mail</th><th>Tipo de acesso</th><th>Cadastro</th></tr></thead><tbody>
        <?php foreach ($users as $user): ?><tr>
            <td><?= escape($user['name']) ?></td><td><?= escape($user['email']) ?></td>
            <td><?php if ($user['role'] === 'admin'): ?>Administrador<?php else: ?><form class="inline-form" method="post"><input type="hidden" name="csrf" value="<?= escape(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int)$user['id'] ?>"><select name="role"><option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Cliente</option><option value="seller" <?= $user['role'] === 'seller' ? 'selected' : '' ?>>Vendedor</option></select><button class="btn btn-outline" type="submit">Salvar</button></form><?php endif; ?></td>
            <td><?= escape((string)$user['created_at']) ?></td>
        </tr><?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </section>
</div></div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
