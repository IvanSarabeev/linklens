<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261025121355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stored_file (uuid UUID NOT NULL, storage_key VARCHAR(255) NOT NULL, owner_id UUID NOT NULL, purpose VARCHAR(32) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(127) NOT NULL, size_bytes INT NOT NULL, sha256 VARCHAR(64) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (uuid))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C339E77C111795A5 ON stored_file (storage_key)');
        $this->addSql('CREATE INDEX idx_store_file_dedupe ON stored_file (owner_id, sha256, purpose)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE stored_file');
    }
}
