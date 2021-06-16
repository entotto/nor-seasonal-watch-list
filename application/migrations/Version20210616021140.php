<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210616021140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Changes to make Seasons more general Groups';
    }

    public function up(Schema $schema): void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE season
    ADD hidden_from_seasons_list TINYINT(1) NULL,
    CHANGE year year INT DEFAULT NULL,
    CHANGE year_part year_part VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`;

UPDATE season SET hidden_from_seasons_list = 0 WHERE hidden_from_seasons_list IS NULL;

ALTER TABLE season
    CHANGE hidden_from_seasons_list hidden_from_seasons_list TINYINT(1) NOT NULL;

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

DELETE FROM season WHERE hidden_from_seasons_list = 1;
DELETE FROM season WHERE year IS NULL;
DELETE FROM season WHERE year_part IS NULL;

ALTER TABLE season
    DROP hidden_from_seasons_list,
    CHANGE year year INT NOT NULL,
    CHANGE year_part year_part VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
