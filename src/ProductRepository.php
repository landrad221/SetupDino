<?php
declare(strict_types=1);
class ProductRepository {
    public function __construct(private PDO $db) {}
    public function getFeaturedProducts(int $limit=8): array {
        $stmt=$this->db->prepare('SELECT id,name,slug,description,price,stock,image FROM products WHERE featured=1 ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':lim',$limit,PDO::PARAM_INT);$stmt->execute();return $stmt->fetchAll();
    }
    public function getAll(?string $category=null): array {
        $sql='SELECT p.*,c.name AS category_name,c.slug AS category_slug FROM products p JOIN categories c ON c.id=p.category_id';
        if ($category !== null && $category !== '') $sql.=' WHERE c.slug=:category';
        $sql.=' ORDER BY p.id DESC';$stmt=$this->db->prepare($sql);
        if ($category !== null && $category !== '') $stmt->bindValue(':category',$category);
        $stmt->execute();return $stmt->fetchAll();
    }
    public function getById(int $id): ?array {
        $stmt=$this->db->prepare('SELECT * FROM products WHERE id=:id');$stmt->execute(['id'=>$id]);return $stmt->fetch() ?: null;
    }
    public function getCategories(): array {return $this->db->query('SELECT * FROM categories ORDER BY name')->fetchAll();}
}
