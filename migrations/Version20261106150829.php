<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261106150829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the initial PostgreSQL schema for the quiz application.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE bonus_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE compte_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE contact_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE historique_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE profil_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE question_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE reponse_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE reponse_bonus_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bonus (
          id INT NOT NULL,
          designation VARCHAR(255) DEFAULT NULL,
          image VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE compte (
          id INT NOT NULL,
          nom VARCHAR(255) DEFAULT NULL,
          email VARCHAR(255) DEFAULT NULL,
          avatar VARCHAR(255) DEFAULT NULL,
          roles JSON DEFAULT NULL,
          password VARCHAR(255) DEFAULT NULL,
          remember_token VARCHAR(255) DEFAULT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
          username VARCHAR(255) DEFAULT NULL,
          salt VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE contact (
          id INT NOT NULL,
          email VARCHAR(255) DEFAULT NULL,
          nom VARCHAR(255) DEFAULT NULL,
          telephone VARCHAR(255) DEFAULT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
          updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE historique (
          id INT NOT NULL,
          question VARCHAR(255) DEFAULT NULL,
          reponse VARCHAR(255) DEFAULT NULL,
          utilisateur VARCHAR(255) DEFAULT NULL,
          note NUMERIC(10, 2) DEFAULT NULL,
          numero_test INT DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE profil (
          id INT NOT NULL,
          designation VARCHAR(255) DEFAULT NULL,
          combinaison VARCHAR(255) DEFAULT NULL,
          image VARCHAR(255) DEFAULT NULL,
          pdf VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE question (
          id INT NOT NULL,
          type_id INT DEFAULT NULL,
          designation VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_B6F7494EC54C8C93 ON question (type_id)');
        $this->addSql('CREATE TABLE reponse (
          id INT NOT NULL,
          question_id INT DEFAULT NULL,
          designation VARCHAR(255) DEFAULT NULL,
          note NUMERIC(10, 2) DEFAULT NULL,
          image VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_5FB6DEC71E27F6BF ON reponse (question_id)');
        $this->addSql('CREATE TABLE reponse_bonus (
          id INT NOT NULL,
          bonus_id INT DEFAULT NULL,
          type_id INT DEFAULT NULL,
          designation VARCHAR(255) DEFAULT NULL,
          note NUMERIC(10, 2) DEFAULT NULL,
          image VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_7071041969545666 ON reponse_bonus (bonus_id)');
        $this->addSql('CREATE INDEX IDX_70710419C54C8C93 ON reponse_bonus (type_id)');
        $this->addSql('CREATE TABLE type (
          id INT NOT NULL,
          designation VARCHAR(255) DEFAULT NULL,
          image VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE messenger_messages (
          id BIGSERIAL NOT NULL,
          body TEXT NOT NULL,
          headers TEXT NOT NULL,
          queue_name VARCHAR(190) NOT NULL,
          created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
          delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
          PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$ BEGIN PERFORM pg_notify(\'messenger_messages\', NEW.queue_name); RETURN NEW; END; $$ LANGUAGE plpgsql');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE FUNCTION notify_messenger_messages()');
        $this->addSql('ALTER TABLE
          question
        ADD
          CONSTRAINT FK_B6F7494EC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          reponse
        ADD
          CONSTRAINT FK_5FB6DEC71E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          reponse_bonus
        ADD
          CONSTRAINT FK_7071041969545666 FOREIGN KEY (bonus_id) REFERENCES bonus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE
          reponse_bonus
        ADD
          CONSTRAINT FK_70710419C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE bonus_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE compte_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE contact_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE historique_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE profil_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE question_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE reponse_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE reponse_bonus_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE type_id_seq CASCADE');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494EC54C8C93');
        $this->addSql('ALTER TABLE reponse DROP CONSTRAINT FK_5FB6DEC71E27F6BF');
        $this->addSql('ALTER TABLE reponse_bonus DROP CONSTRAINT FK_7071041969545666');
        $this->addSql('ALTER TABLE reponse_bonus DROP CONSTRAINT FK_70710419C54C8C93');
        $this->addSql('DROP TABLE bonus');
        $this->addSql('DROP TABLE compte');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE historique');
        $this->addSql('DROP TABLE profil');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE reponse_bonus');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP FUNCTION IF EXISTS notify_messenger_messages()');
    }
}
