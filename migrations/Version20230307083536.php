<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230307083536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD password_is_verified TINYINT(1) NOT NULL, ADD password_token VARCHAR(100) DEFAULT NULL, CHANGE is_verified login_is_verified TINYINT(1) NOT NULL, CHANGE confirmation_token login_token VARCHAR(255) DEFAULT NULL, CHANGE confirmation_token_expires_at login_token_expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD is_verified TINYINT(1) NOT NULL, DROP login_is_verified, DROP password_is_verified, DROP password_token, CHANGE login_token confirmation_token VARCHAR(255) DEFAULT NULL, CHANGE login_token_expires_at confirmation_token_expires_at DATETIME DEFAULT NULL');
    }
}
