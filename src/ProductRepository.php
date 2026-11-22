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
        return $this->storage->getFeaturedProducts($limit);
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
        return $this->storage->searchProducts($search, $category, $sort);
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
        return $this->storage->getProductById($id);
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
}
