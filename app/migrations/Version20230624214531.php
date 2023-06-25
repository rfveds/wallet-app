<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230624214531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', title VARCHAR(64) NOT NULL, slug VARCHAR(64) NOT NULL, INDEX IDX_3AF34668F675F31B (author_id), UNIQUE INDEX uq_categories_title (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE operations (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, wallet_id INT NOT NULL, author_id INT NOT NULL, title VARCHAR(64) NOT NULL, amount NUMERIC(16, 2) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', current_balance NUMERIC(16, 2) NOT NULL, INDEX IDX_2814534812469DE2 (category_id), INDEX IDX_28145348712520F3 (wallet_id), INDEX IDX_28145348F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE operations_tags (operation_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_C5D0F2FF44AC3583 (operation_id), INDEX IDX_C5D0F2FFBAD26311 (tag_id), PRIMARY KEY(operation_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reports (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, category_id INT DEFAULT NULL, wallet_id INT DEFAULT NULL, tag_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_from DATE NOT NULL, date_to DATE NOT NULL, title VARCHAR(64) NOT NULL, INDEX IDX_F11FA745F675F31B (author_id), UNIQUE INDEX UNIQ_F11FA74512469DE2 (category_id), UNIQUE INDEX UNIQ_F11FA745712520F3 (wallet_id), UNIQUE INDEX UNIQ_F11FA745BAD26311 (tag_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tags (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', title VARCHAR(64) NOT NULL, slug VARCHAR(64) NOT NULL, INDEX IDX_6FBC9426F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(64) NOT NULL, last_name VARCHAR(64) NOT NULL, blocked TINYINT(1) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX email_idx (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE wallet (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(32) NOT NULL, balance NUMERIC(16, 2) NOT NULL, title VARCHAR(64) NOT NULL, INDEX IDX_7C68921FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_2814534812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_28145348712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id)');
        $this->addSql('ALTER TABLE operations ADD CONSTRAINT FK_28145348F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE operations_tags ADD CONSTRAINT FK_C5D0F2FF44AC3583 FOREIGN KEY (operation_id) REFERENCES operations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE operations_tags ADD CONSTRAINT FK_C5D0F2FFBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA74512469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id)');
        $this->addSql('ALTER TABLE tags ADD CONSTRAINT FK_6FBC9426F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668F675F31B');
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_2814534812469DE2');
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_28145348712520F3');
        $this->addSql('ALTER TABLE operations DROP FOREIGN KEY FK_28145348F675F31B');
        $this->addSql('ALTER TABLE operations_tags DROP FOREIGN KEY FK_C5D0F2FF44AC3583');
        $this->addSql('ALTER TABLE operations_tags DROP FOREIGN KEY FK_C5D0F2FFBAD26311');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745F675F31B');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA74512469DE2');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745712520F3');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745BAD26311');
        $this->addSql('ALTER TABLE tags DROP FOREIGN KEY FK_6FBC9426F675F31B');
        $this->addSql('ALTER TABLE wallet DROP FOREIGN KEY FK_7C68921FA76ED395');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE operations');
        $this->addSql('DROP TABLE operations_tags');
        $this->addSql('DROP TABLE reports');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE wallet');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
