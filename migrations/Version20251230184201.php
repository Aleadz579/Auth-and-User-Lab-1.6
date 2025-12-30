<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230184201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auth_log (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, success TINYINT NOT NULL, identifier VARCHAR(190) DEFAULT NULL, failure_reason VARCHAR(64) DEFAULT NULL, ip VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, context JSON DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_1DD25DB8A76ED395 (user_id), INDEX idx_authlog_created_at (created_at), INDEX idx_authlog_action (action), INDEX idx_authlog_ip (ip), INDEX idx_authlog_identifier (identifier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE auth_log ADD CONSTRAINT FK_1DD25DB8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_log DROP FOREIGN KEY FK_1DD25DB8A76ED395');
        $this->addSql('DROP TABLE auth_log');
    }
}
