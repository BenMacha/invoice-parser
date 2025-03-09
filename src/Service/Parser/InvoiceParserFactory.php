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

class InvoiceParserFactory
{
    /**
     * @var array<InvoiceParserInterface>
     */
    private array $parsers;

    /**
     * @param iterable<InvoiceParserInterface> $parsers List of invoice parsers
     */
    public function __construct(iterable $parsers)
    {
        $this->parsers = [];
        foreach ($parsers as $parser) {
            $this->parsers[] = $parser;
        }
    }

    public function getParser(string $filePath): InvoiceParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($filePath)) {
                return $parser;
            }
        }

        throw new \Exception("No parser found for file: $filePath");
    }
}
