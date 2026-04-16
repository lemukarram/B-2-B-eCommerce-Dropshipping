<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Setting;
use App\Services\SlugService;
use Core\Database;
use RuntimeException;

/**
 * Parses CSV or XLSX bulk product upload files and batch-inserts products.
 *
 * Expected columns (order-insensitive, case-insensitive header matching):
 *   sku | title | category_slug | base_price | stock_quantity | description
 *
 * All rows are validated first. If any row has an error, nothing is inserted.
 */
class BulkUploadService
{
    private SlugService $slugService;

    public function __construct()
    {
        $this->slugService = new SlugService();
    }

    /**
     * Parse, validate, and insert products from a file.
     *
     * Returns ['inserted' => int, 'errors' => []]
     */
    public function process(string $filePath): array
    {
        $ext  = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $rows = match ($ext) {
            'csv'         => $this->parseCsv($filePath),
            'xlsx', 'xls' => $this->parseXlsx($filePath),
            default       => throw new RuntimeException("Unsupported file extension: {$ext}"),
        };

        $maxRows = (int) Setting::get('max_bulk_upload_rows', '500');
        if (count($rows) > $maxRows) {
            throw new RuntimeException("Upload exceeds maximum row limit of {$maxRows}.");
        }

        $categories = $this->buildCategoryMap();
        $errors     = [];
        $validated  = [];

        foreach ($rows as $lineNo => $row) {
            $line = $lineNo + 2; // +2: 1-based + header row
            $err  = $this->validateRow($row, $categories, $line);

            if ($err) {
                $errors[] = $err;
                continue;
            }

            $validated[] = $this->prepareRow($row, $categories);
        }

        if (!empty($errors)) {
            return ['inserted' => 0, 'errors' => $errors];
        }

        $inserted = $this->batchInsert($validated);
        @unlink($filePath);

        return ['inserted' => $inserted, 'errors' => []];
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new RuntimeException('Cannot open CSV file.');
        }

        $headers = null;
        $rows    = [];

        while (($line = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map(fn($h) => strtolower(trim($h)), $line);
                continue;
            }

            if (count($line) !== count($headers)) {
                continue; // skip malformed rows
            }

            $rows[] = array_combine($headers, $line);
        }

        fclose($handle);
        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new RuntimeException('PhpSpreadsheet is required for XLSX uploads. Run: composer require phpoffice/phpspreadsheet');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $data        = $sheet->toArray(null, true, true, false);

        if (empty($data)) {
            return [];
        }

        $headers = array_map(fn($h) => strtolower(trim((string)$h)), array_shift($data));
        $rows    = [];

        foreach ($data as $line) {
            if (count($line) !== count($headers)) {
                continue;
            }
            $rows[] = array_combine($headers, array_map('strval', $line));
        }

        return $rows;
    }

    private function buildCategoryMap(): array
    {
        $all = Category::allActive();
        $map = [];
        foreach ($all as $cat) {
            $map[$cat['slug']] = $cat['id'];
        }
        return $map;
    }

    private function validateRow(array $row, array $categories, int $line): ?string
    {
        $required = ['sku', 'title', 'base_price'];

        foreach ($required as $col) {
            if (empty(trim((string)($row[$col] ?? '')))) {
                return "Row {$line}: '{$col}' is required.";
            }
        }

        $price = preg_replace('/[^0-9.]/', '', (string)$row['base_price']);
        if (filter_var($price, FILTER_VALIDATE_FLOAT) === false) {
            return "Row {$line}: 'base_price' must be a valid number.";
        }

        if ((float)$price < 0) {
            return "Row {$line}: 'base_price' cannot be negative.";
        }

        $catSlug = trim((string)($row['category_slug'] ?? ''));
        if (!empty($catSlug) && !isset($categories[$catSlug])) {
            return "Row {$line}: category_slug '{$catSlug}' not found.";
        }

        if (strlen(trim((string)$row['sku'])) > 100) {
            return "Row {$line}: 'sku' exceeds 100 characters.";
        }

        return null;
    }

    private function prepareRow(array $row, array $categories): array
    {
        $title = trim(strip_tags((string)$row['title']));
        $sku   = trim(strip_tags((string)$row['sku']));
        $price = (float)preg_replace('/[^0-9.]/', '', (string)$row['base_price']);
        $catSlug = trim((string)($row['category_slug'] ?? ''));

        return [
            'sku'            => $sku,
            'title'          => $title,
            'slug'           => $this->slugService->generate($title),
            'category_id'    => !empty($catSlug) ? $categories[$catSlug] : null,
            'base_price'     => number_format($price, 2, '.', ''),
            'stock_quantity' => isset($row['stock_quantity']) ? max(0, (int)$row['stock_quantity']) : 0,
            'description'    => trim((string)($row['description'] ?? '')),
            'is_active'      => 1,
        ];
    }

    private function batchInsert(array $rows): int
    {
        $pdo      = Database::getInstance();
        $chunkSize = 50;
        $inserted  = 0;

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            $pdo->beginTransaction();
            try {
                $placeholders = implode(',', array_fill(0, count($chunk), '(?,?,?,?,?,?,?,?)'));
                $stmt = $pdo->prepare(
                    "INSERT IGNORE INTO products
                     (sku, title, slug, category_id, base_price, stock_quantity, description, is_active)
                     VALUES {$placeholders}"
                );

                $values = [];
                foreach ($chunk as $row) {
                    $values[] = $row['sku'];
                    $values[] = $row['title'];
                    $values[] = $row['slug'];
                    $values[] = $row['category_id'];
                    $values[] = $row['base_price'];
                    $values[] = $row['stock_quantity'];
                    $values[] = $row['description'];
                    $values[] = $row['is_active'];
                }

                $stmt->execute($values);
                $inserted += $stmt->rowCount();
                $pdo->commit();
            } catch (\Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
        }

        return $inserted;
    }
}
