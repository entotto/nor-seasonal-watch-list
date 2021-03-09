<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210309004953 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds unique constraint on votes per user, show, season and election';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

DELETE ev1 FROM show_season_score ev1
INNER JOIN show_season_score ev2
WHERE
    ev1.id > ev2.id
    AND ev1.season_id = ev2.season_id
    AND ev1.show_id = ev2.show_id
    AND ev1.user_id = ev2.user_id
    AND ev1.election_id = ev2.election_id
    ;

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

DROP INDEX election_vote_unique ON election_vote;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
