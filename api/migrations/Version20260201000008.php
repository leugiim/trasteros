<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201000008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create prestamo table for Prestamo module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE prestamo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            local_id INT NOT NULL,
            entidad_bancaria VARCHAR(255) DEFAULT NULL,
            numero_prestamo VARCHAR(100) DEFAULT NULL,
            capital_solicitado DECIMAL(12,2) NOT NULL,
            total_a_devolver DECIMAL(12,2) NOT NULL,
            tipo_interes DECIMAL(5,4) DEFAULT NULL,
            fecha_concesion DATE NOT NULL,
            estado VARCHAR(50) NOT NULL DEFAULT "activo",
            created_at DATETIME NOT NULL,
            created_by INT DEFAULT NULL,
            updated_at DATETIME NOT NULL,
            updated_by INT DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            deleted_by INT DEFAULT NULL,
            CONSTRAINT fk_prestamo_local FOREIGN KEY (local_id) REFERENCES local(id) ON DELETE CASCADE,
            CONSTRAINT fk_prestamo_created_by FOREIGN KEY (created_by) REFERENCES usuario(id) ON DELETE SET NULL,
            CONSTRAINT fk_prestamo_updated_by FOREIGN KEY (updated_by) REFERENCES usuario(id) ON DELETE SET NULL,
            CONSTRAINT fk_prestamo_deleted_by FOREIGN KEY (deleted_by) REFERENCES usuario(id) ON DELETE SET NULL
        )');

        $this->addSql('CREATE INDEX idx_prestamo_local_id ON prestamo (local_id)');
        $this->addSql('CREATE INDEX idx_prestamo_estado ON prestamo (estado)');
        $this->addSql('CREATE INDEX idx_prestamo_fecha_concesion ON prestamo (fecha_concesion)');
        $this->addSql('CREATE INDEX idx_prestamo_entidad_bancaria ON prestamo (entidad_bancaria)');
        $this->addSql('CREATE INDEX idx_prestamo_deleted_at ON prestamo (deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE prestamo');
    }
}
