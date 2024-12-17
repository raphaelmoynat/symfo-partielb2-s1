<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241217082608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE charge (id SERIAL NOT NULL, participant_id INT NOT NULL, suggestion_id INT DEFAULT NULL, contribution_id INT NOT NULL, name TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_556BA4349D1C3019 ON charge (participant_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_556BA434A41BB822 ON charge (suggestion_id)');
        $this->addSql('CREATE INDEX IDX_556BA434FE5E5FBD ON charge (contribution_id)');
        $this->addSql('ALTER TABLE charge ADD CONSTRAINT FK_556BA4349D1C3019 FOREIGN KEY (participant_id) REFERENCES profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE charge ADD CONSTRAINT FK_556BA434A41BB822 FOREIGN KEY (suggestion_id) REFERENCES suggestion (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE charge ADD CONSTRAINT FK_556BA434FE5E5FBD FOREIGN KEY (contribution_id) REFERENCES contribution (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE suggestion ADD is_taken BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE suggestion DROP status');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE charge DROP CONSTRAINT FK_556BA4349D1C3019');
        $this->addSql('ALTER TABLE charge DROP CONSTRAINT FK_556BA434A41BB822');
        $this->addSql('ALTER TABLE charge DROP CONSTRAINT FK_556BA434FE5E5FBD');
        $this->addSql('DROP TABLE charge');
        $this->addSql('ALTER TABLE suggestion ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE suggestion DROP is_taken');
    }
}
