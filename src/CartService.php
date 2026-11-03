<?php
declare(strict_types=1);
/** Milestone 2 next step: validate cart quantities and calculate totals from database prices. */
class CartService {
    public function __construct(private PDO $db) {}
}
