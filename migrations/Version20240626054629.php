<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240626054629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chapter (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, position INT NOT NULL, tuto_id INT DEFAULT NULL, INDEX IDX_F981B52E28B30A4C (tuto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, text VARCHAR(1000) DEFAULT NULL, code VARCHAR(1000) DEFAULT NULL, position INT NOT NULL, image VARCHAR(1000) DEFAULT NULL, video VARCHAR(1000) DEFAULT NULL, chapter_id INT DEFAULT NULL, INDEX IDX_FEC530A9579F4768 (chapter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tuto (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, estimated_time VARCHAR(255) NOT NULL, difficulty VARCHAR(255) NOT NULL, progress_percentage DOUBLE PRECISION NOT NULL, game VARCHAR(255) NOT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(15) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E28B30A4C FOREIGN KEY (tuto_id) REFERENCES tuto (id)');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E28B30A4C');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9579F4768');
        $this->addSql('DROP TABLE chapter');
        $this->addSql('DROP TABLE content');
        $this->addSql('DROP TABLE tuto');
        $this->addSql('DROP TABLE user');
    }
}
