<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210322031145 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Reduces activity options';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

UPDATE show_season_score SET activity_id = 2 WHERE activity_id IN (3,4);
UPDATE show_season_score SET activity_id = 3 WHERE activity_id IN (5,6);

REPLACE INTO `activity` (`id`, `name`, `nickname`, `rank_order`, `value`, `color_value`, `icon`, `slug`) VALUES
(1, 'PTW (Plan to watch)', 'PTW',      1,  1, 'activity-ptw',      '', 'ptw'),
(2, 'Watching/Finished',   'Watching', 2,  2, 'activity-watching', '', 'watching'),
(3, '(Not watching)',      '(none)',   99, 0, 'activity-none',     '', 'none');

DELETE FROM `activity` WHERE `id` IN (4,5,6);

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

REPLACE INTO `activity` (`id`, `name`, `nickname`, `rank_order`, `value`, `color_value`, `icon`, `slug`) VALUES
(1, 'PTW / Plan to watch', 'PTW', 4, NULL, 'activity-ptw', '', 'ptw'),
(2, 'Watching', 'Watching', 2, NULL, 'activity-watching', '', 'watching'),
(3, 'Finished', 'Finished', 1, NULL, 'activity-finished', '', 'finished'),
(4, 'Paused', 'Paused', 3, NULL, 'activity-paused', '', 'paused'),
(5, 'Dropped', 'Dropped', 5, NULL, 'activity-dropped', '', 'dropped'),
(6, '(Not watching)', '(none)', 99, NULL, 'activity-none', '', 'none');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
