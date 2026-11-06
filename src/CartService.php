<?php
declare(strict_types=1);
final class CartService {
    public function __construct(private PDO $db) {}
    /** @return array{items:array,total_cents:int} */
    public function quote(array $raw): array {
        if (!$raw || count($raw) > 50) throw new InvalidArgumentException('Cart is empty or too large.');
        $quantities = [];
        foreach ($raw as $entry) {
            if (!is_array($entry)) throw new InvalidArgumentException('Invalid cart item.');
            $id = filter_var($entry['id'] ?? null, FILTER_VALIDATE_INT);
            $qty = filter_var($entry['quantity'] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1 || $qty === false || $qty < 1 || $qty > 99 || isset($quantities[$id])) throw new InvalidArgumentException('Invalid product or quantity.');
            $quantities[$id] = $qty;
        }
        $ids = array_keys($quantities);
        $placeholders = implode(',', array_fill(0,count($ids),'?'));
        $stmt = $this->db->prepare("SELECT id,name,price,stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
        $stmt->execute($ids);
        $products = $stmt->fetchAll();
        if (count($products) !== count($ids)) throw new InvalidArgumentException('A product is no longer available.');
        $items = []; $total = 0;
        foreach ($products as $product) {
            $id = (int)$product['id']; $qty = $quantities[$id];
            if ($qty > (int)$product['stock']) throw new InvalidArgumentException('Not enough stock for ' . $product['name']);
            $priceCents = self::toCents((string)$product['price']);
            $total += $priceCents * $qty;
            $items[] = ['id'=>$id,'name'=>$product['name'],'quantity'=>$qty,'price_cents'=>$priceCents];
        }
        return ['items'=>$items,'total_cents'=>$total];
    }
    private static function toCents(string $price): int {
        if (!preg_match('/^(\d+)\.(\d{2})$/', $price, $m)) throw new RuntimeException('Invalid database price');
        return (int)$m[1]*100+(int)$m[2];
    }
}
