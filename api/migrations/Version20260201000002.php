<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create direccion table for Direccion module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE direccion (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo_via VARCHAR(50),
            nombre_via VARCHAR(255) NOT NULL,
            numero VARCHAR(10),
            piso VARCHAR(10),
            puerta VARCHAR(10),
            codigo_postal VARCHAR(10) NOT NULL,
            ciudad VARCHAR(100) NOT NULL,
            provincia VARCHAR(100) NOT NULL,
            pais VARCHAR(100) DEFAULT "España",
            latitud DECIMAL(10,8),
            longitud DECIMAL(11,8),
            created_at DATETIME NOT NULL,
            created_by VARCHAR(36),
            updated_at DATETIME NOT NULL,
            updated_by VARCHAR(36),
            deleted_at DATETIME DEFAULT NULL,
            deleted_by VARCHAR(36),
            CONSTRAINT fk_direccion_created_by FOREIGN KEY (created_by) REFERENCES usuario(id),
            CONSTRAINT fk_direccion_updated_by FOREIGN KEY (updated_by) REFERENCES usuario(id),
            CONSTRAINT fk_direccion_deleted_by FOREIGN KEY (deleted_by) REFERENCES usuario(id)
        )');

        $this->addSql('CREATE INDEX idx_direccion_codigo_postal ON direccion (codigo_postal)');
        $this->addSql('CREATE INDEX idx_direccion_ciudad ON direccion (ciudad)');
        $this->addSql('CREATE INDEX idx_direccion_provincia ON direccion (provincia)');
        $this->addSql('CREATE INDEX idx_direccion_deleted_at ON direccion (deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE direccion');
    }
}
