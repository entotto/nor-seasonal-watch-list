<?php /** @noinspection PhpUnused */
/** @noinspection UnknownInspectionInspection */
/** @noinspection SqlResolve */
/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210209060444 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds the discord_channel table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE discord_channel (id INT AUTO_INCREMENT NOT NULL, season_id INT NOT NULL, anime_show_id INT NOT NULL, name VARCHAR(255) NOT NULL, hidden TINYINT(1) NOT NULL, INDEX IDX_E664AA1C4EC001D1 (season_id), UNIQUE INDEX UNIQ_E664AA1CC5ADDBA9 (anime_show_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discord_channel ADD CONSTRAINT FK_E664AA1C4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE discord_channel ADD CONSTRAINT FK_E664AA1CC5ADDBA9 FOREIGN KEY (anime_show_id) REFERENCES anime_show (id)');
        $this->addSql('ALTER TABLE anime_show ADD discord_channel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime_show ADD CONSTRAINT FK_9DFB0B556D4A6EE0 FOREIGN KEY (discord_channel_id) REFERENCES discord_channel (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9DFB0B556D4A6EE0 ON anime_show (discord_channel_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anime_show DROP FOREIGN KEY FK_9DFB0B556D4A6EE0');
        $this->addSql('DROP TABLE discord_channel');
        $this->addSql('DROP INDEX UNIQ_9DFB0B556D4A6EE0 ON anime_show');
        $this->addSql('ALTER TABLE anime_show DROP discord_channel_id');
    }
}
