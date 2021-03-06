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
final class Version20210209072423 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds the show_season_vote table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE show_season_vote (id INT AUTO_INCREMENT NOT NULL, anime_show_id INT NOT NULL, season_id INT NOT NULL, user_id INT NOT NULL, chosen TINYINT(1) NOT NULL, INDEX IDX_735DE9B2C5ADDBA9 (anime_show_id), INDEX IDX_735DE9B24EC001D1 (season_id), INDEX IDX_735DE9B2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE show_season_vote ADD CONSTRAINT FK_735DE9B2C5ADDBA9 FOREIGN KEY (anime_show_id) REFERENCES anime_show (id)');
        $this->addSql('ALTER TABLE show_season_vote ADD CONSTRAINT FK_735DE9B24EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE show_season_vote ADD CONSTRAINT FK_735DE9B2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE show_season_vote');
    }
}
