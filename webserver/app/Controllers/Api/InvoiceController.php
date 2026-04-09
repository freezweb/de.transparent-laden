<?php

namespace App\Controllers\Api;

use App\Models\InvoiceModel;

class InvoiceController extends ApiBaseController
{
    private InvoiceModel $invoiceModel;

    public function __construct()
    {
        $this->invoiceModel = model(InvoiceModel::class);
    }

    public function index()
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = min(50, (int) ($this->request->getGet('per_page') ?? 20));

        $invoices = $this->invoiceModel->getForUser($this->userId, $page, $perPage);

        foreach ($invoices as &$inv) {
            $inv['line_items'] = json_decode($inv['line_items_json'], true);
            unset($inv['line_items_json']);
        }

        return $this->respond([
            'invoices' => $invoices,
            'pager'    => $this->invoiceModel->pager->getDetails(),
        ]);
    }

    public function show(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (! $invoice || $invoice['user_id'] !== $this->userId) {
            return $this->failNotFound('Invoice not found');
        }

        $invoice['line_items'] = json_decode($invoice['line_items_json'], true);
        unset($invoice['line_items_json']);

        return $this->respond(['invoice' => $invoice]);
    }

    public function downloadPdf(int $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (! $invoice || $invoice['user_id'] !== $this->userId) {
            return $this->failNotFound('Invoice not found');
        }

        if (empty($invoice['pdf_path']) || ! file_exists(WRITEPATH . $invoice['pdf_path'])) {
            return $this->failNotFound('PDF not available');
        }

        return $this->response->download(WRITEPATH . $invoice['pdf_path'], null);
    }
}
