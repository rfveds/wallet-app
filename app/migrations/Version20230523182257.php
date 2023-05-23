<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230523182257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE operation_tag DROP FOREIGN KEY FK_1CA8A1BF44AC3583');
        $this->addSql('ALTER TABLE operation_tag DROP FOREIGN KEY FK_1CA8A1BFBAD26311');
        $this->addSql('DROP TABLE operation_tag');
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_28145348712520F3');
        $this->addSql('DROP INDEX IDX_28145348712520F3 ON operations');
        $this->addSql('ALTER TABLE operations DROP wallet_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE operation_tag (operation_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_1CA8A1BFBAD26311 (tag_id), INDEX IDX_1CA8A1BF44AC3583 (operation_id), PRIMARY KEY(operation_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE operation_tag ADD CONSTRAINT FK_1CA8A1BF44AC3583 FOREIGN KEY (operation_id) REFERENCES operations (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operation_tag ADD CONSTRAINT FK_1CA8A1BFBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operations ADD wallet_id INT NOT NULL');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_28145348712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_28145348712520F3 ON operations (wallet_id)');
    }
}
