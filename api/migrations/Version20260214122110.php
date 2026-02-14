<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260214122110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__gasto AS SELECT id, concepto, descripcion, importe, fecha, categoria, metodo_pago, created_at, updated_at, deleted_at, local_id, created_by, updated_by, deleted_by FROM gasto');
        $this->addSql('DROP TABLE gasto');
        $this->addSql('CREATE TABLE gasto (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, concepto VARCHAR(255) NOT NULL, descripcion CLOB DEFAULT NULL, importe NUMERIC(10, 2) NOT NULL, fecha DATE NOT NULL, categoria VARCHAR(50) NOT NULL, metodo_pago VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, local_id INTEGER NOT NULL, created_by VARCHAR(36) DEFAULT NULL, updated_by VARCHAR(36) DEFAULT NULL, deleted_by VARCHAR(36) DEFAULT NULL, prestamo_id INTEGER DEFAULT NULL, CONSTRAINT FK_AE43DA145D5A2101 FOREIGN KEY (local_id) REFERENCES local (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AE43DA14DE12AB56 FOREIGN KEY (created_by) REFERENCES usuario (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AE43DA1416FE72E1 FOREIGN KEY (updated_by) REFERENCES usuario (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AE43DA141F6FA0AF FOREIGN KEY (deleted_by) REFERENCES usuario (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AE43DA14135A846E FOREIGN KEY (prestamo_id) REFERENCES prestamo (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO gasto (id, concepto, descripcion, importe, fecha, categoria, metodo_pago, created_at, updated_at, deleted_at, local_id, created_by, updated_by, deleted_by) SELECT id, concepto, descripcion, importe, fecha, categoria, metodo_pago, created_at, updated_at, deleted_at, local_id, created_by, updated_by, deleted_by FROM __temp__gasto');
        $this->addSql('DROP TABLE __temp__gasto');
        $this->addSql('CREATE INDEX idx_gasto_deleted_at ON gasto (deleted_at)');
        $this->addSql('CREATE INDEX idx_gasto_categoria ON gasto (categoria)');
        $this->addSql('CREATE INDEX idx_gasto_fecha ON gasto (fecha)');
        $this->addSql('CREATE INDEX IDX_AE43DA141F6FA0AF ON gasto (deleted_by)');
        $this->addSql('CREATE INDEX IDX_AE43DA1416FE72E1 ON gasto (updated_by)');
        $this->addSql('CREATE INDEX IDX_AE43DA14DE12AB56 ON gasto (created_by)');
        $this->addSql('CREATE INDEX IDX_AE43DA145D5A2101 ON gasto (local_id)');
        $this->addSql('CREATE INDEX IDX_AE43DA14135A846E ON gasto (prestamo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__gasto AS SELECT id, concepto, descripcion, importe, fecha, categoria, metodo_pago, created_at, updated_at, deleted_at, local_id, created_by, updated_by, deleted_by FROM gasto');
        $this->addSql('DROP TABLE gasto');
        $this->addSql('CREATE TABLE gasto (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, concepto VARCHAR(255) NOT NULL, descripcion CLOB DEFAULT NULL, importe NUMERIC(10, 2) NOT NULL, fecha DATE NOT NULL, categoria VARCHAR(50) NOT NULL, metodo_pago VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, local_id INTEGER NOT NULL, created_by VARCHAR(36) DEFAULT NULL, updated_by VARCHAR(36) DEFAULT NULL, deleted_by VARCHAR(36) DEFAULT NULL, CONSTRAINT FK_AE43DA145D5A2101 FOREIGN KEY (local_id) REFERENCES local (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AE43DA14DE12AB56 FOREIGN KEY (created_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AE43DA1416FE72E1 FOREIGN KEY (updated_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AE43DA141F6FA0AF FOREIGN KEY (deleted_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO gasto (id, concepto, descripcion, importe, fecha, categoria, metodo_pago, created_at, updated_at, deleted_at, local_id, created_by, updated_by, deleted_by) SELECT id, concepto, descripcion, importe, fecha, categoria, metodo_pago, created_at, updated_at, deleted_at, local_id, created_by, updated_by, deleted_by FROM __temp__gasto');
        $this->addSql('DROP TABLE __temp__gasto');
        $this->addSql('CREATE INDEX IDX_AE43DA145D5A2101 ON gasto (local_id)');
        $this->addSql('CREATE INDEX IDX_AE43DA14DE12AB56 ON gasto (created_by)');
        $this->addSql('CREATE INDEX IDX_AE43DA1416FE72E1 ON gasto (updated_by)');
        $this->addSql('CREATE INDEX IDX_AE43DA141F6FA0AF ON gasto (deleted_by)');
        $this->addSql('CREATE INDEX idx_gasto_fecha ON gasto (fecha)');
        $this->addSql('CREATE INDEX idx_gasto_categoria ON gasto (categoria)');
        $this->addSql('CREATE INDEX idx_gasto_deleted_at ON gasto (deleted_at)');
    }
}
