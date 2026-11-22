<?php declare(strict_types=1);

// Funções pequenas de apresentação, URL e formatação usadas pela aplicação.
/**
 * Escapa um valor opcional para saída segura em HTML.
 *
 * @param string|null $value Valor a ser convertido.
 * @return string Texto escapado para uso em atributos e conteúdo HTML.
 */
function escape(?string $value): string
{
    return htmlspecialchars($value ?? "", ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

/**
 * Formata um preço numérico no padrão monetário brasileiro.
 *
 * @param string|int|float $price Valor monetário a ser formatado.
 * @return string Preço em reais, por exemplo R$ 199,90.
 */
function formatPrice(string|int|float $price): string
{
    return 'R$ ' . number_format((float) $price, 2, ",", ".");
}

/**
 * Converte o caminho relativo de um arquivo público em URL de assets.
 *
 * @param string $path Caminho relativo dentro de /assets.
 * @return string URL absoluta iniciada por /assets/.
 */
function asset(string $path): string
{
    return "/assets/" . ltrim($path, "/");
}

/**
 * Converte texto em um identificador URL seguro e sem acentos.
 *
 * @param string $value Texto original.
 * @param string $fallback Valor usado quando o texto não produz caracteres válidos.
 * @return string Slug em letras minúsculas separado por hífens.
 */
function slugify(string $value, string $fallback = "item"): string
{
    $value = mb_strtolower($value);
    $value = strtr($value, [
        "á" => "a",
        "à" => "a",
        "â" => "a",
        "ã" => "a",
        "ä" => "a",
        "å" => "a",
        "ç" => "c",
        "é" => "e",
        "è" => "e",
        "ê" => "e",
        "ë" => "e",
        "í" => "i",
        "ì" => "i",
        "î" => "i",
        "ï" => "i",
        "ñ" => "n",
        "ó" => "o",
        "ò" => "o",
        "ô" => "o",
        "õ" => "o",
        "ö" => "o",
        "ú" => "u",
        "ù" => "u",
        "û" => "u",
        "ü" => "u",
        "ý" => "y",
    ]);
    $slug = strtolower(
        trim((string) preg_replace("/[^a-z0-9]+/", "-", $value), "-"),
    );
    return $slug !== "" ? $slug : $fallback;
}

/**
 * Registra uma resposta HTTP temporariamente indisponível e encerra a execução.
 *
 * @return never A execução nunca retorna; o processo encerra no navegador.
 */
function renderErrorPage(): never
{
    http_response_code(503);
    exit(
        "Loja temporariamente indisponível. Verifique a configuração do armazenamento."
    );
}
