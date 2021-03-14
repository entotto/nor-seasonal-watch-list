<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection SqlResolve */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210314151928 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds the activity table';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE activity (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    nickname VARCHAR(50) NOT NULL,
    rank_order INT NOT NULL,
    value NUMERIC(5, 1) DEFAULT NULL,
    color_value VARCHAR(50) NOT NULL,
    icon VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE UNIQUE INDEX election_vote_unique ON election_vote (anime_show_id, season_id, user_id, election_id);

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }

    public function down(Schema $schema) : void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE activity;
DROP INDEX election_vote_unique ON election_vote;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
