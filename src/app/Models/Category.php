<?php

declare(strict_types=1);

namespace App\Models;

class Category extends BaseModel
{
    protected static string $table = 'categories';

    public static function findBySlug(string $slug): ?array
    {
        $stmt = static::query(
            'SELECT * FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1',
            [$slug]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function allActive(): array
    {
        return static::query(
            'SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC'
        )->fetchAll();
    }

    public static function topLevel(): array
    {
        return static::query(
            'SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1
             ORDER BY sort_order ASC, name ASC'
        )->fetchAll();
    }

    public static function children(int $parentId): array
    {
        return static::query(
            'SELECT * FROM categories WHERE parent_id = ? AND is_active = 1
             ORDER BY sort_order ASC, name ASC',
            [$parentId]
        )->fetchAll();
    }
}
