<?php

declare(strict_types=1);

namespace App\Models;

class CategoryMarkup extends BaseModel
{
    protected static string $table = 'seller_category_markups';

    public static function forSeller(int $sellerId): array
    {
        return static::query(
            "SELECT scm.*, c.name AS category_name 
             FROM seller_category_markups scm
             JOIN categories c ON c.id = scm.category_id
             WHERE scm.seller_id = ?",
            [$sellerId]
        )->fetchAll();
    }

    public static function setMarkup(int $sellerId, int $categoryId, string $type, float $value): void
    {
        $existing = static::query(
            "SELECT id FROM seller_category_markups WHERE seller_id = ? AND category_id = ?",
            [$sellerId, $categoryId]
        )->fetch();

        if ($existing) {
            static::update((int)$existing['id'], [
                'markup_type'  => $type,
                'markup_value' => $value
            ]);
        } else {
            static::insert([
                'seller_id'    => $sellerId,
                'category_id'  => $categoryId,
                'markup_type'  => $type,
                'markup_value' => $value
            ]);
        }
    }
}
