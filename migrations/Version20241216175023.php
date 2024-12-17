<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216175023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contribution (id SERIAL NOT NULL, event_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EA351E1571F7E88B ON contribution (event_id)');
        $this->addSql('CREATE TABLE suggestion (id SERIAL NOT NULL, contribution_id INT NOT NULL, name TEXT NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DD80F31BFE5E5FBD ON suggestion (contribution_id)');
        $this->addSql('CREATE TABLE taken_charge (id SERIAL NOT NULL, participant_id INT NOT NULL, suggestion_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_66520309D1C3019 ON taken_charge (participant_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6652030A41BB822 ON taken_charge (suggestion_id)');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E1571F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE suggestion ADD CONSTRAINT FK_DD80F31BFE5E5FBD FOREIGN KEY (contribution_id) REFERENCES contribution (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE taken_charge ADD CONSTRAINT FK_66520309D1C3019 FOREIGN KEY (participant_id) REFERENCES profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE taken_charge ADD CONSTRAINT FK_6652030A41BB822 FOREIGN KEY (suggestion_id) REFERENCES suggestion (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE contribution DROP CONSTRAINT FK_EA351E1571F7E88B');
        $this->addSql('ALTER TABLE suggestion DROP CONSTRAINT FK_DD80F31BFE5E5FBD');
        $this->addSql('ALTER TABLE taken_charge DROP CONSTRAINT FK_66520309D1C3019');
        $this->addSql('ALTER TABLE taken_charge DROP CONSTRAINT FK_6652030A41BB822');
        $this->addSql('DROP TABLE contribution');
        $this->addSql('DROP TABLE suggestion');
        $this->addSql('DROP TABLE taken_charge');
    }
}
