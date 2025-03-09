<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\InvoiceProcessor;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:parse',
    description: 'Parse invoice files and update the database'
)]
class ParseInvoicesCommand extends Command
{
    private InvoiceProcessor $processor;

    public function __construct(InvoiceProcessor $processor)
    {
        parent::__construct();
        $this->processor = $processor;
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->addArgument('files', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Files to parse (default: data/invoices.json and data/invoices.csv)');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $files = $input->getArgument('files');//on peut utiliser aussi InputArgument::REQUIRED si on ne veut pas utiliser des fichiers par default
        if (empty($files)) {
            $files = ['data/invoices.json', 'data/invoices.csv'];  //j'ai gardé ces fichiers par précaution
        }

        $totalCount = 0;
        $errors = 0;

        foreach ($files as $file) {
            try {
                $io->section("Processing file: $file");
                $count = $this->processor->processFile($file);
                $totalCount += $count;
                $io->success("Successfully processed $count invoices from $file");
            } catch (Exception $e) {
                $io->error("Error processing $file: " . $e->getMessage());
                $errors++;
            }
        }

        if ($errors > 0) {
            $io->warning("Completed with $errors error(s). Total invoices processed: $totalCount");
            return Command::FAILURE;
        }

        $io->success("All files processed successfully. Total invoices processed: $totalCount");
        return Command::SUCCESS;
    }
}