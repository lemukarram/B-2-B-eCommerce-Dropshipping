<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\BulkUploadService;
use App\Services\FileUploadService;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\View;

class BulkUploadController
{
    private BulkUploadService $bulkService;
    private FileUploadService $uploader;

    public function __construct()
    {
        $this->bulkService = new BulkUploadService();
        $this->uploader    = new FileUploadService();
    }

    public function show(Request $request): void
    {
        View::render('admin/products/bulk_upload', [
            'errors'  => Session::errors(),
            'success' => Session::getFlash('success'),
            'result'  => Session::getFlash('bulk_result'),
        ], 'admin');
    }

    public function process(Request $request): void
    {
        if (!$request->hasFile('bulk_file')) {
            Session::flashErrors(['bulk_file' => ['Please select a CSV or XLSX file.']]);
            Response::redirect('/admin/products/bulk-upload');
        }

        try {
            $filePath = $this->uploader->uploadBulkFile($request->file('bulk_file'));
            $result   = $this->bulkService->process($filePath);

            if (!empty($result['errors'])) {
                Session::flashErrors(['rows' => $result['errors']]);
            } else {
                Session::flash('success', "Successfully imported {$result['inserted']} products.");
            }

            Session::flash('bulk_result', $result);
        } catch (\Throwable $e) {
            Session::flashErrors(['bulk_file' => [$e->getMessage()]]);
        }

        Response::redirect('/admin/products/bulk-upload');
    }
}
