<?php

declare(strict_types=1);

namespace App\Service\Parser;

use Exception;


class JsonInvoiceParser implements InvoiceParserInterface
{
    /**
     * @inheritDoc
     */
    public function supports(string $filePath): bool
    {
        return pathinfo($filePath, PATHINFO_EXTENSION) === 'json';
    }

    /**
     * @inheritDoc
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }

        $content = file_get_contents($filePath);

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in file: $filePath. Error: " . json_last_error_msg());
        }

        $invoices = [];
        foreach ($data as $item) {
            $invoices[] = [
                'name' => $item['nom'] ?? '',
                'amount' => $item['montant'] ?? 0.0,
                'currency' => $item['devise'] ?? '',
                'date' => new \DateTime($item['date'])
            ];
        }

        return $invoices;
    }
}