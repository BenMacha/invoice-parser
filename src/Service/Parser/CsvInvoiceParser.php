<?php

declare(strict_types=1);

namespace App\Service\Parser;

use Exception;

class CsvInvoiceParser implements InvoiceParserInterface
{
    /**
     * @inheritDoc
     */
    public function supports(string $filePath): bool
    {
        return pathinfo($filePath, PATHINFO_EXTENSION) === 'csv';
    }

    /**
     * @inheritDoc
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }

        $rows = file($filePath);
        if ($rows === false) {
            throw new Exception("Failed to read file: $filePath");
        }

        $invoices = [];
        foreach ($rows as $row) {
            $columns = str_getcsv($row, "\t");

            $invoices[] = [
                'amount' => (float)$columns[0],
                'currency' => $columns[1] ?? '',
                'name' => $columns[2] ?? '',
                'date' => new \DateTime($columns[3])
            ];
        }

        return $invoices;
    }
}