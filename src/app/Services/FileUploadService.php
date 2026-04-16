<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class FileUploadService
{
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    private const ALLOWED_IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    private const ALLOWED_BULK_MIMES = [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private const ALLOWED_BULK_EXTS = ['csv', 'xls', 'xlsx'];

    private const MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10 MB
    private const MAX_BULK_SIZE  = 10 * 1024 * 1024; // 10 MB

    /**
     * Validate and move a product/category image.
     * Returns the public-relative path (e.g. /uploads/products/uuid.jpg).
     */
    public function uploadImage(array $file, string $subdir = 'products'): string
    {
        $this->assertNoError($file);
        $this->assertSize($file, self::MAX_IMAGE_SIZE);
        $this->assertMime($file, self::ALLOWED_IMAGE_MIMES);
        $this->assertExtension($file, self::ALLOWED_IMAGE_EXTS);

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destDir  = PUBLIC_PATH . '/uploads/' . $subdir;
        $destPath = $destDir . '/' . $filename;

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        return '/uploads/' . $subdir . '/' . $filename;
    }

    /**
     * Validate and move a bulk upload file (CSV/XLSX).
     * Returns the absolute filesystem path for processing.
     */
    public function uploadBulkFile(array $file): string
    {
        $this->assertNoError($file);
        $this->assertSize($file, self::MAX_BULK_SIZE);
        $this->assertMime($file, self::ALLOWED_BULK_MIMES);
        $this->assertExtension($file, self::ALLOWED_BULK_EXTS);

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destDir  = PUBLIC_PATH . '/uploads/bulk';
        $destPath = $destDir . '/' . $filename;

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        return $destPath;
    }

    private function assertNoError(array $file): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload error code: ' . $file['error']);
        }
    }

    private function assertSize(array $file, int $maxBytes): void
    {
        if ($file['size'] > $maxBytes) {
            $mb = round($maxBytes / 1024 / 1024);
            throw new RuntimeException("File exceeds maximum size of {$mb} MB.");
        }
    }

    private function assertMime(array $file, array $allowed): void
    {
        // Use finfo on the actual temp file — never trust browser-supplied MIME
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowed, true)) {
            throw new RuntimeException("File type not allowed: {$mime}");
        }
    }

    private function assertExtension(array $file, array $allowed): void
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            throw new RuntimeException("File extension not allowed: .{$ext}");
        }
    }

    public function delete(string $relativePath): void
    {
        // relativePath is like /uploads/products/abc.jpg
        $abs = PUBLIC_PATH . $relativePath;
        if (is_file($abs)) {
            unlink($abs);
        }
    }
}
