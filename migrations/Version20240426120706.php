<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240426120706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE album (artist_id VARCHAR(90) NOT NULL, idAlbum VARCHAR(90) NOT NULL, nom VARCHAR(90) NOT NULL, categ VARCHAR(20) NOT NULL, cover VARCHAR(125) NOT NULL, actif INT NOT NULL, year INT NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', visibility TINYINT(1) NOT NULL, INDEX IDX_39986E43B7970CF8 (artist_id), PRIMARY KEY(idAlbum)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE artist (fullname VARCHAR(90) NOT NULL, description LONGTEXT DEFAULT NULL, actif INT NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL, artistId VARCHAR(90) NOT NULL, UNIQUE INDEX UNIQ_159968791657DAE (fullname), PRIMARY KEY(artistId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE artist_has_label (id INT AUTO_INCREMENT NOT NULL, id_label_id VARCHAR(20) NOT NULL, id_artist_id VARCHAR(90) NOT NULL, added_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', quitted_at DATETIME DEFAULT NULL, INDEX IDX_E9FA2BDE6362C3AC (id_label_id), INDEX IDX_E9FA2BDE37A2B0DF (id_artist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE label (id_label VARCHAR(20) NOT NULL, name VARCHAR(50) NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', year INT DEFAULT NULL, PRIMARY KEY(id_label)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE playlist (id_playlist VARCHAR(90) NOT NULL, playlist_has_song_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL, public TINYINT(1) NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL, INDEX IDX_D782112DE2815C07 (playlist_has_song_id), PRIMARY KEY(id_playlist)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE playlist_has_song (id INT AUTO_INCREMENT NOT NULL, download TINYINT(1) DEFAULT NULL, position SMALLINT DEFAULT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE song (playlist_has_song_id INT DEFAULT NULL, idSong VARCHAR(90) NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(125) NOT NULL, cover VARCHAR(125) NOT NULL, visibility TINYINT(1) NOT NULL, actif INT NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', albumId VARCHAR(90) NOT NULL, INDEX IDX_33EDEEA15BE32AF3 (albumId), INDEX IDX_33EDEEA1E2815C07 (playlist_has_song_id), PRIMARY KEY(idSong)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE artist_Id (song_id VARCHAR(90) NOT NULL, artist_id VARCHAR(90) NOT NULL, INDEX IDX_2213285AA0BDB2F3 (song_id), INDEX IDX_2213285AB7970CF8 (artist_id), PRIMARY KEY(song_id, artist_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (idUser VARCHAR(90) NOT NULL, firstname VARCHAR(55) NOT NULL, lastname VARCHAR(55) NOT NULL, email VARCHAR(80) NOT NULL, tel VARCHAR(15) DEFAULT NULL, sexe INT NOT NULL, birthday DATE NOT NULL, password VARCHAR(90) NOT NULL, nb_tentative INT NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL, actif INT NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(idUser)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE following (artist_id VARCHAR(90) NOT NULL, idUser VARCHAR(90) NOT NULL, INDEX IDX_71BF8DE3FE6E88D7 (idUser), INDEX IDX_71BF8DE3B7970CF8 (artist_id), PRIMARY KEY(idUser, artist_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E43B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (artistId)');
        $this->addSql('ALTER TABLE artist ADD CONSTRAINT FK_159968747137A56 FOREIGN KEY (artistId) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE artist_has_label ADD CONSTRAINT FK_E9FA2BDE6362C3AC FOREIGN KEY (id_label_id) REFERENCES label (id_label)');
        $this->addSql('ALTER TABLE artist_has_label ADD CONSTRAINT FK_E9FA2BDE37A2B0DF FOREIGN KEY (id_artist_id) REFERENCES artist (artistId)');
        $this->addSql('ALTER TABLE playlist ADD CONSTRAINT FK_D782112DE2815C07 FOREIGN KEY (playlist_has_song_id) REFERENCES playlist_has_song (id)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA15BE32AF3 FOREIGN KEY (albumId) REFERENCES album (idAlbum)');
        $this->addSql('ALTER TABLE song ADD CONSTRAINT FK_33EDEEA1E2815C07 FOREIGN KEY (playlist_has_song_id) REFERENCES playlist_has_song (id)');
        $this->addSql('ALTER TABLE artist_Id ADD CONSTRAINT FK_2213285AA0BDB2F3 FOREIGN KEY (song_id) REFERENCES song (idSong)');
        $this->addSql('ALTER TABLE artist_Id ADD CONSTRAINT FK_2213285AB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (artistId)');
        $this->addSql('ALTER TABLE following ADD CONSTRAINT FK_71BF8DE3FE6E88D7 FOREIGN KEY (idUser) REFERENCES user (idUser)');
        $this->addSql('ALTER TABLE following ADD CONSTRAINT FK_71BF8DE3B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (artistId)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE album DROP FOREIGN KEY FK_39986E43B7970CF8');
        $this->addSql('ALTER TABLE artist DROP FOREIGN KEY FK_159968747137A56');
        $this->addSql('ALTER TABLE artist_has_label DROP FOREIGN KEY FK_E9FA2BDE6362C3AC');
        $this->addSql('ALTER TABLE artist_has_label DROP FOREIGN KEY FK_E9FA2BDE37A2B0DF');
        $this->addSql('ALTER TABLE playlist DROP FOREIGN KEY FK_D782112DE2815C07');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA15BE32AF3');
        $this->addSql('ALTER TABLE song DROP FOREIGN KEY FK_33EDEEA1E2815C07');
        $this->addSql('ALTER TABLE artist_Id DROP FOREIGN KEY FK_2213285AA0BDB2F3');
        $this->addSql('ALTER TABLE artist_Id DROP FOREIGN KEY FK_2213285AB7970CF8');
        $this->addSql('ALTER TABLE following DROP FOREIGN KEY FK_71BF8DE3FE6E88D7');
        $this->addSql('ALTER TABLE following DROP FOREIGN KEY FK_71BF8DE3B7970CF8');
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE artist_has_label');
        $this->addSql('DROP TABLE label');
        $this->addSql('DROP TABLE playlist');
        $this->addSql('DROP TABLE playlist_has_song');
        $this->addSql('DROP TABLE song');
        $this->addSql('DROP TABLE artist_Id');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE following');
    }
}
