<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216205149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE charge DROP CONSTRAINT FK_556BA434A41BB822');
        $this->addSql('ALTER TABLE charge ADD CONSTRAINT FK_556BA434A41BB822 FOREIGN KEY (suggestion_id) REFERENCES suggestion (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE charge DROP CONSTRAINT fk_556ba434a41bb822');
        $this->addSql('ALTER TABLE charge ADD CONSTRAINT fk_556ba434a41bb822 FOREIGN KEY (suggestion_id) REFERENCES suggestion (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
