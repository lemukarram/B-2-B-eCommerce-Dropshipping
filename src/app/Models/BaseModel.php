<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;
use PDO;
use PDOStatement;

/**
 * Thin active-record-style base model.
 *
 * All queries use prepared statements via Database::getInstance().
 * Child classes define $table and may override as needed.
 *
 * This is NOT an ORM — it is a convenience layer over PDO.
 * Complex queries are written explicitly in child models.
 */
abstract class BaseModel
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected static function db(): PDO
    {
        return Database::getInstance();
    }

    protected static function query(string $sql, array $bindings = []): PDOStatement
    {
        $stmt = static::db()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    public static function find(int $id): ?array
    {
        $stmt = static::query(
            'SELECT * FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = ? LIMIT 1',
            [$id]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findOrFail(int $id): array
    {
        $row = static::find($id);
        if ($row === null) {
            \Core\Response::abort(404, static::$table . ' not found.');
        }
        return $row;
    }

    public static function all(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        return static::query(
            'SELECT * FROM `' . static::$table . '` ORDER BY `' . $orderBy . '` ' . $dir
        )->fetchAll();
    }

    public static function insert(array $data): int
    {
        $cols        = array_keys($data);
        $placeholders = array_fill(0, count($cols), '?');
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            static::$table,
            implode('`, `', $cols),
            implode(', ', $placeholders)
        );
        static::query($sql, array_values($data));
        return (int) static::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $set = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        $sql = "UPDATE `" . static::$table . "` SET {$set} WHERE `" . static::$primaryKey . "` = ?";
        static::query($sql, [...array_values($data), $id]);
    }

    public static function delete(int $id): void
    {
        static::query(
            'DELETE FROM `' . static::$table . '` WHERE `' . static::$primaryKey . '` = ?',
            [$id]
        );
    }

    public static function paginate(int $page, int $perPage = 20, string $where = '', array $bindings = []): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $whereClause = $where ? "WHERE {$where}" : '';

        $total = (int) static::query(
            'SELECT COUNT(*) FROM `' . static::$table . "` {$whereClause}",
            $bindings
        )->fetchColumn();

        $rows = static::query(
            'SELECT * FROM `' . static::$table . "` {$whereClause} LIMIT ? OFFSET ?",
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
