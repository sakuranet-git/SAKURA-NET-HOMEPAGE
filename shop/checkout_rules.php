<?php
declare(strict_types=1);

const SHOP_CHECKOUT_ENABLED_CATEGORIES = ['サポート・保守'];

function shop_is_checkout_enabled_product(array $product): bool
{
    $category = trim((string) ($product['category'] ?? ''));

    return in_array($category, SHOP_CHECKOUT_ENABLED_CATEGORIES, true);
}

function shop_checkout_limited_message(): string
{
    return '現在オンライン決済はサポート・保守カテゴリのみ対応しています。機器・回線・その他商品はお問い合わせフォームよりご相談ください。';
}
