<?php
declare(strict_types=1);
/** Future step: checkout in a transaction, locking stock rows and persisting orders. */
class OrderService {
    public function __construct(private PDO $db) {}
}
