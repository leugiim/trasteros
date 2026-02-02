<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201000008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ingreso table for Ingreso module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ingreso (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            contrato_id INT NOT NULL,
            concepto VARCHAR(255) NOT NULL,
            importe DECIMAL(8,2) NOT NULL,
            fecha_pago DATE NOT NULL,
            metodo_pago VARCHAR(50),
            categoria VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            created_by INT DEFAULT NULL,
            updated_at DATETIME NOT NULL,
            updated_by INT DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            deleted_by INT DEFAULT NULL,
            CONSTRAINT fk_ingreso_contrato FOREIGN KEY (contrato_id) REFERENCES contrato(id),
            CONSTRAINT fk_ingreso_created_by FOREIGN KEY (created_by) REFERENCES usuario(id),
            CONSTRAINT fk_ingreso_updated_by FOREIGN KEY (updated_by) REFERENCES usuario(id),
            CONSTRAINT fk_ingreso_deleted_by FOREIGN KEY (deleted_by) REFERENCES usuario(id)
        )');

        $this->addSql('CREATE INDEX idx_ingreso_contrato_id ON ingreso (contrato_id)');
        $this->addSql('CREATE INDEX idx_ingreso_fecha_pago ON ingreso (fecha_pago)');
        $this->addSql('CREATE INDEX idx_ingreso_categoria ON ingreso (categoria)');
        $this->addSql('CREATE INDEX idx_ingreso_metodo_pago ON ingreso (metodo_pago)');
        $this->addSql('CREATE INDEX idx_ingreso_deleted_at ON ingreso (deleted_at)');
        $this->addSql('CREATE INDEX idx_ingreso_contrato_fecha ON ingreso (contrato_id, fecha_pago)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ingreso');
    }
}
