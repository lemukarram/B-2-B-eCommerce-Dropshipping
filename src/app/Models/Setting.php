<?php

declare(strict_types=1);

namespace App\Models;

class Setting extends BaseModel
{
    protected static string $table = 'settings';

    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $stmt = static::query(
            'SELECT value FROM settings WHERE `key` = ? LIMIT 1',
            [$key]
        );
        $row = $stmt->fetch();

        if ($row === false) {
            return $default;
        }

        self::$cache[$key] = $row['value'];
        return $row['value'];
    }

    public static function set(string $key, string $value, ?int $updatedBy = null): void
    {
        static::query(
            'INSERT INTO settings (`key`, `value`, `updated_by`)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_by` = VALUES(`updated_by`)',
            [$key, $value, $updatedBy]
        );
        self::$cache[$key] = $value;
    }

    public static function allAsArray(): array
    {
        $rows   = static::query('SELECT `key`, `value`, `description` FROM settings')->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row;
        }
        return $result;
    }

    public static function deliveryCharge(): string
    {
        return self::get('default_delivery_charge', '200.00');
    }
}
