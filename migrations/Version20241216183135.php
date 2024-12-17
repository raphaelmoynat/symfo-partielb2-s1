<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216183135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE taken_charge ADD contribution_id INT NOT NULL');
        $this->addSql('ALTER TABLE taken_charge ADD name TEXT NOT NULL');
        $this->addSql('ALTER TABLE taken_charge ADD CONSTRAINT FK_6652030FE5E5FBD FOREIGN KEY (contribution_id) REFERENCES contribution (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6652030FE5E5FBD ON taken_charge (contribution_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE taken_charge DROP CONSTRAINT FK_6652030FE5E5FBD');
        $this->addSql('DROP INDEX IDX_6652030FE5E5FBD');
        $this->addSql('ALTER TABLE taken_charge DROP contribution_id');
        $this->addSql('ALTER TABLE taken_charge DROP name');
    }
}
