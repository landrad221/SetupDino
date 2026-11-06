<?php
declare(strict_types=1);
require_once __DIR__ . '/CartService.php';
final class OrderService {
    public function __construct(private PDO $db) {}
    public function create(int $userId, array $cart, array $address): int {
        $this->db->beginTransaction();
        try {
            $quote = (new CartService($this->db))->quote($cart);
            $stmt = $this->db->prepare('INSERT INTO orders (user_id,total,status) VALUES (?, ?, ?)');
            $stmt->execute([$userId, number_format($quote['total_cents']/100,2,'.',''), 'pending']);
            $orderId = (int)$this->db->lastInsertId();
            $itemStmt = $this->db->prepare('INSERT INTO order_items (order_id,product_id,quantity,unit_price) VALUES (?,?,?,?)');
            $stockStmt = $this->db->prepare('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?');
            foreach ($quote['items'] as $item) {
                $itemStmt->execute([$orderId,$item['id'],$item['quantity'],number_format($item['price_cents']/100,2,'.','')]);
                $stockStmt->execute([$item['quantity'],$item['id'],$item['quantity']]);
                if ($stockStmt->rowCount() !== 1) throw new RuntimeException('Stock changed. Please retry.');
            }
            $addr = $this->db->prepare('INSERT INTO order_addresses (order_id,recipient_name,postal_code,street,number,complement,neighborhood,city,state) VALUES (?,?,?,?,?,?,?,?,?)');
            $addr->execute([$orderId,$address['recipient_name'],$address['postal_code'],$address['street'],$address['number'],$address['complement'],$address['neighborhood'],$address['city'],$address['state']]);
            $this->db->commit(); return $orderId;
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
    }
}
