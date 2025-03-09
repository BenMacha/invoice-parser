<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\Parser\CsvInvoiceParser;
use App\Service\Parser\JsonInvoiceParser;
use PHPUnit\Framework\TestCase;

class InvoiceParserTest extends TestCase
{
    public function testJsonParserSupports(): void
    {
        $parser = new JsonInvoiceParser();

        $this->assertTrue($parser->supports('file.json'));
        $this->assertFalse($parser->supports('file.csv'));
        $this->assertFalse($parser->supports('file.txt'));
    }

    public function testCsvParserSupports(): void
    {
        $parser = new CsvInvoiceParser();

        $this->assertTrue($parser->supports('file.csv'));
        $this->assertFalse($parser->supports('file.json'));
        $this->assertFalse($parser->supports('file.txt'));
    }

    public function testJsonParserParse(): void
    {
        $parser = new JsonInvoiceParser();

        // Créer un fichier json temporaire pour le test
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.json';
        $jsonContent = '[{"montant": 100.50, "devise": "EUR", "nom": "Test Name", "date": "2025-03-01"}]';
        file_put_contents($tempFile, $jsonContent);

        $result = $parser->parse($tempFile);

        $this->assertCount(1, $result);
        $this->assertEquals('Test Name', $result[0]['name']);
        $this->assertEquals(100.50, $result[0]['amount']);
        $this->assertEquals('EUR', $result[0]['currency']);
        $this->assertEquals('2025-03-01', $result[0]['date']->format('Y-m-d'));

    }

    public function testCsvParserParse(): void
    {
        $parser = new CsvInvoiceParser();

        // Créer un fichier csv temporaire pour le test
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.csv';
        $csvContent = "100.50\tEUR\tTest Name\t2025-03-01";
        file_put_contents($tempFile, $csvContent);

        $result = $parser->parse($tempFile);

        $this->assertCount(1, $result);
        $this->assertEquals('Test Name', $result[0]['name']);
        $this->assertEquals(100.50, $result[0]['amount']);
        $this->assertEquals('EUR', $result[0]['currency']);
        $this->assertEquals('2025-03-01', $result[0]['date']->format('Y-m-d'));

    }

}