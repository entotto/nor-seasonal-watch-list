<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210126051518 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `show` (id INT AUTO_INCREMENT NOT NULL, japanese_title VARCHAR(255) DEFAULT NULL, english_title VARCHAR(255) DEFAULT NULL, full_japanese_title LONGTEXT DEFAULT NULL, full_english_title LONGTEXT DEFAULT NULL, anilist_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE show_season (show_id INT NOT NULL, season_id INT NOT NULL, INDEX IDX_7F837432D0C1FC64 (show_id), INDEX IDX_7F8374324EC001D1 (season_id), PRIMARY KEY(show_id, season_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE show_season ADD CONSTRAINT FK_7F837432D0C1FC64 FOREIGN KEY (show_id) REFERENCES `show` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE show_season ADD CONSTRAINT FK_7F8374324EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE show_season DROP FOREIGN KEY FK_7F837432D0C1FC64');
        $this->addSql('DROP TABLE `show`');
        $this->addSql('DROP TABLE show_season');
    }
}
