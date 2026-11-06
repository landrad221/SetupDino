<?php
declare(strict_types=1);

final class ProductRepository
{
    public function __construct(private PDO $db) {}

    public function getFeaturedProducts(int $limit = 8): array
    {
        $stmt = $this->db->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE p.featured = 1 ORDER BY p.id DESC LIMIT :limit');
        $stmt->bindValue(':limit', max(1, min($limit, 24)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function search(string $search = '', string $category = '', string $sort = 'newest'): array
    {
        $orders = [
            'newest' => 'p.id DESC',
            'price_asc' => 'p.price ASC, p.id DESC',
            'price_desc' => 'p.price DESC, p.id DESC',
            'name_asc' => 'p.name ASC, p.id DESC',
        ];
        $order = $orders[$sort] ?? $orders['newest'];
        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1';
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
        $stmt = $this->db->prepare($sql . ' ORDER BY ' . $order);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAll(?string $category = null): array
    {
        return $this->search('', $category ?? '');
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getCategories(): array
    {
        return $this->db->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();
    }
}
