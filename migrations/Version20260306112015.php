<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306112015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE intervention_unite (intervention_id INT NOT NULL, unite_id INT NOT NULL, INDEX IDX_6801DD9C8EAE3863 (intervention_id), INDEX IDX_6801DD9CEC4A74AB (unite_id), PRIMARY KEY (intervention_id, unite_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE intervention_unite ADD CONSTRAINT FK_6801DD9C8EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intervention_unite ADD CONSTRAINT FK_6801DD9CEC4A74AB FOREIGN KEY (unite_id) REFERENCES unite (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unite ADD CONSTRAINT FK_1D64C11843375062 FOREIGN KEY (baie_id) REFERENCES baie (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention_unite DROP FOREIGN KEY FK_6801DD9C8EAE3863');
        $this->addSql('ALTER TABLE intervention_unite DROP FOREIGN KEY FK_6801DD9CEC4A74AB');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE intervention_unite');
        $this->addSql('ALTER TABLE unite DROP FOREIGN KEY FK_1D64C11843375062');
    }
}
