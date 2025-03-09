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

namespace App\Service;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Service\Parser\InvoiceParserFactory;
use Doctrine\ORM\EntityManagerInterface;
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

    public function processFile(string $filePath): int
    {
        try {
            $parser = $this->parserFactory->getParser($filePath);
            $invoiceData = $parser->parse($filePath);

            return $this->processInvoiceData($invoiceData);
        } catch (\Exception $e) {
            $this->logger->error("Failed to process invoice file: {$e->getMessage()}");

            throw $e;
        }
    }

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
                ++$count;

                if (0 === $count % $batchSize) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
            }

            if (0 !== $count % $batchSize) {
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
