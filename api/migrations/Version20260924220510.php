<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924220510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store refresh tokens as SHA-256 hashes (existing sessions keep working)';
    }

    public function isTransactional(): bool
    {
        // Data only (no DDL), so the transaction does make it atomic
        return true;
    }

    public function up(Schema $schema): void
    {
        // Plain tokens are 128 hex chars (bin2hex of 64 random bytes); hashes
        // are 64, so this only touches tokens that aren't hashed yet. SHA2()
        // returns lowercase hex, like PHP's hash('sha256', ...).
        $this->addSql('UPDATE refresh_tokens SET token = SHA2(token, 256) WHERE CHAR_LENGTH(token) = 128');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Hashed refresh tokens can\'t be turned back into plain ones');
    }
}
