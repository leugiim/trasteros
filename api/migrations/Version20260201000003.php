<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create local table for Local module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE local (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            direccion_id INT NOT NULL,
            superficie_total DECIMAL(10,2),
            numero_trasteros INT,
            fecha_compra DATE,
            precio_compra DECIMAL(12,2),
            referencia_catastral VARCHAR(50),
            valor_catastral DECIMAL(12,2),
            created_at DATETIME NOT NULL,
            created_by VARCHAR(36),
            updated_at DATETIME NOT NULL,
            updated_by VARCHAR(36),
            deleted_at DATETIME DEFAULT NULL,
            deleted_by VARCHAR(36),
            CONSTRAINT fk_local_direccion FOREIGN KEY (direccion_id) REFERENCES direccion(id),
            CONSTRAINT fk_local_created_by FOREIGN KEY (created_by) REFERENCES usuario(id),
            CONSTRAINT fk_local_updated_by FOREIGN KEY (updated_by) REFERENCES usuario(id),
            CONSTRAINT fk_local_deleted_by FOREIGN KEY (deleted_by) REFERENCES usuario(id)
        )');

        $this->addSql('CREATE INDEX idx_local_direccion ON local (direccion_id)');
        $this->addSql('CREATE INDEX idx_local_nombre ON local (nombre)');
        $this->addSql('CREATE INDEX idx_local_deleted_at ON local (deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE local');
    }
}
