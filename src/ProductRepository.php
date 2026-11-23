<?php declare(strict_types=1);

// API de consulta de produtos para o catálogo público.
/**
 * Consulta produtos por meio da interface de armazenamento.
 */
final class ProductRepository
{
    /**
     * Cria o repositório com o armazenamento configurado.
     *
     * @param AppStorage $storage Implementação usada para consultar dados.
     */
    public function __construct(private AppStorage $storage) {}

    /**
     * Lista produtos marcados como destaques.
     *
     * @param int $limit Quantidade máxima, com padrão 8.
     * @return array<int,array<string,mixed>> Produtos encontrados.
     */
    public function getFeaturedProducts(int $limit = 8): array
    {
        return $this->withPromotions(
            $this->storage->getFeaturedProducts($limit),
        );
    }

    /**
     * Busca produtos aplicando texto, categoria e ordenação.
     *
     * @param string $search Texto procurado no nome ou descrição.
     * @param string $category Slug da categoria ou texto vazio.
     * @param string $sort Ordenação: newest, price_asc, price_desc ou name_asc.
     * @return array<int,array<string,mixed>> Produtos correspondentes.
     */
    public function search(
        string $search = "",
        string $category = "",
        string $sort = "newest",
    ): array {
        return $this->withPromotions(
            $this->storage->searchProducts($search, $category, $sort),
        );
    }

    /**
     * Lista produtos, opcionalmente filtrados por categoria.
     *
     * @param string|null $category Slug da categoria ou null.
     * @return array<int,array<string,mixed>> Produtos encontrados.
     */
    public function getAll(?string $category = null): array
    {
        return $this->search("", $category ?? "");
    }

    /**
     * Busca um produto pelo identificador.
     *
     * @param int $id Identificador inteiro positivo.
     * @return array<string,mixed>|null Dados do produto ou null.
     */
    public function getById(int $id): ?array
    {
        $product = $this->storage->getProductById($id);
        return $product === null
            ? null
            : ($this->withPromotions([$product])[0] ?? $product);
    }

    /**
     * Lista todas as categorias disponíveis.
     *
     * @return array<int,array<string,mixed>> Categorias encontradas.
     */
    public function getCategories(): array
    {
        return $this->storage->listCategories();
    }

    /**
     * Adiciona preços promocionais CSV sem alterar o contrato compartilhado.
     *
     * @param array<int,array<string,mixed>> $products Produtos a complementar.
     * @return array<int,array<string,mixed>> Produtos com promoções ativas.
     */
    private function withPromotions(array $products): array
    {
        if (!$this->storage instanceof CsvStorage || !$products) {
            return $products;
        }
        $prices = $this->storage->getPromotionPrices(
            array_column($products, "id"),
        );
        foreach ($products as &$product) {
            $product["promotion_price"] =
                $prices[(int) $product["id"]] ?? null;
        }
        unset($product);
        return $products;
    }
}
