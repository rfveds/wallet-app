<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230523204311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE operations_tags (operation_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_C5D0F2FF44AC3583 (operation_id), INDEX IDX_C5D0F2FFBAD26311 (tag_id), PRIMARY KEY(operation_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE operations_tags ADD CONSTRAINT FK_C5D0F2FF44AC3583 FOREIGN KEY (operation_id) REFERENCES operations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operations_tags ADD CONSTRAINT FK_C5D0F2FFBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operation_tag DROP FOREIGN KEY FK_1CA8A1BF44AC3583');
        $this->addSql('ALTER TABLE operation_tag DROP FOREIGN KEY FK_1CA8A1BFBAD26311');
        $this->addSql('DROP TABLE operation_tag');
        $this->addSql('ALTER TABLE operations CHANGE wallet_id wallet_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE operation_tag (operation_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_1CA8A1BFBAD26311 (tag_id), INDEX IDX_1CA8A1BF44AC3583 (operation_id), PRIMARY KEY(operation_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE operation_tag ADD CONSTRAINT FK_1CA8A1BF44AC3583 FOREIGN KEY (operation_id) REFERENCES operations (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operation_tag ADD CONSTRAINT FK_1CA8A1BFBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operations_tags DROP FOREIGN KEY FK_C5D0F2FF44AC3583');
        $this->addSql('ALTER TABLE operations_tags DROP FOREIGN KEY FK_C5D0F2FFBAD26311');
        $this->addSql('DROP TABLE operations_tags');
        $this->addSql('ALTER TABLE operations CHANGE wallet_id wallet_id INT DEFAULT NULL');
    }
}
