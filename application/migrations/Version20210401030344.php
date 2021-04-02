<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210401030344 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds displayName column';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE user ADD display_name VARCHAR(255) DEFAULT NULL;

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

ALTER TABLE user DROP display_name;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
