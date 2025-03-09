<?php

declare(strict_types=1);

namespace App\Service\Parser;

use Exception;


class InvoiceParserFactory
{
    /**
     * @var InvoiceParserInterface[]
     */
    private array $parsers;

    /**
     *
     * @param iterable $parsers List of invoice parsers
     */
    public function __construct(iterable $parsers)
    {
        $this->parsers = [];
        foreach ($parsers as $parser) {
            $this->parsers[] = $parser;
        }
    }

    /**
     *
     * @param string $filePath
     * @return InvoiceParserInterface
     * @throws Exception
     */
    public function getParser(string $filePath): InvoiceParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($filePath)) {
                return $parser;
            }
        }

        throw new Exception("No parser found for file: $filePath");
    }
}