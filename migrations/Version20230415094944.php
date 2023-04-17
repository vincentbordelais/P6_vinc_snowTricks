<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230415094944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video_yt DROP FOREIGN KEY FK_7EB4027B3256915B');
        $this->addSql('DROP INDEX UNIQ_7EB4027B3256915B ON video_yt');
        $this->addSql('ALTER TABLE video_yt CHANGE relation_id trick_id INT NOT NULL');
        $this->addSql('ALTER TABLE video_yt ADD CONSTRAINT FK_7EB4027BB281BE2E FOREIGN KEY (trick_id) REFERENCES trick (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7EB4027BB281BE2E ON video_yt (trick_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video_yt DROP FOREIGN KEY FK_7EB4027BB281BE2E');
        $this->addSql('DROP INDEX UNIQ_7EB4027BB281BE2E ON video_yt');
        $this->addSql('ALTER TABLE video_yt CHANGE trick_id relation_id INT NOT NULL');
        $this->addSql('ALTER TABLE video_yt ADD CONSTRAINT FK_7EB4027B3256915B FOREIGN KEY (relation_id) REFERENCES trick (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7EB4027B3256915B ON video_yt (relation_id)');
    }
}
