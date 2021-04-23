<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210422134128 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE im2021_paniers ADD COLUMN quantite INTEGER DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__im2021_paniers AS SELECT pk_panier, utilisateur, produit FROM im2021_paniers');
        $this->addSql('DROP TABLE im2021_paniers');
        $this->addSql('CREATE TABLE im2021_paniers (pk_panier INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, utilisateur VARCHAR(255) NOT NULL, produit VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO im2021_paniers (pk_panier, utilisateur, produit) SELECT pk_panier, utilisateur, produit FROM __temp__im2021_paniers');
        $this->addSql('DROP TABLE __temp__im2021_paniers');
    }
}
