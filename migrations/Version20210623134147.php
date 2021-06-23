<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210623134147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product_in_store AS SELECT id, amount, name FROM product_in_store');
        $this->addSql('DROP TABLE product_in_store');
        $this->addSql('CREATE TABLE product_in_store (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, amount INTEGER NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO product_in_store (id, amount, name) SELECT id, amount, name FROM __temp__product_in_store');
        $this->addSql('DROP TABLE __temp__product_in_store');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product_in_store AS SELECT id, name, amount FROM product_in_store');
        $this->addSql('DROP TABLE product_in_store');
        $this->addSql('CREATE TABLE product_in_store (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, amount INTEGER NOT NULL, name VARCHAR(5) NOT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO product_in_store (id, name, amount) SELECT id, name, amount FROM __temp__product_in_store');
        $this->addSql('DROP TABLE __temp__product_in_store');
    }
}
