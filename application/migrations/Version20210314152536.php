<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210314152536 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds activity data, updates score data';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

REPLACE INTO `activity` (`id`, `name`, `nickname`, `rank_order`, `value`, `color_value`, `icon`, `slug`) VALUES
(1, 'PTW (plan to watch)', 'PTW', 1, NULL, 'activity-ptw', '', 'ptw'),
(2, 'Watching', 'Watching', 2, NULL, 'activity-watching', '', 'watching'),
(3, 'Finished', 'Finished', 3, NULL, 'activity-finished', '', 'finished'),
(4, 'Paused', 'Paused', 4, NULL, 'activity-paused', '', 'paused'),
(5, 'Dropped', 'Dropped', 5, NULL, 'activity-dropped', '', 'dropped'),
(6, '(Not watching)', '(none)', 99, NULL, 'activity-none', '', 'none')
;

UPDATE `show_season_score`
SET `score_id` = NULL
WHERE `score_id` IN (1,5);

UPDATE `show_season_score`
SET `score_id` = 5
WHERE `score_id` = 6;

REPLACE INTO `score` (`id`, `name`, `rank_order`, `value`, `nickname`, `color_value`, `icon`, `slug`) VALUES
(1, 'Neutral', 4, '0.0', 'Neutral', 'score-neutral', '', 'neutral'),
(2, 'Favorable', 3, '1.0', 'Favorable', 'score-favorable', '', 'favorable'),
(3, 'Highly favorable', 2, '2.0', 'Highly', 'score-highly', '', 'highly-favorable'),
(4, 'Th8a should cover', 1, '3.0', 'Th8a', 'score-th8a', '', 'th8a'),
(5, 'Unfavorable', 5, '-1.0', 'Unfavorable', 'score-unfavorable', '', 'unfavorable'),
(6, '(No opinion)', 99, '0.0', '(None)', 'score-none', '', 'none')
;

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

UPDATE `show_season_score`
SET `score_id` = 6
WHERE `score_id` = 5;

REPLACE INTO `score` (`id`, `name`, `rank_order`, `value`, `nickname`, `color_value`, `icon`, `slug`) VALUES
(1, 'Plan to watch', 5, '0.1', 'PTW', 'info', '<i class="bi bi-bookmark"></i>', 'ptw'),
(2, 'Watching', 6, '1.0', 'Watching', 'primary', '<i class="bi bi-star"></i>', 'watching'),
(3, 'Suggested to all', 7, '2.0', 'Suggested', 'success', '<i class="bi bi-star"></i> <i class="bi bi-star"></i>', 'suggested'),
(4, 'Th8a should cover', 8, '3.0', 'Th8a', 'royal', '<i class="bi bi-star"></i> <i class="bi bi-star"></i> <i class="bi bi-star"></i>', 'th8a'),
(5, 'Dropped', 3, '0.0', 'Dropped', 'secondary', '<i class="bi bi-exclamation-triangle"></i>', 'dropped'),
(6, 'Disliked', 2, '-1.0', 'Disliked', 'danger', '<i class="bi bi-x-octagon"></i>', 'disliked');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
