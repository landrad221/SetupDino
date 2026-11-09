<?php
declare(strict_types=1);
final class OrderService {
    public function __construct(private AppStorage $storage) {}
    public function create(int $userId, array $cart, array $address): int {
        return $this->storage->createOrder($userId, $cart, $address);
    }
}
