<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230223100220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, machine_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date DATE NOT NULL, state VARCHAR(255) NOT NULL, INDEX IDX_97A0ADA39395C3F3 (customer_id), INDEX IDX_97A0ADA3F6B75B26 (machine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA39395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3F6B75B26 FOREIGN KEY (machine_id) REFERENCES machine (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA39395C3F3');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3F6B75B26');
        $this->addSql('DROP TABLE ticket');
    }
}
