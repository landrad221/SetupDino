<?php
declare(strict_types=1);

<<<<<<< HEAD
function productSlug(PDO $db, string $name, ?int $productId): string
=======
function productSlug(AppStorage $storage, string $name, ?int $productId): string
>>>>>>> master
{
    $slug = slugify($name, 'product');

    $candidate = substr($slug, 0, 200);
    $suffix = 2;
    while (true) {
<<<<<<< HEAD
        $sql = 'SELECT id FROM products WHERE slug = :slug';
        $params = ['slug' => $candidate];
        if ($productId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $productId;
        }
        $stmt = $db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        if (!$stmt->fetchColumn()) {
=======
        if (!$storage->productSlugExists($candidate, $productId)) {
>>>>>>> master
            return $candidate;
        }
        $ending = '-' . $suffix++;
        $candidate = substr($slug, 0, 200 - strlen($ending)) . $ending;
    }
}

function receiveProductImage(array $upload): ?string
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($upload['error'] ?? null) !== UPLOAD_ERR_OK || !is_uploaded_file((string)($upload['tmp_name'] ?? ''))) {
        throw new InvalidArgumentException('A imagem não foi enviada corretamente.');
    }
    if (($upload['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('A imagem deve ter no máximo 5 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string)$upload['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!is_string($mime) || !isset($extensions[$mime])) {
        throw new InvalidArgumentException('Use uma imagem JPG, PNG, WebP ou GIF.');
    }
    $dimensions = getimagesize((string)$upload['tmp_name']);
    if ($dimensions === false || $dimensions[0] > 10000 || $dimensions[1] > 10000 || $dimensions[0] * $dimensions[1] > 40000000) {
        throw new InvalidArgumentException('A imagem tem dimensões inválidas ou muito grandes.');
    }

    $directory = dirname(__DIR__) . '/assets/images/products';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Não foi possível preparar o diretório de imagens.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file((string)$upload['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('Não foi possível salvar a imagem enviada.');
    }
    return $filename;
}

<<<<<<< HEAD
function removeUnusedProductImage(PDO $db, ?string $filename): void
=======
function removeUnusedProductImage(AppStorage $storage, ?string $filename): void
>>>>>>> master
{
    if ($filename === null || $filename === '') {
        return;
    }
    try {
<<<<<<< HEAD
        $stmt = $db->prepare('SELECT 1 FROM products WHERE image = ? LIMIT 1');
        $stmt->execute([$filename]);
        if ($stmt->fetchColumn()) {
            return;
        }
    } catch (PDOException $e) {
=======
        if ($storage->isProductImageUsed($filename)) {
            return;
        }
    } catch (Throwable $e) {
>>>>>>> master
        error_log('Unable to verify whether a product image is still referenced: ' . $e->getMessage());
        return;
    }
    $path = dirname(__DIR__) . '/assets/images/products/' . basename($filename);
    if (is_file($path) && !unlink($path)) {
        error_log('Unable to remove unused product image.');
    }
}

<<<<<<< HEAD
function deleteManagedProduct(PDO $db, int $productId, ?int $sellerId): void
{
    $sql = 'SELECT image FROM products WHERE id = ?';
    $params = [$productId];
    if ($sellerId !== null) {
        $sql .= ' AND seller_id = ?';
        $params[] = $sellerId;
    }
    $find = $db->prepare($sql);
    $find->execute($params);
    $product = $find->fetch();
    if (!$product) {
        throw new InvalidArgumentException('O produto não foi encontrado ou não pertence a esta conta.');
    }

    $deleteSql = 'DELETE FROM products WHERE id = ?' . ($sellerId === null ? '' : ' AND seller_id = ?');
    $deleteParams = $sellerId === null ? [$productId] : [$productId, $sellerId];
    $delete = $db->prepare($deleteSql);
    $delete->execute($deleteParams);
    if ($delete->rowCount() !== 1) {
        throw new InvalidArgumentException('O produto não foi encontrado ou não pertence a esta conta.');
    }
    removeUnusedProductImage($db, $product['image']);
}

function saveManagedProduct(PDO $db, int $actorId, bool $isAdmin, ?int $productId, array $input, array $upload): void
{
    $sql = 'SELECT id, image, featured FROM products WHERE id = :id';
    if (!$isAdmin) {
        $sql .= ' AND seller_id = :seller_id';
    }
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $productId ?? 0, PDO::PARAM_INT);
    if (!$isAdmin) {
        $stmt->bindValue(':seller_id', $actorId, PDO::PARAM_INT);
    }
    $stmt->execute();
    $existing = $productId === null ? null : $stmt->fetch();
=======
function deleteManagedProduct(AppStorage $storage, int $productId, ?int $sellerId): void
{
    $image = $storage->deleteProduct($productId, $sellerId);
    removeUnusedProductImage($storage, $image);
}

function saveManagedProduct(AppStorage $storage, int $actorId, bool $isAdmin, ?int $productId, array $input, array $upload): void
{
    $existing = $productId === null ? null : $storage->getManagedProduct($productId, $isAdmin ? null : $actorId);
>>>>>>> master
    if ($productId !== null && !$existing) {
        throw new InvalidArgumentException('O produto não foi encontrado ou não pertence a esta conta.');
    }

    $name = trim((string)($input['name'] ?? ''));
    $description = trim((string)($input['description'] ?? ''));
    $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT);
    $price = trim((string)($input['price'] ?? ''));
    $stock = filter_var($input['stock'] ?? null, FILTER_VALIDATE_INT);
    if ($name === '' || mb_strlen($name) > 180) {
        throw new InvalidArgumentException('Informe um nome com até 180 caracteres.');
    }
    if ($description === '' || mb_strlen($description) > 15000) {
        throw new InvalidArgumentException('Informe uma descrição com até 15.000 caracteres.');
    }
    if ($categoryId === false || $categoryId < 1) {
        throw new InvalidArgumentException('Selecione uma categoria válida.');
    }
    if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $price)) {
        throw new InvalidArgumentException('Informe um preço válido, com até duas casas decimais.');
    }
    if ($stock === false || $stock < 0 || $stock > 2147483647) {
        throw new InvalidArgumentException('Informe uma quantidade válida em estoque.');
    }
