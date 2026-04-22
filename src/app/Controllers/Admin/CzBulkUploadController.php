<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Services\CzImportService;
use App\Services\FileUploadService;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\View;

class CzBulkUploadController
{
    private CzImportService $czService;
    private FileUploadService $uploader;

    public function __construct()
    {
        $this->czService = new CzImportService();
        $this->uploader  = new FileUploadService();
    }

    public function show(Request $request): void
    {
        View::render('admin/products/cz_upload', [
            'categories' => Category::allActive(),
            'errors'     => Session::errors(),
            'success'    => Session::getFlash('success'),
            'result'     => Session::getFlash('cz_result'),
            'pending'    => Session::getFlash('pending_import'),
        ], 'admin');
    }

    public function process(Request $request): void
    {
        // 1. Check if we are confirming a new category or assigning existing
        $categoryName = $request->input('category_name');
        $categoryId   = $request->input('category_id') ? (int)$request->input('category_id') : null;
        $pending      = $request->input('pending_file');
        $reference    = $request->input('reference');

        if (($categoryName || $categoryId) && $pending && $reference) {
            $this->executeImport(STORAGE_PATH . '/uploads/bulk/' . $pending, $reference, $categoryName, $categoryId);
            return;
        }

        // 2. Standard Upload
        if (!$request->hasFile('cz_file')) {
            Session::flashErrors(['cz_file' => ['Please select an XLSX file.']]);
            Response::redirect('/admin/products/cz-import');
        }

        try {
            $file = $request->file('cz_file');
            $filename = pathinfo($file['name'], PATHINFO_FILENAME);
            $reference = $filename; // e.g. '34'
            
            $categoryId = $request->input('category_id') ? (int)$request->input('category_id') : null;

            $filePath = $this->uploader->uploadBulkFile($file);
            $this->executeImport($filePath, $reference, null, $categoryId);

        } catch (\Throwable $e) {
            Session::flashErrors(['cz_file' => [$e->getMessage()]]);
            Response::redirect('/admin/products/cz-import');
        }
    }

    private function executeImport(string $filePath, string $reference, ?string $categoryName = null, ?int $categoryId = null): void
    {
        $result = $this->czService->process($filePath, $reference, $categoryName, $categoryId);

        if (isset($result['missing_category'])) {
            Session::flash('pending_import', [
                'file'      => basename($filePath),
                'reference' => $reference
            ]);
            Response::redirect('/admin/products/cz-import');
        }

        if (!empty($result['errors'])) {
            Session::flashErrors(['rows' => $result['errors']]);
        } else {
            $msg = "Import Complete: {$result['inserted']} new, {$result['updated']} updated.";
            Session::flash('success', $msg);
        }

        Session::flash('cz_result', $result);
        Response::redirect('/admin/products/cz-import');
    }
}
