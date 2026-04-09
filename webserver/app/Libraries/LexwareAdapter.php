<?php

namespace App\Libraries;

class LexwareAdapter
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = env('lexware.apiUrl', '');
        $this->apiKey = env('lexware.apiKey', '');
    }

    public function createVoucher(array $invoice): string
    {
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            throw new \RuntimeException('Lexware API not configured');
        }

        $voucherData = [
            'voucherDate'   => date('Y-m-d', strtotime($invoice['issued_at'])),
            'totalGrossAmount' => $invoice['gross_amount_cent'] / 100,
            'totalTaxAmount'   => $invoice['tax_amount_cent'] / 100,
            'taxRate'          => $invoice['tax_rate'],
            'voucherNumber'    => $invoice['invoice_number'],
            'voucherItems'     => json_decode($invoice['line_items_json'], true),
        ];

        $response = $this->request('POST', '/v1/vouchers', $voucherData);

        if (empty($response['id'])) {
            throw new \RuntimeException('Lexware API did not return voucher ID');
        }

        return $response['id'];
    }

    private function request(string $method, string $path, ?array $data = null): array
    {
        $client = service('curlrequest');

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ];

        if ($data) {
            $options['json'] = $data;
        }

        $response = $client->request($method, $this->apiUrl . $path, $options);

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException('Lexware API error: ' . $response->getStatusCode() . ' ' . $response->getBody());
        }

        return json_decode($response->getBody(), true) ?? [];
    }
}
