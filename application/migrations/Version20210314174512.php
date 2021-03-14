<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210314174512 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds link to Activity from ShowSeasonScore';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE show_season_score ADD activity_id INT DEFAULT NULL;
ALTER TABLE show_season_score ADD CONSTRAINT FK_5D000DDC81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id);
CREATE INDEX IDX_5D000DDC81C06096 ON show_season_score (activity_id);

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

ALTER TABLE show_season_score DROP FOREIGN KEY FK_5D000DDC81C06096;
DROP INDEX IDX_5D000DDC81C06096 ON show_season_score;
ALTER TABLE show_season_score DROP activity_id;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
