<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

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
            'errors'   => Session::errors(),
            'success'  => Session::getFlash('success'),
            'result'   => Session::getFlash('cz_result'),
            'pending'  => Session::getFlash('pending_import'),
        ], 'admin');
    }

    public function process(Request $request): void
    {
        // 1. Check if we are confirming a new category
        $categoryName = $request->input('category_name');
        $pending      = $request->input('pending_file');
        $reference    = $request->input('reference');

        if ($categoryName && $pending && $reference) {
            $this->executeImport(STORAGE_PATH . '/uploads/bulk/' . $pending, $reference, $categoryName);
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

            $filePath = $this->uploader->uploadBulkFile($file);
            $this->executeImport($filePath, $reference);

        } catch (\Throwable $e) {
            Session::flashErrors(['cz_file' => [$e->getMessage()]]);
            Response::redirect('/admin/products/cz-import');
        }
    }

    private function executeImport(string $filePath, string $reference, ?string $categoryName = null): void
    {
        $result = $this->czService->process($filePath, $reference, $categoryName);

        if (isset($result['missing_category'])) {
            // Move file to a temporary location if not already there, or just keep it in bulk
            // For simplicity, we assume FileUploadService already put it in uploads/bulk/
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
