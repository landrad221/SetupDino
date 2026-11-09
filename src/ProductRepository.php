<?php
declare(strict_types=1);

final class ProductRepository
{
    public function __construct(private AppStorage $storage) {}

    public function getFeaturedProducts(int $limit = 8): array
    {
        return $this->storage->getFeaturedProducts($limit);
    }

    public function search(string $search = '', string $category = '', string $sort = 'newest'): array
    {
        return $this->storage->searchProducts($search, $category, $sort);
    }

    public function getAll(?string $category = null): array
    {
        return $this->search('', $category ?? '');
    }

    public function getById(int $id): ?array
    {
        return $this->storage->getProductById($id);
    }

    public function getCategories(): array
    {
        return $this->storage->listCategories();
    }
}
