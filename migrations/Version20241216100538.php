<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216100538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invitation (id SERIAL NOT NULL, event_id INT NOT NULL, receiver_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F11D61A271F7E88B ON invitation (event_id)');
        $this->addSql('CREATE INDEX IDX_F11D61A2CD53EDB6 ON invitation (receiver_id)');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT FK_F11D61A271F7E88B');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT FK_F11D61A2CD53EDB6');
        $this->addSql('DROP TABLE invitation');
    }
}
