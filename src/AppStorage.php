<?php
declare(strict_types=1);

interface AppStorage
{
    public function findUserByEmail(string $email): ?array;
    public function createCustomer(string $name, string $email, string $passwordHash): array;
    public function getUserRole(int $userId): ?string;
    public function listUsers(): array;
    public function updateUserRole(int $userId, string $role): void;
    public function getAdminDashboardCounts(): array;

    public function listCategories(bool $withProductCount = false): array;
    public function createCategory(string $name, string $slug): void;
    public function updateCategory(int $categoryId, string $name, string $slug): void;
    public function deleteCategory(int $categoryId): void;

    public function getFeaturedProducts(int $limit): array;
    public function searchProducts(string $search, string $category, string $sort): array;
    public function getProductById(int $productId): ?array;
    public function getManagedProduct(int $productId, ?int $sellerId): ?array;
    public function productSlugExists(string $slug, ?int $excludeProductId): bool;
    public function saveProduct(?int $productId, ?int $sellerId, array $product): void;
    public function deleteProduct(int $productId, ?int $sellerId): ?string;
    public function isProductImageUsed(string $filename): bool;
    public function listAdminProducts(): array;
    public function listSellerProducts(int $sellerId): array;
    public function getSellerSummary(int $sellerId): array;

    public function createOrder(int $userId, array $cart, array $address): int;
    public function getOrderForUser(int $orderId, int $userId): ?array;
    public function listAdminOrders(): array;
    public function updateOrderStatus(int $orderId, string $status): void;
}