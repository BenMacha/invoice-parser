<?php

declare(strict_types=1);

/**
 * PHP version 8.2 & Symfony 5.4.
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * https://www.php.net/license/3_01.txt.
 *
 * POS developed by Ben Macha.
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

class JsonInvoiceParser implements InvoiceParserInterface
{
    public function supports(string $filePath): bool
    {
        return 'json' === pathinfo($filePath, PATHINFO_EXTENSION);
    }

    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: $filePath");
        }

        $content = file_get_contents($filePath);

        $data = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception("Invalid JSON in file: $filePath. Error: " . json_last_error_msg());
        }

        $invoices = [];
        foreach ($data as $item) {
            $invoices[] = [
                'name' => $item['nom'] ?? '',
                'amount' => $item['montant'] ?? 0.0,
                'currency' => $item['devise'] ?? '',
                'date' => new \DateTime($item['date']),
            ];
        }

        return $invoices;
    }
}
