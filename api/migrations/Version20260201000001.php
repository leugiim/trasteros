<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create usuario table for Users module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE usuario (
            id VARCHAR(36) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            rol VARCHAR(20) NOT NULL,
            activo BOOLEAN NOT NULL DEFAULT TRUE,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_usuario_email ON usuario (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE usuario');
    }
}
