<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240501141458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservas DROP FOREIGN KEY FK_536BC957FE06F768');
        $this->addSql('DROP INDEX UNIQ_536BC957FE06F768 ON reservas');
        $this->addSql('ALTER TABLE reservas CHANGE idInstalacion id_instalacion_id INT NOT NULL');
        $this->addSql('ALTER TABLE reservas ADD CONSTRAINT FK_536BC9577361D98C FOREIGN KEY (id_instalacion_id) REFERENCES Instalaciones (id)');
        $this->addSql('CREATE INDEX IDX_536BC9577361D98C ON reservas (id_instalacion_id)');
        $this->addSql('ALTER TABLE usuarios CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE Reservas DROP FOREIGN KEY FK_536BC9577361D98C');
        $this->addSql('DROP INDEX IDX_536BC9577361D98C ON Reservas');
        $this->addSql('ALTER TABLE Reservas CHANGE id_instalacion_id idInstalacion INT NOT NULL');
        $this->addSql('ALTER TABLE Reservas ADD CONSTRAINT FK_536BC957FE06F768 FOREIGN KEY (idInstalacion) REFERENCES instalaciones (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_536BC957FE06F768 ON Reservas (idInstalacion)');
        $this->addSql('ALTER TABLE Usuarios CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
