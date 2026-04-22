<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Services\SlugService;
use Core\Database;
use Core\Session;
use RuntimeException;

class CzImportService
{
    private SlugService $slugService;

    public function __construct()
    {
        $this->slugService = new SlugService();
    }

    /**
     * Process the CZ Excel file.
     * 
     * @param string $filePath Path to .xlsx file
     * @param string $reference Category reference (e.g. '34')
     * @param string|null $categoryName Optional name if category needs creating
     * @param int|null $categoryId Optional existing category ID to assign
     * @return array [inserted, updated, errors]
     */
    public function process(string $filePath, string $reference, ?string $categoryName = null, ?int $categoryId = null): array
    {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        try {
            $rows = $this->parseXlsx($filePath);
        } catch (\Throwable $e) {
            return ['errors' => ["Excel Parsing Error: " . $e->getMessage()]];
        }

        if (empty($rows)) {
            return ['errors' => ["The Excel file contains no valid data rows."]];
        }

        $pdo = Database::getInstance();
        
        // 1. Resolve Category
        $category = null;
        if ($categoryId) {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            $category = $stmt->fetch();
        }

        if (!$category) {
            $category = Category::findByReference($reference);
        }

        if (!$category) {
            if (empty($categoryName)) {
                return ['missing_category' => true, 'reference' => $reference];
            }
            // Create Category
            $pdo->prepare("INSERT INTO categories (name, slug, reference) VALUES (?, ?, ?)")
                ->execute([$categoryName, $this->slugService->generate($categoryName), $reference]);
            $categoryId = (int)$pdo->lastInsertId();
        } else {
            $categoryId = (int)$category['id'];
            // If category exists but has no reference, or different reference, update it
            if (($category['reference'] ?? '') !== $reference) {
                $pdo->prepare("UPDATE categories SET reference = ? WHERE id = ?")
                    ->execute([$reference, $categoryId]);
            }
        }

        $stats = ['inserted' => 0, 'updated' => 0, 'errors' => []];

        foreach ($rows as $index => $row) {
            try {
                $this->syncProduct($row, $categoryId, $stats);
            } catch (\Throwable $e) {
                $stats['errors'][] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        @unlink($filePath);
        return $stats;
    }

    private function syncProduct(array $row, int $categoryId, array &$stats): void
    {
        $pid = trim((string)($row['pid'] ?? ''));
        if (empty($pid)) throw new RuntimeException("Missing PID column.");

        // Clean name and description of potentially breaking characters
        $name        = $this->sanitizeText((string)($row['name'] ?? $row['product name'] ?? ''));
        $price       = (float)preg_replace('/[^0-9.]/', '', (string)($row['price'] ?? '0'));
        $description = $this->sanitizeText((string)($row['description'] ?? $row['descriptio'] ?? ''), true);
        $imageName   = trim((string)($row['image name'] ?? $row['local image filename'] ?? ''));

        if (empty($name)) throw new RuntimeException("Product name is empty after sanitization.");

        $product = Product::findByPid($pid);
        $pdo = Database::getInstance();

        if ($product) {
            // Update - keep buying price as buy_price
            $stmt = $pdo->prepare("UPDATE products SET 
                title = ?, buy_price = ?, description = ?, category_id = ?, updated_at = NOW() 
                WHERE id = ?");
            $stmt->execute([$name, $price, $description, $categoryId, $product['id']]);
            $productId = (int)$product['id'];
            $stats['updated']++;
        } else {
            // Insert
            $slug = $this->slugService->generate($name);
            $sku  = 'CZ-' . $pid;
            
            $stmt = $pdo->prepare("INSERT INTO products 
                (pid, sku, title, slug, description, buy_price, base_price, category_id, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
            // Initialize base_price same as buy_price until margin is applied
            $stmt->execute([$pid, $sku, $name, $slug, $description, $price, $price, $categoryId]);
            $productId = (int)$pdo->lastInsertId();
            $stats['inserted']++;
        }

        // Handle Image mapping
        if (!empty($imageName)) {
            $this->syncImage($productId, $imageName);
        }
    }

    private function syncImage(int $productId, string $imageName): void
    {
        $pdo = Database::getInstance();
        $path = 'products/' . $imageName;

        $stmt = $pdo->prepare("SELECT id FROM product_images WHERE product_id = ? AND image_path = ?");
        $stmt->execute([$productId, $path]);
        
        if (!$stmt->fetch()) {
            // Set all other images as not primary if this is new primary or first image
            $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, 1)")
                ->execute([$productId, $path]);
        }
    }

    private function sanitizeText(string $text, bool $keepHtml = false): string
    {
        // Remove null bytes and handle potential binary data
        $text = str_replace("\0", '', $text);
        
        // Convert encoding to UTF-8 and strip invalid characters
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        if (!$keepHtml) {
            $text = strip_tags($text);
        }

        return trim($text);
    }

    private function parseXlsx(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException("File not found at: " . $path);
        }

        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new RuntimeException('PhpSpreadsheet library is missing on server.');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        } catch (\Throwable $e) {
            throw new RuntimeException("Failed to load Excel file: " . $e->getMessage());
        }

        $sheet = $spreadsheet->getActiveSheet();
        $data  = $sheet->toArray(null, true, true, true);

        if (empty($data)) return [];

        $headers = array_map(fn($h) => strtolower(trim((string)$h)), array_shift($data));
        $rows    = [];

        $headerCount = count($headers);
        foreach ($data as $index => $row) {
            if (empty(array_filter($row))) continue;
            
            // Ensure row has exactly the same number of columns as headers
            $rowCount = count($row);
            if ($rowCount > $headerCount) {
                $row = array_slice($row, 0, $headerCount);
            } elseif ($rowCount < $headerCount) {
                $row = array_pad($row, $headerCount, '');
            }

            try {
                $rows[] = array_combine($headers, $row);
            } catch (\Throwable $e) {
                // Skip if still failing to combine
                continue;
            }
        }

        return $rows;
    }
}
