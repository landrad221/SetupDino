USE SetupDino;

ALTER TABLE users
    MODIFY role ENUM('customer', 'seller', 'admin') NOT NULL DEFAULT 'customer';

ALTER TABLE products
    ADD COLUMN seller_id BIGINT UNSIGNED NULL AFTER category_id,
    ADD CONSTRAINT fk_product_seller
        FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL;
