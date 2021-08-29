<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210829012221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds "stopped watching" activity option';
    }

    public function up(Schema $schema): void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

REPLACE INTO `activity` (`id`, `name`, `nickname`, `rank_order`, `value`, `color_value`, `icon`, `slug`) VALUES
(1, 'PTW (Plan to watch)', 'PTW', 1, '1.000', 'activity-ptw', '', 'ptw'),
(2, 'Watching/Finished', 'Watching', 2, '1.000', 'activity-watching', '', 'watching'),
(3, '(No opinion)', '(none)', 99, NULL, 'activity-none', '', 'none'),
(4, 'Stopped watching', 'Stopped', 3, NULL, 'activity-stopped', '', 'stopped');
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

REPLACE INTO `activity` (`id`, `name`, `nickname`, `rank_order`, `value`, `color_value`, `icon`, `slug`) VALUES
(1, 'PTW (Plan to watch)', 'PTW', 1, '1.000', 'activity-ptw', '', 'ptw'),
(2, 'Watching/Finished', 'Watching', 2, '1.000', 'activity-watching', '', 'watching'),
(3, '(Not watching)', '(none)', 99, NULL, 'activity-none', '', 'none');
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
