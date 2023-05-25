<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230525171950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE operations ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_28145348F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_28145348F675F31B ON operations (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_28145348F675F31B');
        $this->addSql('DROP INDEX IDX_28145348F675F31B ON operations');
        $this->addSql('ALTER TABLE operations DROP author_id');
    }
}
