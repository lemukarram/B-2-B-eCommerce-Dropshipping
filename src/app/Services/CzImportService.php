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
     * @return array [inserted, updated, errors]
     */
    public function process(string $filePath, string $reference, ?string $categoryName = null): array
    {
        $rows = $this->parseXlsx($filePath);
        if (empty($rows)) {
            throw new RuntimeException("Excel file is empty or malformed.");
        }

        $pdo = Database::getInstance();
        $category = Category::findByReference($reference);

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

        $name        = trim((string)($row['name'] ?? ''));
        $price       = (float)preg_replace('/[^0-9.]/', '', (string)($row['price'] ?? '0'));
        $description = $this->cleanDescription((string)($row['description'] ?? $row['descriptio'] ?? ''));
        $imageName   = trim((string)($row['image name'] ?? ''));

        $product = Product::findByPid($pid);
        $pdo = Database::getInstance();

        if ($product) {
            // Update
            $stmt = $pdo->prepare("UPDATE products SET 
                title = ?, base_price = ?, description = ?, category_id = ?, updated_at = NOW() 
                WHERE id = ?");
            $stmt->execute([$name, $price, $description, $categoryId, $product['id']]);
            $productId = (int)$product['id'];
            $stats['updated']++;
        } else {
            // Insert
            $slug = $this->slugService->generate($name);
            $sku  = 'CZ-' . $pid;
            
            $stmt = $pdo->prepare("INSERT INTO products 
                (pid, sku, title, slug, description, base_price, category_id, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$pid, $sku, $name, $slug, $description, $price, $categoryId]);
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

    private function cleanDescription(string $html): string
    {
        // Remove weird characters but keep HTML structure
        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
        // Optional: you can add more aggressive cleaning here if needed
        return trim($html);
    }

    private function parseXlsx(string $path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new RuntimeException('PhpSpreadsheet is missing on server.');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $data        = $sheet->toArray(null, true, true, true);

        if (empty($data)) return [];

        $headers = array_map(fn($h) => strtolower(trim((string)$h)), array_shift($data));
        $rows    = [];

        foreach ($data as $row) {
            if (empty(array_filter($row))) continue;
            $rows[] = array_combine($headers, $row);
        }

        return $rows;
    }
}
