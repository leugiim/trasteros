<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create gasto table for Gasto module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE gasto (
            id INT AUTO_INCREMENT PRIMARY KEY,
            local_id INT NOT NULL,
            concepto VARCHAR(255) NOT NULL,
            descripcion TEXT,
            importe DECIMAL(10,2) NOT NULL,
            fecha DATE NOT NULL,
            categoria VARCHAR(50) NOT NULL,
            metodo_pago VARCHAR(50),
            created_at DATETIME NOT NULL,
            created_by VARCHAR(36),
            updated_at DATETIME NOT NULL,
            updated_by VARCHAR(36),
            deleted_at DATETIME DEFAULT NULL,
            deleted_by VARCHAR(36),
            CONSTRAINT fk_gasto_local FOREIGN KEY (local_id) REFERENCES local(id),
            CONSTRAINT fk_gasto_created_by FOREIGN KEY (created_by) REFERENCES usuario(id),
            CONSTRAINT fk_gasto_updated_by FOREIGN KEY (updated_by) REFERENCES usuario(id),
            CONSTRAINT fk_gasto_deleted_by FOREIGN KEY (deleted_by) REFERENCES usuario(id),
            CONSTRAINT chk_gasto_categoria CHECK (categoria IN (
                "suministros", "seguros", "impuestos", "mantenimiento",
                "prestamo", "gestoria", "otros"
            )),
            CONSTRAINT chk_gasto_metodo_pago CHECK (
                metodo_pago IS NULL OR
                metodo_pago IN ("efectivo", "transferencia", "tarjeta", "domiciliacion")
            )
        )');

        $this->addSql('CREATE INDEX idx_gasto_local ON gasto (local_id)');
        $this->addSql('CREATE INDEX idx_gasto_fecha ON gasto (fecha)');
        $this->addSql('CREATE INDEX idx_gasto_categoria ON gasto (categoria)');
        $this->addSql('CREATE INDEX idx_gasto_deleted_at ON gasto (deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE gasto');
    }
}
