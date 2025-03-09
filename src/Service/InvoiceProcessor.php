<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Service\Parser\InvoiceParserFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class InvoiceProcessor
{
    private EntityManagerInterface $entityManager;
    private InvoiceParserFactory $parserFactory;
    private LoggerInterface $logger;
    private InvoiceRepository $invoiceRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        InvoiceParserFactory $parserFactory,
        LoggerInterface $logger,
        InvoiceRepository $invoiceRepository
    ) {
        $this->entityManager = $entityManager;
        $this->parserFactory = $parserFactory;
        $this->logger = $logger;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     *
     * @param string $filePath
     * @return int
     * @throws Exception
     */
    public function processFile(string $filePath): int
    {
        try {
            $parser = $this->parserFactory->getParser($filePath);
            $invoiceData = $parser->parse($filePath);

            return $this->processInvoiceData($invoiceData);
        } catch (Exception $e) {
            $this->logger->error("Failed to process invoice file: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     *
     * @param array $invoiceData
     * @return int
     * @throws \Exception
     */
    private function processInvoiceData(array $invoiceData): int
    {
        $count = 0;
        $batchSize = 20;

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            foreach ($invoiceData as $data) {
                $invoice = $this->invoiceRepository->findOneBy(['name' => $data['name']]) ?? new Invoice();

                $invoice->setName($data['name'])
                    ->setAmount($data['amount'])
                    ->setCurrency($data['currency'])
                    ->setInvoiceDate($data['date']);

                $this->entityManager->persist($invoice);
                $count++;

                if ($count % $batchSize === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
            }

            if ($count % $batchSize !== 0) {
                $this->entityManager->flush();
            }
            $connection->commit();

            return $count;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->error("Transaction failed: {$e->getMessage()}");
            throw $e;
        }
    }
}