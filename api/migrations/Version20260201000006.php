<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201000006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cliente table for Cliente module';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cliente (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            apellidos VARCHAR(200) NOT NULL,
            dni_nie VARCHAR(20) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            telefono VARCHAR(20) DEFAULT NULL,
            activo BOOLEAN DEFAULT TRUE,
            created_at DATETIME NOT NULL,
            created_by INT DEFAULT NULL,
            updated_at DATETIME NOT NULL,
            updated_by INT DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            deleted_by INT DEFAULT NULL,
            CONSTRAINT fk_cliente_created_by FOREIGN KEY (created_by) REFERENCES usuario(id) ON DELETE SET NULL,
            CONSTRAINT fk_cliente_updated_by FOREIGN KEY (updated_by) REFERENCES usuario(id) ON DELETE SET NULL,
            CONSTRAINT fk_cliente_deleted_by FOREIGN KEY (deleted_by) REFERENCES usuario(id) ON DELETE SET NULL
        )');

        $this->addSql('CREATE INDEX idx_cliente_dni_nie ON cliente (dni_nie)');
        $this->addSql('CREATE INDEX idx_cliente_email ON cliente (email)');
        $this->addSql('CREATE INDEX idx_cliente_nombre ON cliente (nombre)');
        $this->addSql('CREATE INDEX idx_cliente_apellidos ON cliente (apellidos)');
        $this->addSql('CREATE INDEX idx_cliente_activo ON cliente (activo)');
        $this->addSql('CREATE INDEX idx_cliente_deleted_at ON cliente (deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cliente');
    }
}
