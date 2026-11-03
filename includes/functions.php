<?php
declare(strict_types=1);
function escape(?string $value): string {return htmlspecialchars($value ?? '',ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8');}
function formatPrice(string|int|float $price): string {return 'R$ '.number_format((float)$price,2,',','.');}
function asset(string $path): string {return '/assets/'.ltrim($path,'/');}
function renderErrorPage(): never {http_response_code(503);exit('Store temporarily unavailable. Please check the database configuration.');}
