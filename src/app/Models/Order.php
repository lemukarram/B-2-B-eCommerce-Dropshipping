<?php

declare(strict_types=1);

namespace App\Models;

class Order extends BaseModel
{
    protected static string $table = 'orders';

    public static function findByNumber(string $orderNumber): ?array
    {
        $stmt = static::query(
            'SELECT * FROM orders WHERE order_number = ? LIMIT 1',
            [$orderNumber]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function withUser(int $id): ?array
    {
        $stmt = static::query(
            'SELECT o.*, 
                    u.name AS user_name, u.email AS user_email, u.role AS user_role,
                    p.name AS seller_name, p.email AS seller_email
             FROM orders o
             JOIN users u ON u.id = o.user_id
             LEFT JOIN users p ON p.id = o.parent_seller_id
             WHERE o.id = ?
             LIMIT 1',
            [$id]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function itemsFor(int $orderId): array
    {
        return static::query(
            'SELECT oi.*, p.pid, c.reference as cat_reference, pi.image_path
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN product_images pi ON pi.product_id = oi.product_id AND pi.is_primary = 1
             WHERE oi.order_id = ? 
             ORDER BY oi.id ASC',
            [$orderId]
        )->fetchAll();
    }

    public static function forUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $total = (int) static::query(
            'SELECT COUNT(*) FROM orders WHERE user_id = ?',
            [$userId]
        )->fetchColumn();

        $rows = static::query(
            'SELECT * FROM orders WHERE user_id = ?
             ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$userId, $perPage, $offset]
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
     * Orders placed by a Seller's referrals (Stores).
     */
    public static function forReferrals(int $sellerId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $total = (int) static::query(
            'SELECT COUNT(*) FROM orders WHERE parent_seller_id = ?',
            [$sellerId]
        )->fetchColumn();

        $rows = static::query(
            'SELECT o.*, u.name AS store_name 
             FROM orders o
             JOIN users u ON u.id = o.user_id
             WHERE o.parent_seller_id = ?
             ORDER BY o.created_at DESC LIMIT ? OFFSET ?',
            [$sellerId, $perPage, $offset]
        )->fetchAll();

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    public static function adminList(int $page = 1, int $perPage = 20, ?string $status = null): array
    {
        $where    = $status ? "o.status = ?" : "1=1";
        $bindings = $status ? [$status] : [];
        $offset   = ($page - 1) * $perPage;

        $total = (int) static::query(
            "SELECT COUNT(*) FROM orders o WHERE {$where}",
            $bindings
        )->fetchColumn();

        $rows = static::query(
            "SELECT o.*, u.name AS user_name, u.email AS user_email, p.name AS seller_name
             FROM orders o
             JOIN users u ON u.id = o.user_id
             LEFT JOIN users p ON p.id = o.parent_seller_id
             WHERE {$where}
             ORDER BY o.created_at DESC
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
}
