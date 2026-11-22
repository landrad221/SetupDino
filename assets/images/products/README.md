# Imagens de produtos

Fotos JPG, PNG, WebP e GIF enviadas são armazenadas neste diretório por `includes/product-management.php`.

- Tamanho máximo do envio: 5 MB.
- Dimensões máximas: 10.000 por 10.000 pixels.
- Máximo de 40 milhões de pixels decodificados.
- Nomes são gerados aleatoriamente em hexadecimal com a extensão validada.
- Imagens órfãs são removidas após atualizações ou exclusões sem referências.

Mantenha este diretório gravável pelo processo PHP e sirva-o como conteúdo estático.
