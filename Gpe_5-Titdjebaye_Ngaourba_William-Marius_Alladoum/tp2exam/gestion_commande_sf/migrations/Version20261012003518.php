<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012003518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cmd_client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(80) NOT NULL, prenom VARCHAR(80) NOT NULL, email VARCHAR(150) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, region_id INT DEFAULT NULL, INDEX IDX_83CC231F98260155 (region_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cmd_commande (id INT AUTO_INCREMENT NOT NULL, date_commande DATE NOT NULL, client_id INT NOT NULL, INDEX idx_commande_date (date_commande), INDEX IDX_C176F01119EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cmd_departement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cmd_ligne_commande (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, commande_id INT NOT NULL, produit_id INT NOT NULL, UNIQUE INDEX uniq_ligne_commande_produit (commande_id, produit_id), INDEX IDX_DCA7064082EA2E54 (commande_id), INDEX IDX_DCA70640F347EFB (produit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cmd_produit (id INT AUTO_INCREMENT NOT NULL, designation VARCHAR(150) NOT NULL, stock INT NOT NULL, prix_unitaire INT NOT NULL, photo VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cmd_region (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, departement_id INT NOT NULL, INDEX IDX_4BEAD63CCCF9E01E (departement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE cmd_client ADD CONSTRAINT FK_83CC231F98260155 FOREIGN KEY (region_id) REFERENCES cmd_region (id)');
        $this->addSql('ALTER TABLE cmd_commande ADD CONSTRAINT FK_C176F01119EB6921 FOREIGN KEY (client_id) REFERENCES cmd_client (id)');
        $this->addSql('ALTER TABLE cmd_ligne_commande ADD CONSTRAINT FK_DCA7064082EA2E54 FOREIGN KEY (commande_id) REFERENCES cmd_commande (id)');
        $this->addSql('ALTER TABLE cmd_ligne_commande ADD CONSTRAINT FK_DCA70640F347EFB FOREIGN KEY (produit_id) REFERENCES cmd_produit (id)');
        $this->addSql('ALTER TABLE cmd_region ADD CONSTRAINT FK_4BEAD63CCCF9E01E FOREIGN KEY (departement_id) REFERENCES cmd_departement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cmd_client DROP FOREIGN KEY FK_83CC231F98260155');
        $this->addSql('ALTER TABLE cmd_commande DROP FOREIGN KEY FK_C176F01119EB6921');
        $this->addSql('ALTER TABLE cmd_ligne_commande DROP FOREIGN KEY FK_DCA7064082EA2E54');
        $this->addSql('ALTER TABLE cmd_ligne_commande DROP FOREIGN KEY FK_DCA70640F347EFB');
        $this->addSql('ALTER TABLE cmd_region DROP FOREIGN KEY FK_4BEAD63CCCF9E01E');
        $this->addSql('DROP TABLE cmd_client');
        $this->addSql('DROP TABLE cmd_commande');
        $this->addSql('DROP TABLE cmd_departement');
        $this->addSql('DROP TABLE cmd_ligne_commande');
        $this->addSql('DROP TABLE cmd_produit');
        $this->addSql('DROP TABLE cmd_region');
    }
}
