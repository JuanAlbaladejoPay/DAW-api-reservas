<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240417161220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Instalaciones (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, precioHora DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Reservas (id INT AUTO_INCREMENT NOT NULL, fecha DATE NOT NULL, hora TIME NOT NULL, duracion INT NOT NULL, importe DOUBLE PRECISION NOT NULL, idUsuario INT NOT NULL, idInstalacion INT NOT NULL, INDEX IDX_536BC95732DCDBAF (idUsuario), UNIQUE INDEX UNIQ_536BC957FE06F768 (idInstalacion), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Usuarios (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nombre VARCHAR(255) NOT NULL, apellidos VARCHAR(255) NOT NULL, telefono INT NOT NULL, UNIQUE INDEX UNIQ_F780E5A4E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Reservas ADD CONSTRAINT FK_536BC95732DCDBAF FOREIGN KEY (idUsuario) REFERENCES Usuarios (id)');
        $this->addSql('ALTER TABLE Reservas ADD CONSTRAINT FK_536BC957FE06F768 FOREIGN KEY (idInstalacion) REFERENCES Instalaciones (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Reservas DROP FOREIGN KEY FK_536BC95732DCDBAF');
        $this->addSql('ALTER TABLE Reservas DROP FOREIGN KEY FK_536BC957FE06F768');
        $this->addSql('DROP TABLE Instalaciones');
        $this->addSql('DROP TABLE Reservas');
        $this->addSql('DROP TABLE Usuarios');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
