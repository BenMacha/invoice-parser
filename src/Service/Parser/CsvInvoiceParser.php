<?php

declare(strict_types=1);

/**
 * PHP version 8.2 & Symfony 5.4.
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * https://www.php.net/license/3_01.txt.
 *
 * Updated by Ben Macha.
 *
 * @category   Symfony Invoicing Parcer Project
 *
 * @author     Ali BEN MECHA       <contact@benmacha.tn>
 *
 * @copyright  Ⓒ 2025 benmacha.tn
 *
 * @see       https://www.benmacha.tn
 *
 */

namespace App\Service\Parser;

class CsvInvoiceParser implements InvoiceParserInterface
{
    public function supports(string $filePath): bool
    {
        return 'csv' === pathinfo($filePath, PATHINFO_EXTENSION);
    }

    /**
     * @return array<array{name: string, amount: float, currency: string, date: \DateTime}>
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: $filePath");
        }

        $rows = file($filePath);
        if (false === $rows) {
            throw new \Exception("Failed to read file: $filePath");
        }

        $invoices = [];
        foreach ($rows as $row) {
            $columns = str_getcsv($row, "\t");

            $invoices[] = [
                'amount' => (float) $columns[0],
                'currency' => $columns[1] ?? '',
                'name' => $columns[2] ?? '',
                'date' => new \DateTime($columns[3]),
            ];
        }

        return $invoices;
    }
}
