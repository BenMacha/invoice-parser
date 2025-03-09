<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Service\InvoiceProcessor;
use App\Service\Parser\InvoiceParserFactory;
use App\Service\Parser\InvoiceParserInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InvoiceProcessorTest extends TestCase
{
    private $entityManager;
    private $parserFactory;
    private $parser;
    private $logger;
    private $invoiceRepository;
    private $processor;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->parserFactory = $this->createMock(InvoiceParserFactory::class);
        $this->parser = $this->createMock(InvoiceParserInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->invoiceRepository = $this->createMock(InvoiceRepository::class);

        $this->processor = new InvoiceProcessor(
            $this->entityManager,
            $this->parserFactory,
            $this->logger,
            $this->invoiceRepository
        );
    }

    public function testProcessFile(): void
    {
        $testFile = 'test.json';
        $invoiceData = [
            [
                'name' => 'Test Name',
                'amount' => 100.50,
                'currency' => 'EUR',
                'date' => new DateTime('2025-03-01')
            ],
            [
                'name' => 'Another Name',
                'amount' => 200.75,
                'currency' => 'USD',
                'date' => new DateTime('2025-03-02')
            ]
        ];

        // Le parser factory doit retourner notre parseur mock
        $this->parserFactory->expects($this->once())
            ->method('getParser')
            ->with($testFile)
            ->willReturn($this->parser);

        // Le parseur doit parser le fichier et retourner nos données de test
        $this->parser->expects($this->once())
            ->method('parse')
            ->with($testFile)
            ->willReturn($invoiceData);

        // Pour le premier invoice, on simule qu'il n'existe pas en base
        $this->invoiceRepository->expects($this->at(0))
            ->method('findOneBy')
            ->with(['name' => 'Test Name'])
            ->willReturn(null);

        // Pour le second invoice, on simule qu'il existe déjà
        $existingInvoice = new Invoice();
        $existingInvoice->setName('Another Name');
        $this->invoiceRepository->expects($this->at(1))
            ->method('findOneBy')
            ->with(['name' => 'Another Name'])
            ->willReturn($existingInvoice);

        // On doit persister les deux invoices
        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        // Et faire un flush final
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->processor->processFile($testFile);

        $this->assertEquals(2, $result);
    }

    public function testProcessFileWithError(): void
    {
        $testFile = 'test.json';
        $exception = new \Exception('Test error');

        // Le parser factory lance une exception
        $this->parserFactory->expects($this->once())
            ->method('getParser')
            ->with($testFile)
            ->willThrowException($exception);

        // Le logger doit enregistrer l'erreur
        $this->logger->expects($this->once())
            ->method('error')
            ->with("Failed to process invoice file: Test error");

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test error');

        $this->processor->processFile($testFile);
    }
}