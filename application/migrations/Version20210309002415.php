<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210309002415 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

DELETE ssc1 FROM show_season_score ssc1
INNER JOIN show_season_score ssc2
WHERE
    ssc1.id > ssc2.id
    AND ssc1.season_id = ssc2.season_id
    AND ssc1.show_id = ssc2.show_id
    AND ssc1.user_id = ssc2.user_id
    ;

CREATE UNIQUE INDEX show_season_score_unique ON show_season_score (season_id, show_id, user_id);

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

DROP INDEX show_season_score_unique;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
