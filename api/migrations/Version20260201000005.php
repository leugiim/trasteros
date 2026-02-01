<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201000005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create trastero table for Trastero module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE trastero (
            id INT AUTO_INCREMENT PRIMARY KEY,
            local_id INT NOT NULL,
            numero VARCHAR(20) NOT NULL,
            nombre VARCHAR(100),
            superficie DECIMAL(6,2) NOT NULL,
            precio_mensual DECIMAL(8,2) NOT NULL,
            estado VARCHAR(50) NOT NULL DEFAULT "disponible",
            created_at DATETIME NOT NULL,
            created_by VARCHAR(36),
            updated_at DATETIME NOT NULL,
            updated_by VARCHAR(36),
            deleted_at DATETIME DEFAULT NULL,
            deleted_by VARCHAR(36),
            CONSTRAINT fk_trastero_local FOREIGN KEY (local_id) REFERENCES local(id),
            CONSTRAINT fk_trastero_created_by FOREIGN KEY (created_by) REFERENCES usuario(id),
            CONSTRAINT fk_trastero_updated_by FOREIGN KEY (updated_by) REFERENCES usuario(id),
            CONSTRAINT fk_trastero_deleted_by FOREIGN KEY (deleted_by) REFERENCES usuario(id),
            CONSTRAINT unique_trastero_local UNIQUE (local_id, numero),
            CONSTRAINT chk_trastero_estado CHECK (estado IN (
                "disponible", "ocupado", "mantenimiento", "reservado"
            ))
        )');

        $this->addSql('CREATE INDEX idx_trastero_local ON trastero (local_id)');
        $this->addSql('CREATE INDEX idx_trastero_estado ON trastero (estado)');
        $this->addSql('CREATE INDEX idx_trastero_deleted_at ON trastero (deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE trastero');
    }
}
