<?php
declare(strict_types=1);
function escape(?string $value): string {return htmlspecialchars($value ?? '',ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8');}
function formatPrice(string|int|float $price): string {return 'R$ '.number_format((float)$price,2,',','.');}
function asset(string $path): string {return '/assets/'.ltrim($path,'/');}
function slugify(string $value, string $fallback = 'item'): string {
    $value = mb_strtolower($value);
    $value = strtr($value, ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a','å'=>'a','ç'=>'c','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ñ'=>'n','ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ý'=>'y']);
    $slug = strtolower(trim((string)preg_replace('/[^a-z0-9]+/', '-', $value), '-'));
    return $slug !== '' ? $slug : $fallback;
}
function renderErrorPage(): never {http_response_code(503);exit('Store temporarily unavailable. Please check the database configuration.');}
