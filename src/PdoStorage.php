<?php
declare(strict_types=1);

final class PdoStorage implements AppStorage
{
    public function __construct(private PDO $db) {}

    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT id, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function createCustomer(string $name, string $email, string $passwordHash): array
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, \'customer\')');
        $stmt->execute([$name, $email, $passwordHash]);
        return ['id' => (int)$this->db->lastInsertId(), 'role' => 'customer'];
    }

    public function getUserRole(int $userId): ?string
    {
        $stmt = $this->db->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $role = $stmt->fetchColumn();
        return is_string($role) ? $role : null;
    }

    public function listUsers(): array
    {
        return $this->db->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
    }

    public function updateUserRole(int $userId, string $role): void
    {
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ? AND role <> 'admin'");
        $stmt->execute([$role, $userId]);
    }

    public function getAdminDashboardCounts(): array
    {
        return [
            'products' => (int)$this->db->query('SELECT COUNT(*) FROM products')->fetchColumn(),
            'orders' => (int)$this->db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'sellers' => (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn(),
            'customers' => (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
        ];
    }

    public function listCategories(bool $withProductCount = false): array
    {
        if ($withProductCount) {
            return $this->db->query(
                'SELECT c.id, c.name, c.slug, COUNT(p.id) AS product_count
                 FROM categories c LEFT JOIN products p ON p.category_id = c.id
                 GROUP BY c.id, c.name, c.slug ORDER BY c.name'
            )->fetchAll();
        }
        return $this->db->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();
    }

    public function createCategory(string $name, string $slug): void
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
        $stmt->execute([$name, $slug]);
    }

    public function updateCategory(int $categoryId, string $name, string $slug): void
    {
        $stmt = $this->db->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?');
        $stmt->execute([$name, $slug, $categoryId]);
    }

    public function deleteCategory(int $categoryId): void
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
    }

    public function getFeaturedProducts(int $limit): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p JOIN categories c ON c.id = p.category_id
             WHERE p.featured = 1 ORDER BY p.id DESC LIMIT :limit'
        );
        $stmt->bindValue(':limit', max(1, min($limit, 24)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function searchProducts(string $search, string $category, string $sort): array
    {
        $orders = [
            'newest' => 'p.id DESC',
            'price_asc' => 'p.price ASC, p.id DESC',
            'price_desc' => 'p.price DESC, p.id DESC',
            'name_asc' => 'p.name ASC, p.id DESC',
        ];
        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (p.name LIKE :name OR p.description LIKE :description)';
            $params['name'] = '%' . $search . '%';
            $params['description'] = '%' . $search . '%';
        }
        if ($category !== '') {
            $sql .= ' AND c.slug = :category';
            $params['category'] = $category;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY ' . ($orders[$sort] ?? $orders['newest']));
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getProductById(int $productId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ?'
        );
        $stmt->execute([$productId]);
        return $stmt->fetch() ?: null;
    }

    public function getManagedProduct(int $productId, ?int $sellerId): ?array
    {
        $sql = 'SELECT * FROM products WHERE id = ?';
        $params = [$productId];
        if ($sellerId !== null) {
            $sql .= ' AND seller_id = ?';
            $params[] = $sellerId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function productSlugExists(string $slug, ?int $excludeProductId): bool
    {
        $sql = 'SELECT 1 FROM products WHERE slug = ?';
        $params = [$slug];
        if ($excludeProductId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeProductId;
        }
        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    public function saveProduct(?int $productId, ?int $sellerId, array $product): void
    {
        if ($productId === null) {
            $stmt = $this->db->prepare(
                'INSERT INTO products (category_id, seller_id, name, slug, description, price, stock, image, featured)
                 VALUES (:category_id, :seller_id, :name, :slug, :description, :price, :stock, :image, :featured)'
            );
            $stmt->execute($product + ['seller_id' => $sellerId]);
            return;
        }
        $sql = 'UPDATE products SET category_id = :category_id, name = :name, slug = :slug,
                description = :description, price = :price, stock = :stock, image = :image, featured = :featured
                WHERE id = :id';
        $params = $product + ['id' => $productId];
        if ($sellerId !== null) {
            $sql .= ' AND seller_id = :seller_id';
            $params['seller_id'] = $sellerId;
        }
        $this->db->prepare($sql)->execute($params);
    }

    public function deleteProduct(int $productId, ?int $sellerId): ?string
    {
        $product = $this->getManagedProduct($productId, $sellerId);
        if (!$product) {
            throw new InvalidArgumentException('O produto não foi encontrado ou não pertence a esta conta.');
        }
        $sql = 'DELETE FROM products WHERE id = ?';
        $params = [$productId];
        if ($sellerId !== null) {
            $sql .= ' AND seller_id = ?';
            $params[] = $sellerId;
        }
        $this->db->prepare($sql)->execute($params);
        return $product['image'];
    }

    public function isProductImageUsed(string $filename): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM products WHERE image = ? LIMIT 1');
        $stmt->execute([$filename]);
        return (bool)$stmt->fetchColumn();
    }

    public function listAdminProducts(): array
    {
        return $this->db->query(
            'SELECT p.id, p.name, p.price, p.stock, p.featured, c.name AS category_name, u.name AS seller_name
             FROM products p JOIN categories c ON c.id = p.category_id
             LEFT JOIN users u ON u.id = p.seller_id ORDER BY p.id DESC'
        )->fetchAll();
    }

    public function listSellerProducts(int $sellerId): array
    {
        $stmt = $this->db->prepare('SELECT id, name, price, stock FROM products WHERE seller_id = ? ORDER BY id DESC');
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public function getSellerSummary(int $sellerId): array
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS product_count, COALESCE(SUM(stock), 0) AS stock_count FROM products WHERE seller_id = ?');
        $stmt->execute([$sellerId]);
        return $stmt->fetch();
    }

    public function createOrder(int $userId, array $cart, array $address): int
    {
        $quantities = $this->normalizeCart($cart);
        $this->db->beginTransaction();
        try {
            $placeholders = implode(',', array_fill(0, count($quantities), '?'));
            $stmt = $this->db->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
            $stmt->execute(array_keys($quantities));
            $products = $stmt->fetchAll();
            if (count($products) !== count($quantities)) {
                throw new InvalidArgumentException('A product is no longer available.');
            }
            $totalCents = 0;
            foreach ($products as $product) {
                $quantity = $quantities[(int)$product['id']];
                if ($quantity > (int)$product['stock']) {
                    throw new InvalidArgumentException('Not enough stock for ' . $product['name']);
                }
                $totalCents += $this->priceToCents((string)$product['price']) * $quantity;
            }
            $order = $this->db->prepare('INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)');
            $order->execute([$userId, number_format($totalCents / 100, 2, '.', ''), 'pending']);
            $orderId = (int)$this->db->lastInsertId();
            $itemStmt = $this->db->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
            $stockStmt = $this->db->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($products as $product) {
                $productId = (int)$product['id'];
                $quantity = $quantities[$productId];
                $price = number_format($this->priceToCents((string)$product['price']) / 100, 2, '.', '');
                $itemStmt->execute([$orderId, $productId, $quantity, $price]);
                $stockStmt->execute([$quantity, $productId, $quantity]);
                if ($stockStmt->rowCount() !== 1) {
                    throw new RuntimeException('Stock changed. Please retry.');
                }
            }
            $addressStmt = $this->db->prepare(
                'INSERT INTO order_addresses (order_id, recipient_name, postal_code, street, number, complement, neighborhood, city, state)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $addressStmt->execute([
                $orderId,
                $address['recipient_name'],
                $address['postal_code'],
                $address['street'],
                $address['number'],
                $address['complement'],
                $address['neighborhood'],
                $address['city'],
                $address['state'],
            ]);
            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getOrderForUser(int $orderId, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, total, status FROM orders WHERE id = ? AND user_id = ?');
        $stmt->execute([$orderId, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function listAdminOrders(): array
    {
        return $this->db->query(
            'SELECT o.id, o.total, o.status, o.created_at, u.name AS customer_name, u.email AS customer_email,
             COUNT(oi.id) AS item_count FROM orders o JOIN users u ON u.id = o.user_id
             LEFT JOIN order_items oi ON oi.order_id = o.id
             GROUP BY o.id, u.name, u.email ORDER BY o.created_at DESC'
        )->fetchAll();
    }

    public function updateOrderStatus(int $orderId, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
    }

    private function normalizeCart(array $cart): array
    {
        if (!$cart || count($cart) > 50) {
            throw new InvalidArgumentException('Cart is empty or too large.');
        }
        $quantities = [];
        foreach ($cart as $entry) {
            if (!is_array($entry)) {
                throw new InvalidArgumentException('Invalid cart item.');
            }
            $productId = filter_var($entry['id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($entry['quantity'] ?? null, FILTER_VALIDATE_INT);
            if ($productId === false || $productId < 1 || $quantity === false || $quantity < 1 || $quantity > 99 || isset($quantities[$productId])) {
                throw new InvalidArgumentException('Invalid product or quantity.');
            }
            $quantities[$productId] = $quantity;
        }
        return $quantities;
    }

    private function priceToCents(string $price): int
    {
        if (!preg_match('/^(\d+)\.(\d{2})$/', $price, $matches)) {
            throw new RuntimeException('Invalid database price.');
        }
        return (int)$matches[1] * 100 + (int)$matches[2];
    }
}