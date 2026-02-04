<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203194232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cliente (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, apellidos VARCHAR(200) NOT NULL, dni_nie VARCHAR(20) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, telefono VARCHAR(20) DEFAULT NULL, activo BOOLEAN NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, created_by VARCHAR(36) DEFAULT NULL, updated_by VARCHAR(36) DEFAULT NULL, deleted_by VARCHAR(36) DEFAULT NULL, CONSTRAINT FK_F41C9B25DE12AB56 FOREIGN KEY (created_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F41C9B2516FE72E1 FOREIGN KEY (updated_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F41C9B251F6FA0AF FOREIGN KEY (deleted_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F41C9B25DE12AB56 ON cliente (created_by)');
        $this->addSql('CREATE INDEX IDX_F41C9B2516FE72E1 ON cliente (updated_by)');
        $this->addSql('CREATE INDEX IDX_F41C9B251F6FA0AF ON cliente (deleted_by)');
        $this->addSql('CREATE INDEX idx_cliente_dni_nie ON cliente (dni_nie)');
        $this->addSql('CREATE INDEX idx_cliente_email ON cliente (email)');
        $this->addSql('CREATE INDEX idx_cliente_deleted_at ON cliente (deleted_at)');
        $this->addSql('CREATE TABLE contrato (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, fecha_inicio DATE NOT NULL, fecha_fin DATE DEFAULT NULL, precio_mensual NUMERIC(8, 2) NOT NULL, fianza NUMERIC(8, 2) DEFAULT NULL, fianza_pagada BOOLEAN NOT NULL, estado VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, trastero_id INTEGER NOT NULL, cliente_id INTEGER NOT NULL, created_by VARCHAR(36) DEFAULT NULL, updated_by VARCHAR(36) DEFAULT NULL, deleted_by VARCHAR(36) DEFAULT NULL, CONSTRAINT FK_66696523874EE4A5 FOREIGN KEY (trastero_id) REFERENCES trastero (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_66696523DE734E51 FOREIGN KEY (cliente_id) REFERENCES cliente (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_66696523DE12AB56 FOREIGN KEY (created_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6669652316FE72E1 FOREIGN KEY (updated_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_666965231F6FA0AF FOREIGN KEY (deleted_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_66696523874EE4A5 ON contrato (trastero_id)');
        $this->addSql('CREATE INDEX IDX_66696523DE734E51 ON contrato (cliente_id)');
        $this->addSql('CREATE INDEX IDX_66696523DE12AB56 ON contrato (created_by)');
        $this->addSql('CREATE INDEX IDX_6669652316FE72E1 ON contrato (updated_by)');
        $this->addSql('CREATE INDEX IDX_666965231F6FA0AF ON contrato (deleted_by)');
        $this->addSql('CREATE INDEX idx_contrato_estado ON contrato (estado)');
        $this->addSql('CREATE INDEX idx_contrato_fecha_inicio ON contrato (fecha_inicio)');
        $this->addSql('CREATE INDEX idx_contrato_fecha_fin ON contrato (fecha_fin)');
        $this->addSql('CREATE INDEX idx_contrato_deleted_at ON contrato (deleted_at)');
        $this->addSql('CREATE TABLE direccion (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, tipo_via VARCHAR(50) DEFAULT NULL, nombre_via VARCHAR(255) NOT NULL, numero VARCHAR(10) DEFAULT NULL, piso VARCHAR(10) DEFAULT NULL, puerta VARCHAR(10) DEFAULT NULL, codigo_postal VARCHAR(10) NOT NULL, ciudad VARCHAR(100) NOT NULL, provincia VARCHAR(100) NOT NULL, pais VARCHAR(100) NOT NULL, latitud NUMERIC(10, 8) DEFAULT NULL, longitud NUMERIC(11, 8) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, created_by VARCHAR(36) DEFAULT NULL, updated_by VARCHAR(36) DEFAULT NULL, deleted_by VARCHAR(36) DEFAULT NULL, CONSTRAINT FK_F384BE95DE12AB56 FOREIGN KEY (created_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F384BE9516FE72E1 FOREIGN KEY (updated_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F384BE951F6FA0AF FOREIGN KEY (deleted_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F384BE95DE12AB56 ON direccion (created_by)');
        $this->addSql('CREATE INDEX IDX_F384BE9516FE72E1 ON direccion (updated_by)');
        $this->addSql('CREATE INDEX IDX_F384BE951F6FA0AF ON direccion (deleted_by)');
        $this->addSql('CREATE INDEX idx_direccion_deleted_at ON direccion (deleted_at)');
        $this->addSql('CREATE TABLE gasto (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, concepto VARCHAR(255) NOT NULL, descripcion CLOB DEFAULT NULL, importe NUMERIC(10, 2) NOT NULL, fecha DATE NOT NULL, categoria VARCHAR(50) NOT NULL, metodo_pago VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, local_id INTEGER NOT NULL, created_by VARCHAR(36) DEFAULT NULL, updated_by VARCHAR(36) DEFAULT NULL, deleted_by VARCHAR(36) DEFAULT NULL, CONSTRAINT FK_AE43DA145D5A2101 FOREIGN KEY (local_id) REFERENCES local (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AE43DA14DE12AB56 FOREIGN KEY (created_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AE43DA1416FE72E1 FOREIGN KEY (updated_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_AE43DA141F6FA0AF FOREIGN KEY (deleted_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_AE43DA145D5A2101 ON gasto (local_id)');
        $this->addSql('CREATE INDEX IDX_AE43DA14DE12AB56 ON gasto (created_by)');
        $this->addSql('CREATE INDEX IDX_AE43DA1416FE72E1 ON gasto (updated_by)');
        $this->addSql('CREATE INDEX IDX_AE43DA141F6FA0AF ON gasto (deleted_by)');
        $this->addSql('CREATE INDEX idx_gasto_fecha ON gasto (fecha)');
        $this->addSql('CREATE INDEX idx_gasto_categoria ON gasto (categoria)');
        $this->addSql('CREATE INDEX idx_gasto_deleted_at ON gasto (deleted_at)');
        $this->addSql('CREATE TABLE ingreso (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, concepto VARCHAR(255) NOT NULL, importe NUMERIC(8, 2) NOT NULL, fecha_pago DATE NOT NULL, metodo_pago VARCHAR(50) DEFAULT NULL, categoria VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, contrato_id INTEGER NOT NULL, created_by VARCHAR(36) DEFAULT NULL, updated_by VARCHAR(36) DEFAULT NULL, deleted_by VARCHAR(36) DEFAULT NULL, CONSTRAINT FK_CC9B241F70AE7BF1 FOREIGN KEY (contrato_id) REFERENCES contrato (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CC9B241FDE12AB56 FOREIGN KEY (created_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CC9B241F16FE72E1 FOREIGN KEY (updated_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CC9B241F1F6FA0AF FOREIGN KEY (deleted_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CC9B241F70AE7BF1 ON ingreso (contrato_id)');
        $this->addSql('CREATE INDEX IDX_CC9B241FDE12AB56 ON ingreso (created_by)');
        $this->addSql('CREATE INDEX IDX_CC9B241F16FE72E1 ON ingreso (updated_by)');
        $this->addSql('CREATE INDEX IDX_CC9B241F1F6FA0AF ON ingreso (deleted_by)');
        $this->addSql('CREATE INDEX idx_ingreso_fecha_pago ON ingreso (fecha_pago)');
        $this->addSql('CREATE INDEX idx_ingreso_categoria ON ingreso (categoria)');
        $this->addSql('CREATE INDEX idx_ingreso_deleted_at ON ingreso (deleted_at)');
        $this->addSql('CREATE TABLE local (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, superficie_total NUMERIC(10, 2) DEFAULT NULL, numero_trasteros INTEGER DEFAULT NULL, fecha_compra DATE DEFAULT NULL, precio_compra NUMERIC(12, 2) DEFAULT NULL, referencia_catastral VARCHAR(50) DEFAULT NULL, valor_catastral NUMERIC(12, 2) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, direccion_id INTEGER NOT NULL, created_by VARCHAR(36) DEFAULT NULL, updated_by VARCHAR(36) DEFAULT NULL, deleted_by VARCHAR(36) DEFAULT NULL, CONSTRAINT FK_8BD688E8D0A7BD7 FOREIGN KEY (direccion_id) REFERENCES direccion (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8BD688E8DE12AB56 FOREIGN KEY (created_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8BD688E816FE72E1 FOREIGN KEY (updated_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8BD688E81F6FA0AF FOREIGN KEY (deleted_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8BD688E8D0A7BD7 ON local (direccion_id)');
        $this->addSql('CREATE INDEX IDX_8BD688E8DE12AB56 ON local (created_by)');
        $this->addSql('CREATE INDEX IDX_8BD688E816FE72E1 ON local (updated_by)');
        $this->addSql('CREATE INDEX IDX_8BD688E81F6FA0AF ON local (deleted_by)');
        $this->addSql('CREATE INDEX idx_local_deleted_at ON local (deleted_at)');
        $this->addSql('CREATE TABLE prestamo (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, entidad_bancaria VARCHAR(255) DEFAULT NULL, numero_prestamo VARCHAR(100) DEFAULT NULL, capital_solicitado NUMERIC(12, 2) NOT NULL, total_a_devolver NUMERIC(12, 2) NOT NULL, tipo_interes NUMERIC(5, 4) DEFAULT NULL, fecha_concesion DATE NOT NULL, estado VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, local_id INTEGER NOT NULL, created_by VARCHAR(36) DEFAULT NULL, updated_by VARCHAR(36) DEFAULT NULL, deleted_by VARCHAR(36) DEFAULT NULL, CONSTRAINT FK_F4D874F25D5A2101 FOREIGN KEY (local_id) REFERENCES local (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F4D874F2DE12AB56 FOREIGN KEY (created_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F4D874F216FE72E1 FOREIGN KEY (updated_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F4D874F21F6FA0AF FOREIGN KEY (deleted_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F4D874F25D5A2101 ON prestamo (local_id)');
        $this->addSql('CREATE INDEX IDX_F4D874F2DE12AB56 ON prestamo (created_by)');
        $this->addSql('CREATE INDEX IDX_F4D874F216FE72E1 ON prestamo (updated_by)');
        $this->addSql('CREATE INDEX IDX_F4D874F21F6FA0AF ON prestamo (deleted_by)');
        $this->addSql('CREATE INDEX idx_prestamo_estado ON prestamo (estado)');
        $this->addSql('CREATE INDEX idx_prestamo_fecha_concesion ON prestamo (fecha_concesion)');
        $this->addSql('CREATE INDEX idx_prestamo_entidad_bancaria ON prestamo (entidad_bancaria)');
        $this->addSql('CREATE INDEX idx_prestamo_deleted_at ON prestamo (deleted_at)');
        $this->addSql('CREATE TABLE trastero (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, numero VARCHAR(20) NOT NULL, nombre VARCHAR(100) DEFAULT NULL, superficie NUMERIC(6, 2) NOT NULL, precio_mensual NUMERIC(8, 2) NOT NULL, estado VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, local_id INTEGER NOT NULL, created_by VARCHAR(36) DEFAULT NULL, updated_by VARCHAR(36) DEFAULT NULL, deleted_by VARCHAR(36) DEFAULT NULL, CONSTRAINT FK_21509E5C5D5A2101 FOREIGN KEY (local_id) REFERENCES local (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_21509E5CDE12AB56 FOREIGN KEY (created_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_21509E5C16FE72E1 FOREIGN KEY (updated_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_21509E5C1F6FA0AF FOREIGN KEY (deleted_by) REFERENCES usuario (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_21509E5C5D5A2101 ON trastero (local_id)');
        $this->addSql('CREATE INDEX IDX_21509E5CDE12AB56 ON trastero (created_by)');
        $this->addSql('CREATE INDEX IDX_21509E5C16FE72E1 ON trastero (updated_by)');
        $this->addSql('CREATE INDEX IDX_21509E5C1F6FA0AF ON trastero (deleted_by)');
        $this->addSql('CREATE INDEX idx_trastero_estado ON trastero (estado)');
        $this->addSql('CREATE INDEX idx_trastero_deleted_at ON trastero (deleted_at)');
        $this->addSql('CREATE UNIQUE INDEX unique_trastero_local ON trastero (local_id, numero)');
        $this->addSql('CREATE TABLE usuario (id VARCHAR(36) NOT NULL, nombre VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, rol VARCHAR(20) NOT NULL, activo BOOLEAN NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2265B05DE7927C74 ON usuario (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cliente');
        $this->addSql('DROP TABLE contrato');
        $this->addSql('DROP TABLE direccion');
        $this->addSql('DROP TABLE gasto');
        $this->addSql('DROP TABLE ingreso');
        $this->addSql('DROP TABLE local');
        $this->addSql('DROP TABLE prestamo');
        $this->addSql('DROP TABLE trastero');
        $this->addSql('DROP TABLE usuario');
    }
}
