<?php

declare(strict_types=1);

namespace App\Models;

class Product extends BaseModel
{
    protected static string $table = 'products';

    public static function findBySlug(string $slug): ?array
    {
        $stmt = static::query(
            'SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.slug = ? AND p.is_active = 1
             LIMIT 1',
            [$slug]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBySku(string $sku): ?array
    {
        $stmt = static::query('SELECT * FROM products WHERE sku = ? LIMIT 1', [$sku]);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Paginated product list for admin — includes base_price.
     */
    public static function adminList(int $page = 1, int $perPage = 20, ?int $categoryId = null): array
    {
        $where    = $categoryId ? 'p.category_id = ?' : '1=1';
        $bindings = $categoryId ? [$categoryId] : [];
        $offset   = ($page - 1) * $perPage;

        $total = (int) static::query(
            "SELECT COUNT(*) FROM products p WHERE {$where}",
            $bindings
        )->fetchColumn();

        $rows = static::query(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE {$where}
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            [...$bindings, $perPage, $offset]
        )->fetchAll();

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Paginated product list for sellers — base_price visible, guest price hidden.
     */
    public static function sellerList(int $page = 1, int $perPage = 20, ?int $categoryId = null): array
    {
        $where    = $categoryId ? 'p.is_active = 1 AND p.category_id = ?' : 'p.is_active = 1';
        $bindings = $categoryId ? [$categoryId] : [];
        $offset   = ($page - 1) * $perPage;

        $total = (int) static::query(
            "SELECT COUNT(*) FROM products p WHERE {$where}",
            $bindings
        )->fetchColumn();

        $rows = static::query(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE {$where}
             ORDER BY p.title ASC
             LIMIT ? OFFSET ?",
            [...$bindings, $perPage, $offset]
        )->fetchAll();

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Guest product list — no base_price column returned.
     */
    public static function guestList(int $page = 1, int $perPage = 20, ?int $categoryId = null): array
    {
        $where    = $categoryId ? 'p.is_active = 1 AND p.category_id = ?' : 'p.is_active = 1';
        $bindings = $categoryId ? [$categoryId] : [];
        $offset   = ($page - 1) * $perPage;

        $total = (int) static::query(
            "SELECT COUNT(*) FROM products p WHERE {$where}",
            $bindings
        )->fetchColumn();

        $rows = static::query(
            "SELECT p.id, p.title, p.slug, p.description, p.stock_quantity, p.category_id,
                    c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE {$where}
             ORDER BY p.title ASC
             LIMIT ? OFFSET ?",
            [...$bindings, $perPage, $offset]
        )->fetchAll();

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Paginated product list for stores — shows wholesale_price based on seller markup.
     */
    public static function storeList(int $sellerId, int $page = 1, int $perPage = 20, ?int $categoryId = null): array
    {
        $where    = $categoryId ? 'p.is_active = 1 AND p.category_id = ?' : 'p.is_active = 1';
        $bindings = $categoryId ? [$categoryId] : [];
        $offset   = ($page - 1) * $perPage;

        $total = (int) static::query(
            "SELECT COUNT(*) FROM products p WHERE {$where}",
            $bindings
        )->fetchColumn();

        $rows = static::query(
            "SELECT p.*, c.name AS category_name,
                    scm.markup_type, scm.markup_value
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN seller_category_markups scm ON scm.category_id = p.category_id AND scm.seller_id = ?
             WHERE {$where}
             ORDER BY p.title ASC
             LIMIT ? OFFSET ?",
            [$sellerId, ...$bindings, $perPage, $offset]
        )->fetchAll();

        // Calculate final wholesale price for each
        foreach ($rows as &$row) {
            $row['wholesale_price'] = static::calculateWholesalePrice(
                (float)$row['base_price'],
                $row['markup_type'],
                (float)$row['markup_value']
            );
        }

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    public static function calculateWholesalePrice(float $base, ?string $type, float $val): float
    {
        if (!$type) return $base;
        return $type === 'fixed' ? $base + $val : $base * (1 + ($val / 100));
    }

    public static function images(int $productId): array
    {
        return static::query(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC',
            [$productId]
        )->fetchAll();
    }

    public static function primaryImage(int $productId): ?array
    {
        $stmt = static::query(
            'SELECT * FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1',
            [$productId]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get all active products for export purposes.
     */
    public static function allForExport(): array
    {
        return static::query(
            'SELECT p.*, c.name AS category_name, pi.image_path
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.is_active = 1
             ORDER BY p.title ASC'
        )->fetchAll();
    }
}