<<<<<<< HEAD
    $category = $db->prepare('SELECT id FROM categories WHERE id = ?');
    $category->execute([$categoryId]);
    if (!$category->fetchColumn()) {
        throw new InvalidArgumentException('A categoria selecionada não existe.');
    }

    $slug = productSlug($db, $name, $productId);
=======
    $categoryExists = false;
    foreach ($storage->listCategories() as $category) {
        if ((int)$category['id'] === $categoryId) {
            $categoryExists = true;
            break;
        }
    }
    if (!$categoryExists) {
        throw new InvalidArgumentException('A categoria selecionada não existe.');
    }

    $slug = productSlug($storage, $name, $productId);
>>>>>>> master
    $image = $existing['image'] ?? null;
    $removeImage = isset($input['remove_image']) && $input['remove_image'] === '1';
    $newImage = receiveProductImage($upload);
    if ($newImage !== null) {
        $image = $newImage;
    } elseif ($removeImage) {
        $image = null;
    }
    $featured = $isAdmin
        ? (isset($input['featured']) && $input['featured'] === '1' ? 1 : 0)
        : (int)($existing['featured'] ?? 0);

    try {
<<<<<<< HEAD
        if ($productId === null) {
            $insert = $db->prepare(
                'INSERT INTO products (category_id, seller_id, name, slug, description, price, stock, image, featured)
                 VALUES (:category_id, :seller_id, :name, :slug, :description, :price, :stock, :image, :featured)'
            );
            $insert->execute([
                'category_id' => $categoryId,
                'seller_id' => $isAdmin ? null : $actorId,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'image' => $image,
                'featured' => $featured,
            ]);
        } else {
            $update = $db->prepare(
                'UPDATE products SET category_id = :category_id, name = :name, slug = :slug,
                 description = :description, price = :price, stock = :stock, image = :image, featured = :featured
                 WHERE id = :id' . ($isAdmin ? '' : ' AND seller_id = :seller_id')
            );
            $params = [
                'category_id' => $categoryId,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'image' => $image,
                'featured' => $featured,
                'id' => $productId,
            ];
            if (!$isAdmin) {
                $params['seller_id'] = $actorId;
            }
            $update->execute($params);
        }
=======
        $storage->saveProduct($productId, $isAdmin ? null : $actorId, [
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'image' => $image,
            'featured' => $featured,
        ]);
>>>>>>> master
    } catch (Throwable $e) {
        if ($newImage !== null) {
            $uploadedPath = dirname(__DIR__) . '/assets/images/products/' . $newImage;
            if (is_file($uploadedPath) && !unlink($uploadedPath)) {
                error_log('Unable to remove uploaded product image after a failed save.');
            }
        }
        throw $e;
    }

    $oldImage = $existing['image'] ?? null;
    if ($oldImage && $oldImage !== $image) {
<<<<<<< HEAD
        removeUnusedProductImage($db, $oldImage);
=======
        removeUnusedProductImage($storage, $oldImage);
>>>>>>> master
    }
}
