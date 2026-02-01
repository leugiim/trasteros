<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201000007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create contrato table for Contrato module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contrato (
            id INT AUTO_INCREMENT PRIMARY KEY,
            trastero_id INT NOT NULL,
            cliente_id INT NOT NULL,
            fecha_inicio DATE NOT NULL,
            fecha_fin DATE DEFAULT NULL,
            precio_mensual DECIMAL(8,2) NOT NULL,
            fianza DECIMAL(8,2) DEFAULT NULL,
            fianza_pagada BOOLEAN DEFAULT FALSE,
            estado ENUM(\'activo\', \'finalizado\', \'cancelado\', \'pendiente\') DEFAULT \'activo\',
            created_at DATETIME NOT NULL,
            created_by INT DEFAULT NULL,
            updated_at DATETIME NOT NULL,
            updated_by INT DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            deleted_by INT DEFAULT NULL,
            CONSTRAINT fk_contrato_trastero FOREIGN KEY (trastero_id) REFERENCES trastero(id) ON DELETE RESTRICT,
            CONSTRAINT fk_contrato_cliente FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE RESTRICT,
            CONSTRAINT fk_contrato_created_by FOREIGN KEY (created_by) REFERENCES usuario(id) ON DELETE SET NULL,
            CONSTRAINT fk_contrato_updated_by FOREIGN KEY (updated_by) REFERENCES usuario(id) ON DELETE SET NULL,
            CONSTRAINT fk_contrato_deleted_by FOREIGN KEY (deleted_by) REFERENCES usuario(id) ON DELETE SET NULL
        )');

        $this->addSql('CREATE INDEX idx_contrato_trastero_id ON contrato (trastero_id)');
        $this->addSql('CREATE INDEX idx_contrato_cliente_id ON contrato (cliente_id)');
        $this->addSql('CREATE INDEX idx_contrato_estado ON contrato (estado)');
        $this->addSql('CREATE INDEX idx_contrato_fecha_inicio ON contrato (fecha_inicio)');
        $this->addSql('CREATE INDEX idx_contrato_fecha_fin ON contrato (fecha_fin)');
        $this->addSql('CREATE INDEX idx_contrato_deleted_at ON contrato (deleted_at)');
        $this->addSql('CREATE INDEX idx_contrato_trastero_estado ON contrato (trastero_id, estado)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contrato');
    }
}
