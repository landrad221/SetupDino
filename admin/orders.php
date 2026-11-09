<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = getStorage();
$statuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
$labels = ['pending' => 'Pendente', 'paid' => 'Pago', 'shipped' => 'Enviado', 'delivered' => 'Entregue', 'cancelled' => 'Cancelado'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf($_POST['csrf'] ?? null);
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $status = (string)($_POST['status'] ?? '');
    if ($id === false || $id < 1 || !in_array($status, $statuses, true)) {
        $error = 'Selecione um pedido e status válidos.';
    } else {
        $db->updateOrderStatus($id, $status);
        header('Location: /admin/orders.php?saved=1');
        exit;
    }
}
$orders = $db->listAdminOrders();
$pageTitle = 'Pedidos';
require __DIR__ . '/../includes/header.php';
?>
<section class="section"><div class="container"><div class="admin-layout">
    <?php $managementIsAdmin = true; require __DIR__ . '/../includes/management-sidebar.php'; ?>
    <section class="admin-content">
        <span class="eyebrow">Vendas da loja</span><h1>Pedidos</h1>
        <?php if (($_GET['saved'] ?? '') === '1'): ?><p class="notice">Status do pedido atualizado.</p><?php endif; ?>
        <?php if ($error !== ''): ?><p class="form-error" role="alert"><?= escape($error) ?></p><?php endif; ?>
        <?php if (!$orders): ?><p class="empty-state">Ainda não há pedidos.</p><?php else: ?>
        <div class="table-wrap"><table class="management-table"><thead><tr><th>Pedido</th><th>Cliente</th><th>Itens</th><th>Total</th><th>Status</th><th>Data</th></tr></thead><tbody>
        <?php foreach ($orders as $order): ?><tr>
            <td>#<?= (int)$order['id'] ?></td><td><?= escape($order['customer_name']) ?><br><span class="muted"><?= escape($order['customer_email']) ?></span></td><td><?= (int)$order['item_count'] ?></td><td><?= formatPrice($order['total']) ?></td>
            <td><form class="inline-form" method="post"><input type="hidden" name="csrf" value="<?= escape(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int)$order['id'] ?>"><select name="status"><?php foreach ($statuses as $status): ?><option value="<?= escape($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= escape($labels[$status]) ?></option><?php endforeach; ?></select><button class="btn btn-outline" type="submit">Atualizar</button></form></td>
            <td><?= escape((string)$order['created_at']) ?></td>
        </tr><?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </section>
</div></div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
