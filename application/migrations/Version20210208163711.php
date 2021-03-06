<?php /** @noinspection PhpUnused */
/** @noinspection UnknownInspectionInspection */
/** @noinspection SqlResolve */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210208163711 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Connects show_season_score table to the new score table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE show_season_score DROP score_name, CHANGE score score_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE show_season_score ADD CONSTRAINT FK_5D000DDC12EB0A51 FOREIGN KEY (score_id) REFERENCES score (id)');
        $this->addSql('CREATE INDEX IDX_5D000DDC12EB0A51 ON show_season_score (score_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE show_season_score DROP FOREIGN KEY FK_5D000DDC12EB0A51');
        $this->addSql('DROP INDEX IDX_5D000DDC12EB0A51 ON show_season_score');
        $this->addSql('ALTER TABLE show_season_score ADD score_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE score_id score INT DEFAULT NULL');
    }
}
