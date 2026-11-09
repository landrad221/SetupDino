<?php
declare(strict_types=1);

final class CsvStorage implements AppStorage
{
    private const COLUMNS = [
        'users' => ['id', 'name', 'email', 'password_hash', 'role', 'created_at'],
        'categories' => ['id', 'name', 'slug', 'created_at'],
        'products' => ['id', 'category_id', 'seller_id', 'name', 'slug', 'description', 'price', 'stock', 'image', 'featured', 'created_at'],
        'orders' => ['id', 'user_id', 'total', 'status', 'created_at'],
        'order_items' => ['id', 'order_id', 'product_id', 'quantity', 'unit_price'],
        'order_addresses' => ['id', 'order_id', 'recipient_name', 'postal_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state'],
    ];

    private array $tables = [];
    private bool $active = false;

    public function __construct(private string $directory)
    {
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Could not create the CSV storage directory.');
        }
        $this->access(function (): void {
            $hasSeedTables = is_file($this->path('categories')) || is_file($this->path('products'));
            if (!$hasSeedTables) {
                $this->seedCatalog();
                $this->writeTables();
            }
        });
    }

    public function findUserByEmail(string $email): ?array
    {
        return $this->access(function () use ($email): ?array {
            foreach ($this->tables['users'] as $user) {
                if (strcasecmp($user['email'], $email) === 0) {
                    return $user;
                }
            }
            return null;
        });
    }

    public function createCustomer(string $name, string $email, string $passwordHash): array
    {
        return $this->access(function () use ($name, $email, $passwordHash): array {
            foreach ($this->tables['users'] as $user) {
                if (strcasecmp($user['email'], $email) === 0) {
                    throw new PDOException('An account with this email already exists.');
                }
            }
            $hasAdministrator = count(array_filter($this->tables['users'], static fn(array $user): bool => $user['role'] === 'admin')) > 0;
            $role = $hasAdministrator ? 'customer' : 'admin';
            $id = $this->nextId('users');
            $this->tables['users'][] = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'password_hash' => $passwordHash,
                'role' => $role,
                'created_at' => $this->now(),
            ];
            return ['id' => $id, 'role' => $role];
        }, true);
    }

    public function getUserRole(int $userId): ?string
    {
        return $this->access(function () use ($userId): ?string {
            foreach ($this->tables['users'] as $user) {
                if ((int)$user['id'] === $userId) {
                    return $user['role'];
                }
            }
            return null;
        });
    }

    public function listUsers(): array
    {
        return $this->access(function (): array {
            $users = $this->tables['users'];
            usort($users, static fn(array $left, array $right): int => strcmp($right['created_at'], $left['created_at']));
            return array_map(static fn(array $user): array => [
                'id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'],
                'role' => $user['role'], 'created_at' => $user['created_at'],
            ], $users);
        });
    }

    public function updateUserRole(int $userId, string $role): void
    {
        $this->access(function () use ($userId, $role): void {
            foreach ($this->tables['users'] as &$user) {
                if ((int)$user['id'] === $userId && $user['role'] !== 'admin') {
                    $user['role'] = $role;
                    break;
                }
            }
            unset($user);
        }, true);
    }

    public function getAdminDashboardCounts(): array
    {
        return $this->access(function (): array {
            $counts = ['products' => count($this->tables['products']), 'orders' => count($this->tables['orders']), 'sellers' => 0, 'customers' => 0];
            foreach ($this->tables['users'] as $user) {
                if ($user['role'] === 'seller') {
                    $counts['sellers']++;
                } elseif ($user['role'] === 'customer') {
                    $counts['customers']++;
                }
            }
            return $counts;
        });
    }

    public function listCategories(bool $withProductCount = false): array
    {
        return $this->access(function () use ($withProductCount): array {
            $categories = $this->tables['categories'];
            foreach ($categories as &$category) {
                if ($withProductCount) {
                    $category['product_count'] = count(array_filter(
                        $this->tables['products'],
                        static fn(array $product): bool => (int)$product['category_id'] === (int)$category['id']
                    ));
                }
            }
            unset($category);
            usort($categories, static fn(array $left, array $right): int => strcasecmp($left['name'], $right['name']));
            return array_map(static function (array $category) use ($withProductCount): array {
                $row = ['id' => $category['id'], 'name' => $category['name'], 'slug' => $category['slug']];
                if ($withProductCount) {
                    $row['product_count'] = $category['product_count'];
                }
                return $row;
            }, $categories);
        });
    }

    public function createCategory(string $name, string $slug): void
    {
        $this->access(function () use ($name, $slug): void {
            $this->assertUniqueCategorySlug($slug);
            $this->tables['categories'][] = ['id' => $this->nextId('categories'), 'name' => $name, 'slug' => $slug, 'created_at' => $this->now()];
        }, true);
    }

    public function updateCategory(int $categoryId, string $name, string $slug): void
    {
        $this->access(function () use ($categoryId, $name, $slug): void {
            $this->assertUniqueCategorySlug($slug, $categoryId);
            foreach ($this->tables['categories'] as &$category) {
                if ((int)$category['id'] === $categoryId) {
                    $category['name'] = $name;
                    $category['slug'] = $slug;
                    break;
                }
            }
            unset($category);
        }, true);
    }

    public function deleteCategory(int $categoryId): void
    {
        $this->access(function () use ($categoryId): void {
            foreach ($this->tables['products'] as $product) {
                if ((int)$product['category_id'] === $categoryId) {
                    throw new PDOException('Category is still referenced by a product.');
                }
            }
            $this->tables['categories'] = array_values(array_filter(
                $this->tables['categories'],
                static fn(array $category): bool => (int)$category['id'] !== $categoryId
            ));
        }, true);
    }

    public function getFeaturedProducts(int $limit): array
    {
        return $this->access(function () use ($limit): array {
            $products = array_values(array_filter($this->tables['products'], static fn(array $product): bool => (int)$product['featured'] === 1));
            usort($products, static fn(array $left, array $right): int => (int)$right['id'] <=> (int)$left['id']);
            return array_map(fn(array $product): array => $this->joinCategory($product), array_slice($products, 0, max(1, min($limit, 24))));
        });
    }

    public function searchProducts(string $search, string $category, string $sort): array
    {
        return $this->access(function () use ($search, $category, $sort): array {
            $products = [];
            foreach ($this->tables['products'] as $product) {
                $categoryRow = $this->findRow('categories', (int)$product['category_id']);
                if (!$categoryRow || ($category !== '' && $categoryRow['slug'] !== $category)) {
                    continue;
                }
                if ($search !== '' && mb_stripos($product['name'] . ' ' . $product['description'], $search) === false) {
                    continue;
                }
                $products[] = $this->joinCategory($product, $categoryRow);
            }
            usort($products, static function (array $left, array $right) use ($sort): int {
                $byId = (int)$right['id'] <=> (int)$left['id'];
                return match ($sort) {
                    'price_asc' => ((float)$left['price'] <=> (float)$right['price']) ?: $byId,
                    'price_desc' => ((float)$right['price'] <=> (float)$left['price']) ?: $byId,
                    'name_asc' => strcasecmp($left['name'], $right['name']) ?: $byId,
                    default => $byId,
                };
            });
            return $products;
        });
    }

    public function getProductById(int $productId): ?array
    {
        return $this->access(function () use ($productId): ?array {
            $product = $this->findRow('products', $productId);
            return $product ? $this->joinCategory($product) : null;
        });
    }

    public function getManagedProduct(int $productId, ?int $sellerId): ?array
    {
        return $this->access(function () use ($productId, $sellerId): ?array {
            $product = $this->findRow('products', $productId);
            if (!$product || ($sellerId !== null && (int)($product['seller_id'] ?? 0) !== $sellerId)) {
                return null;
            }
            return $product;
        });
    }

    public function productSlugExists(string $slug, ?int $excludeProductId): bool
    {
        return $this->access(function () use ($slug, $excludeProductId): bool {
            foreach ($this->tables['products'] as $product) {
                if ($product['slug'] === $slug && (int)$product['id'] !== $excludeProductId) {
                    return true;
                }
            }
            return false;
        });
    }

    public function saveProduct(?int $productId, ?int $sellerId, array $product): void
    {
        $this->access(function () use ($productId, $sellerId, $product): void {
            if (!$this->findRow('categories', (int)$product['category_id'])) {
                throw new InvalidArgumentException('A categoria selecionada não existe.');
            }
            foreach ($this->tables['products'] as $existing) {
                if ($existing['slug'] === $product['slug'] && (int)$existing['id'] !== $productId) {
                    throw new PDOException('Duplicate product slug.');
                }
            }
            $product['price'] = number_format((float)$product['price'], 2, '.', '');
            if ($productId === null) {
                $product['seller_id'] = $sellerId;
                $product['id'] = $this->nextId('products');
                $product['created_at'] = $this->now();
                $this->tables['products'][] = $product;
                return;
            }
            foreach ($this->tables['products'] as &$existing) {
                if ((int)$existing['id'] === $productId && ($sellerId === null || (int)($existing['seller_id'] ?? 0) === $sellerId)) {
                    $existing = array_merge($existing, $product, ['id' => $productId]);
                    break;
                }
            }
            unset($existing);
        }, true);
    }

    public function deleteProduct(int $productId, ?int $sellerId): ?string
    {
        return $this->access(function () use ($productId, $sellerId): ?string {
            $product = $this->findRow('products', $productId);
            if (!$product || ($sellerId !== null && (int)($product['seller_id'] ?? 0) !== $sellerId)) {
                throw new InvalidArgumentException('O produto não foi encontrado ou não pertence a esta conta.');
            }
            foreach ($this->tables['order_items'] as $item) {
                if ((int)$item['product_id'] === $productId) {
                    throw new PDOException('Product is referenced by an order.');
                }
            }
            $this->tables['products'] = array_values(array_filter(
                $this->tables['products'],
                static fn(array $row): bool => (int)$row['id'] !== $productId
            ));
            return $product['image'];
        }, true);
    }

    public function isProductImageUsed(string $filename): bool
    {
        return $this->access(function () use ($filename): bool {
            foreach ($this->tables['products'] as $product) {
                if (($product['image'] ?? '') === $filename) {
                    return true;
                }
            }
            return false;
        });
    }

    public function listAdminProducts(): array
    {
        return $this->access(function (): array {
            $products = [];
            foreach ($this->tables['products'] as $product) {
                $category = $this->findRow('categories', (int)$product['category_id']);
                if (!$category) {
                    continue;
                }
                $seller = isset($product['seller_id']) ? $this->findRow('users', (int)$product['seller_id']) : null;
                $products[] = [
                    'id' => $product['id'], 'name' => $product['name'], 'price' => $product['price'],
                    'stock' => $product['stock'], 'featured' => $product['featured'],
                    'category_name' => $category['name'], 'seller_name' => $seller['name'] ?? null,
                ];
            }
            usort($products, static fn(array $left, array $right): int => (int)$right['id'] <=> (int)$left['id']);
            return $products;
        });
    }

    public function listSellerProducts(int $sellerId): array
    {
        return $this->access(function () use ($sellerId): array {
            $products = array_values(array_filter(
                $this->tables['products'],
                static fn(array $product): bool => (int)($product['seller_id'] ?? 0) === $sellerId
            ));
            usort($products, static fn(array $left, array $right): int => (int)$right['id'] <=> (int)$left['id']);
            return array_map(static fn(array $product): array => [
                'id' => $product['id'], 'name' => $product['name'], 'price' => $product['price'], 'stock' => $product['stock'],
            ], $products);
        });
    }

    public function getSellerSummary(int $sellerId): array
    {
        return $this->access(function () use ($sellerId): array {
            $products = array_filter(
                $this->tables['products'],
                static fn(array $product): bool => (int)($product['seller_id'] ?? 0) === $sellerId
            );
            return ['product_count' => count($products), 'stock_count' => array_sum(array_map(static fn(array $product): int => (int)$product['stock'], $products))];
        });
    }

    public function createOrder(int $userId, array $cart, array $address): int
    {
        $quantities = $this->normalizeCart($cart);
        return $this->access(function () use ($userId, $quantities, $address): int {
            if (!$this->findRow('users', $userId)) {
                throw new InvalidArgumentException('Customer account not found.');
            }
            $products = [];
            foreach ($quantities as $productId => $quantity) {
                $product = $this->findRow('products', (int)$productId);
                if (!$product) {
                    throw new InvalidArgumentException('A product is no longer available.');
                }
                if ($quantity > (int)$product['stock']) {
                    throw new InvalidArgumentException('Not enough stock for ' . $product['name']);
                }
                $products[] = $product;
            }
            $totalCents = 0;
            foreach ($products as $product) {
                $totalCents += $this->priceToCents((string)$product['price']) * $quantities[(int)$product['id']];
            }
            $orderId = $this->nextId('orders');
            $this->tables['orders'][] = [
                'id' => $orderId, 'user_id' => $userId, 'total' => number_format($totalCents / 100, 2, '.', ''),
                'status' => 'pending', 'created_at' => $this->now(),
            ];
            foreach ($products as $product) {
                $productId = (int)$product['id'];
                $quantity = $quantities[$productId];
                $this->tables['order_items'][] = [
                    'id' => $this->nextId('order_items'), 'order_id' => $orderId, 'product_id' => $productId,
                    'quantity' => $quantity, 'unit_price' => number_format($this->priceToCents((string)$product['price']) / 100, 2, '.', ''),
                ];
                foreach ($this->tables['products'] as &$storedProduct) {
                    if ((int)$storedProduct['id'] === $productId) {
                        $storedProduct['stock'] = (int)$storedProduct['stock'] - $quantity;
                        break;
                    }
                }
                unset($storedProduct);
            }
            $this->tables['order_addresses'][] = [
                'id' => $this->nextId('order_addresses'), 'order_id' => $orderId,
                'recipient_name' => $address['recipient_name'], 'postal_code' => $address['postal_code'],
                'street' => $address['street'], 'number' => $address['number'], 'complement' => $address['complement'],
                'neighborhood' => $address['neighborhood'], 'city' => $address['city'], 'state' => $address['state'],
            ];
            return $orderId;
        }, true);
    }

    public function getOrderForUser(int $orderId, int $userId): ?array
    {
        return $this->access(function () use ($orderId, $userId): ?array {
            foreach ($this->tables['orders'] as $order) {
                if ((int)$order['id'] === $orderId && (int)$order['user_id'] === $userId) {
                    return ['id' => $order['id'], 'total' => $order['total'], 'status' => $order['status']];
                }
            }
            return null;
        });
    }

    public function listAdminOrders(): array
    {
        return $this->access(function (): array {
            $orders = [];
            foreach ($this->tables['orders'] as $order) {
                $user = $this->findRow('users', (int)$order['user_id']);
                if (!$user) {
                    continue;
                }
                $orders[] = [
                    'id' => $order['id'], 'total' => $order['total'], 'status' => $order['status'],
                    'created_at' => $order['created_at'], 'customer_name' => $user['name'], 'customer_email' => $user['email'],
                    'item_count' => count(array_filter($this->tables['order_items'], static fn(array $item): bool => (int)$item['order_id'] === (int)$order['id'])),
                ];
            }
            usort($orders, static fn(array $left, array $right): int => strcmp($right['created_at'], $left['created_at']));
            return $orders;
        });
    }

    public function updateOrderStatus(int $orderId, string $status): void
    {
        $this->access(function () use ($orderId, $status): void {
            foreach ($this->tables['orders'] as &$order) {
                if ((int)$order['id'] === $orderId) {
                    $order['status'] = $status;
                    break;
                }
            }
            unset($order);
        }, true);
    }

    private function access(callable $operation, bool $persist = false): mixed
    {
        if ($this->active) {
            return $operation();
        }
        $lock = fopen($this->directory . DIRECTORY_SEPARATOR . '.lock', 'c+b');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            throw new RuntimeException('Could not lock the CSV storage.');
        }
        $this->active = true;
        try {
            $this->tables = $this->readTables();
            $result = $operation();
            if ($persist) {
                $this->writeTables();
            }
            return $result;
        } finally {
            $this->tables = [];
            $this->active = false;
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function readTables(): array
    {
        $tables = [];
        foreach (self::COLUMNS as $table => $columns) {
            $tables[$table] = $this->readTable($table, $columns);
        }
        return $tables;
    }

    private function readTable(string $table, array $columns): array
    {
        $path = $this->path($table);
        if (!is_file($path)) {
            return [];
        }
        $file = fopen($path, 'rb');
        if ($file === false) {
            throw new RuntimeException('Could not read CSV table: ' . $table);
        }
        try {
            $header = fgetcsv($file, null, ',', '"', '');
            if ($header === false) {
                return [];
            }
            $rows = [];
            while (($values = fgetcsv($file, null, ',', '"', '')) !== false) {
                if ($values === [null]) {
                    continue;
                }
                $values = array_pad($values, count($header), '');
                $row = array_combine($header, array_slice($values, 0, count($header)));
                if (!is_array($row)) {
                    continue;
                }
                foreach (['id', 'category_id', 'seller_id', 'user_id', 'stock', 'featured', 'order_id', 'product_id', 'quantity'] as $integerColumn) {
                    if (array_key_exists($integerColumn, $row)) {
                        $row[$integerColumn] = $row[$integerColumn] === '' ? null : (int)$row[$integerColumn];
                    }
                }
                foreach (['seller_id', 'description', 'image', 'complement'] as $nullableColumn) {
                    if (array_key_exists($nullableColumn, $row) && $row[$nullableColumn] === '') {
                        $row[$nullableColumn] = null;
                    }
                }
                $rows[] = $row;
            }
            return $rows;
        } finally {
            fclose($file);
        }
    }

    private function writeTables(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            $path = $this->path($table);
            $temporary = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';
            $file = fopen($temporary, 'wb');
            if ($file === false) {
                throw new RuntimeException('Could not write CSV table: ' . $table);
            }
            try {
                fputcsv($file, $columns, ',', '"', '');
                foreach ($this->tables[$table] as $row) {
                    fputcsv($file, array_map(static fn(string $column): mixed => $row[$column] ?? '', $columns), ',', '"', '');
                }
            } finally {
                fclose($file);
            }
            $backup = $path . '.bak';
            if (is_file($backup) && !unlink($backup)) {
                unlink($temporary);
                throw new RuntimeException('Could not replace the CSV backup file.');
            }
            if (is_file($path) && !rename($path, $backup)) {
                unlink($temporary);
                throw new RuntimeException('Could not replace CSV table: ' . $table);
            }
            if (!rename($temporary, $path)) {
                if (is_file($backup)) {
                    rename($backup, $path);
                }
                unlink($temporary);
                throw new RuntimeException('Could not save CSV table: ' . $table);
            }
            if (is_file($backup)) {
                unlink($backup);
            }
        }
    }

    private function seedCatalog(): void
    {
        $now = $this->now();
        $categories = [
            ['Keyboards', 'keyboards'], ['Mice', 'mice'], ['Headsets', 'headsets'],
            ['Gaming Chairs', 'gaming-chairs'], ['Monitors', 'monitors'], ['Accessories', 'accessories'],
        ];
        foreach ($categories as $index => [$name, $slug]) {
            $this->tables['categories'][] = ['id' => $index + 1, 'name' => $name, 'slug' => $slug, 'created_at' => $now];
        }
        $this->tables['products'] = [
            ['id' => 1, 'category_id' => 1, 'seller_id' => null, 'name' => 'RGB Mechanical Keyboard', 'slug' => 'rgb-mechanical-keyboard', 'description' => 'Mechanical gaming keyboard with RGB lighting.', 'price' => '249.90', 'stock' => 15, 'image' => 'keyboard.jpg', 'featured' => 1, 'created_at' => $now],
            ['id' => 2, 'category_id' => 2, 'seller_id' => null, 'name' => 'Gamer Pro Mouse', 'slug' => 'gamer-pro-mouse', 'description' => 'Ergonomic gaming mouse with adjustable DPI.', 'price' => '129.90', 'stock' => 30, 'image' => 'mouse.jpg', 'featured' => 1, 'created_at' => $now],
            ['id' => 3, 'category_id' => 3, 'seller_id' => null, 'name' => 'Gamer X1 Headset', 'slug' => 'gamer-x1-headset', 'description' => 'Surround sound headset with microphone.', 'price' => '299.90', 'stock' => 12, 'image' => 'headset.jpg', 'featured' => 1, 'created_at' => $now],
            ['id' => 4, 'category_id' => 4, 'seller_id' => null, 'name' => 'Cyber Gaming Chair', 'slug' => 'cyber-gaming-chair', 'description' => 'Adjustable ergonomic chair for long sessions.', 'price' => '1199.90', 'stock' => 8, 'image' => 'chair.jpg', 'featured' => 1, 'created_at' => $now],
        ];
    }

    private function assertUniqueCategorySlug(string $slug, ?int $excludeId = null): void
    {
        foreach ($this->tables['categories'] as $category) {
            if ($category['slug'] === $slug && (int)$category['id'] !== $excludeId) {
                throw new PDOException('Duplicate category slug.');
            }
        }
    }

    private function joinCategory(array $product, ?array $category = null): array
    {
        $category ??= $this->findRow('categories', (int)$product['category_id']);
        if (!$category) {
            return $product;
        }
        $product['category_name'] = $category['name'];
        $product['category_slug'] = $category['slug'];
        return $product;
    }

    private function findRow(string $table, int $id): ?array
    {
        foreach ($this->tables[$table] as $row) {
            if ((int)$row['id'] === $id) {
                return $row;
            }
        }
        return null;
    }

    private function nextId(string $table): int
    {
        $maximum = 0;
        foreach ($this->tables[$table] as $row) {
            $maximum = max($maximum, (int)$row['id']);
        }
        return $maximum + 1;
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
            throw new RuntimeException('Invalid CSV price.');
        }
        return (int)$matches[1] * 100 + (int)$matches[2];
    }

    private function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    private function path(string $table): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $table . '.csv';
    }
}