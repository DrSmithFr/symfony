<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250918202531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'YT Series Entities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categories (id SERIAL NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3AF34668989D9B62 ON categories (slug)');
        $this->addSql('CREATE TABLE episodes (id SERIAL NOT NULL, season_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, rank INT NOT NULL, name VARCHAR(255) NOT NULL, duration INT DEFAULT NULL, import_code VARCHAR(255) DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7DD55EDD4EC001D1 ON episodes (season_id)');
        $this->addSql('CREATE TABLE historic (id SERIAL NOT NULL, user_uuid UUID DEFAULT NULL, series_id INT DEFAULT NULL, episode_id INT DEFAULT NULL, time_code INT DEFAULT 0 NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AD52EF56ABFE1C6F ON historic (user_uuid)');
        $this->addSql('CREATE INDEX IDX_AD52EF565278319C ON historic (series_id)');
        $this->addSql('CREATE INDEX IDX_AD52EF56362B62A0 ON historic (episode_id)');
        $this->addSql('COMMENT ON COLUMN historic.user_uuid IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE seasons (id SERIAL NOT NULL, series_id INT DEFAULT NULL, rank INT NOT NULL, name VARCHAR(255) NOT NULL, import_code VARCHAR(255) DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B4F4301C5278319C ON seasons (series_id)');
        $this->addSql('CREATE TABLE series (id SERIAL NOT NULL, series_type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, description VARCHAR(255) NOT NULL, import_code VARCHAR(255) DEFAULT NULL, tags TEXT DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3A10012D9D47B361 ON series (series_type_id)');
        $this->addSql('COMMENT ON COLUMN series.tags IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE mtm_series_to_categories (series_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(series_id, category_id))');
        $this->addSql('CREATE INDEX IDX_D87FDB7D5278319C ON mtm_series_to_categories (series_id)');
        $this->addSql('CREATE INDEX IDX_D87FDB7D12469DE2 ON mtm_series_to_categories (category_id)');
        $this->addSql('CREATE TABLE series_type (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, import_code VARCHAR(255) DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE episodes ADD CONSTRAINT FK_7DD55EDD4EC001D1 FOREIGN KEY (season_id) REFERENCES seasons (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE historic ADD CONSTRAINT FK_AD52EF56ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE historic ADD CONSTRAINT FK_AD52EF565278319C FOREIGN KEY (series_id) REFERENCES series (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE historic ADD CONSTRAINT FK_AD52EF56362B62A0 FOREIGN KEY (episode_id) REFERENCES episodes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE seasons ADD CONSTRAINT FK_B4F4301C5278319C FOREIGN KEY (series_id) REFERENCES series (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE series ADD CONSTRAINT FK_3A10012D9D47B361 FOREIGN KEY (series_type_id) REFERENCES series_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtm_series_to_categories ADD CONSTRAINT FK_D87FDB7D5278319C FOREIGN KEY (series_id) REFERENCES series (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mtm_series_to_categories ADD CONSTRAINT FK_D87FDB7D12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE episodes DROP CONSTRAINT FK_7DD55EDD4EC001D1');
        $this->addSql('ALTER TABLE historic DROP CONSTRAINT FK_AD52EF56ABFE1C6F');
        $this->addSql('ALTER TABLE historic DROP CONSTRAINT FK_AD52EF565278319C');
        $this->addSql('ALTER TABLE historic DROP CONSTRAINT FK_AD52EF56362B62A0');
        $this->addSql('ALTER TABLE seasons DROP CONSTRAINT FK_B4F4301C5278319C');
        $this->addSql('ALTER TABLE series DROP CONSTRAINT FK_3A10012D9D47B361');
        $this->addSql('ALTER TABLE mtm_series_to_categories DROP CONSTRAINT FK_D87FDB7D5278319C');
        $this->addSql('ALTER TABLE mtm_series_to_categories DROP CONSTRAINT FK_D87FDB7D12469DE2');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE episodes');
        $this->addSql('DROP TABLE historic');
        $this->addSql('DROP TABLE seasons');
        $this->addSql('DROP TABLE series');
        $this->addSql('DROP TABLE mtm_series_to_categories');
        $this->addSql('DROP TABLE series_type');
    }
}
