<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250304000000 extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice ALTER name TYPE VARCHAR(75)');
        $this->addSql('ALTER TABLE invoice ALTER currency TYPE VARCHAR(3)');
        $this->addSql('ALTER TABLE invoice ADD invoice_date DATE NOT NULL');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice DROP invoice_date');
    }
}