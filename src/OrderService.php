<?php declare(strict_types=1);

// Serviço de domínio que delega a criação de pedidos ao armazenamento configurado.
/**
 * Cria pedidos sem acoplar regras de apresentação ao armazenamento.
 */
final class OrderService
{
    /**
     * Cria o serviço ligado ao armazenamento configurado.
     *
     * @param AppStorage $storage Implementação usada para persistir pedidos.
     */
    public function __construct(private AppStorage $storage) {}

    /**
     * Cria um pedido completo a partir de carrinho e endereço.
     *
     * @param int $userId Identificador do cliente autenticado.
     * @param array<int,array<string,mixed>> $cart Itens com id e quantity.
     * @param array<string,mixed> $address Dados de entrega validados pelo chamador.
     * @return int Identificador inteiro do pedido criado.
     */
    public function create(int $userId, array $cart, array $address): int
    {
        return $this->storage->createOrder($userId, $cart, $address);
    }
}
