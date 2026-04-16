<?php

declare(strict_types=1);

namespace App\Services;

use Core\Database;

class SlugService
{
    /**
     * Generate a URL-safe slug from a string, guaranteed unique in $table.$column.
     *
     * Usage:
     *   $slug = $slugService->generate('My Product Title!', 'products', 'slug');
     */
    public function generate(string $text, string $table = 'products', string $column = 'slug'): string
    {
        $base = $this->slugify($text);
        $slug = $base;
        $i    = 1;

        $pdo = Database::getInstance();

        while (true) {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?"
            );
            $stmt->execute([$slug]);

            if ((int)$stmt->fetchColumn() === 0) {
                break;
            }

            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function slugify(string $text): string
    {
        // Transliterate unicode to ASCII
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?? $text;

        // Convert to lowercase
        $text = mb_strtolower($text, 'UTF-8');

        // Replace non-alphanumeric characters with hyphens
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Trim leading/trailing hyphens
        return trim($text, '-');
    }
}
