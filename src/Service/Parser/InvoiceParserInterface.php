<?php

declare(strict_types=1);

namespace App\Service\Parser;


interface InvoiceParserInterface
{
    /**
     * @param string $filePath
     * @return bool
     */
    public function supports(string $filePath): bool;

    /**
     *
     * @param string $filePath
     * @return array
     */
    public function parse(string $filePath): array;
}