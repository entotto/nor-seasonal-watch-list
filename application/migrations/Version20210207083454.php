<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210207083454 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Changes table name from "show" to "anime_show"';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(<<<EOF
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

RENAME TABLE `show` TO anime_show;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
EOF
        );
    }

    public function down(Schema $schema) : void
    {
        $this->addSql(<<<EOF
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

RENAME TABLE anime_show TO `show`;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
EOF
        );
    }
}
