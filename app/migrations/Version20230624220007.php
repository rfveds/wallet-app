<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230624220007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reports DROP INDEX UNIQ_F11FA745BAD26311, ADD INDEX IDX_F11FA745BAD26311 (tag_id)');
        $this->addSql('ALTER TABLE reports DROP INDEX UNIQ_F11FA745712520F3, ADD INDEX IDX_F11FA745712520F3 (wallet_id)');
        $this->addSql('ALTER TABLE reports DROP INDEX UNIQ_F11FA74512469DE2, ADD INDEX IDX_F11FA74512469DE2 (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reports DROP INDEX IDX_F11FA74512469DE2, ADD UNIQUE INDEX UNIQ_F11FA74512469DE2 (category_id)');
        $this->addSql('ALTER TABLE reports DROP INDEX IDX_F11FA745712520F3, ADD UNIQUE INDEX UNIQ_F11FA745712520F3 (wallet_id)');
        $this->addSql('ALTER TABLE reports DROP INDEX IDX_F11FA745BAD26311, ADD UNIQUE INDEX UNIQ_F11FA745BAD26311 (tag_id)');
    }
}
