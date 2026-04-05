<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401234057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE progression DROP FOREIGN KEY `progression_ibfk_1`');
        $this->addSql('ALTER TABLE tentative DROP FOREIGN KEY `tentative_ibfk_1`');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE progression');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE tentative');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_badge');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY `fk_activite_jeu`');
        $this->addSql('ALTER TABLE activite DROP score, DROP duree, DROP resultat, DROP type_activite, CHANGE bonne_reponse bonne_reponse VARCHAR(255) DEFAULT NULL, CHANGE jeu_id jeu_id INT NOT NULL, CHANGE question question LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B87555158C9E392E FOREIGN KEY (jeu_id) REFERENCES jeu_educatif (id)');
        $this->addSql('ALTER TABLE activite RENAME INDEX fk_activite_jeu TO IDX_B87555158C9E392E');
        $this->addSql('ALTER TABLE jeu_educatif CHANGE description description LONGTEXT DEFAULT NULL, CHANGE deleted deleted TINYINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, img VARCHAR(500) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, face_id VARCHAR(200) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, UNIQUE INDEX email (email), INDEX idx_admin_face_id (face_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE badge (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, icone VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE progression (id INT AUTO_INCREMENT NOT NULL, session_id INT NOT NULL, competence VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, domaine ENUM(\'COMMUNICATION\', \'MOTRICITE\', \'SOCIAL\', \'COGNITIF\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, niveau_initial INT NOT NULL, niveau_actuel INT NOT NULL, evaluation TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, recompense VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, objectif_atteint TINYINT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX idx_progression_domaine (domaine), INDEX idx_progression_session (session_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, type ENUM(\'SENSORIEL\', \'COMMUNICATION\', \'COMPORTEMENTAL\', \'SOCIAL\', \'COGNITIF\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, date DATE NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, objectif TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, materiel VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, duree_minutes INT NOT NULL, pictogrammes TINYINT DEFAULT 0, routine TINYINT DEFAULT 0, recompense VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, notes TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, niveau_anxiete INT DEFAULT 0, accompagnement_parent TINYINT DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX idx_session_date (date), INDEX idx_session_type (type), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE tentative (id INT AUTO_INCREMENT NOT NULL, score INT DEFAULT NULL, duree INT DEFAULT NULL, resultat VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, activite_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX tentative_ibfk_1 (activite_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user (id_user INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prenom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, role ENUM(\'enfant\', \'professeur\') CHARACTER SET utf8mb4 DEFAULT \'enfant\' NOT NULL COLLATE `utf8mb4_general_ci`, img VARCHAR(500) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, face_id VARCHAR(200) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, UNIQUE INDEX email (email), INDEX idx_user_face_id (face_id), PRIMARY KEY (id_user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_badge (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, badge_id INT DEFAULT NULL, date_obtention DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE progression ADD CONSTRAINT `progression_ibfk_1` FOREIGN KEY (session_id) REFERENCES session (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tentative ADD CONSTRAINT `tentative_ibfk_1` FOREIGN KEY (activite_id) REFERENCES activite (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B87555158C9E392E');
        $this->addSql('ALTER TABLE activite ADD score INT DEFAULT NULL, ADD duree INT DEFAULT NULL, ADD resultat VARCHAR(50) DEFAULT NULL, ADD type_activite VARCHAR(50) DEFAULT NULL, CHANGE question question TEXT DEFAULT NULL, CHANGE bonne_reponse bonne_reponse VARCHAR(100) DEFAULT NULL, CHANGE jeu_id jeu_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT `fk_activite_jeu` FOREIGN KEY (jeu_id) REFERENCES jeu_educatif (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activite RENAME INDEX idx_b87555158c9e392e TO fk_activite_jeu');
        $this->addSql('ALTER TABLE jeu_educatif CHANGE description description TEXT DEFAULT NULL, CHANGE deleted deleted TINYINT DEFAULT 0');
    }
}
