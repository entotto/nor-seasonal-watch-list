<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210207080138 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds Anilist-related fields to Show';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(<<<EOF
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

ALTER TABLE `show`
ADD description LONGTEXT DEFAULT NULL,
ADD hashtag VARCHAR(255) DEFAULT NULL,
ADD cover_image_medium LONGTEXT DEFAULT NULL,
ADD cover_image_large LONGTEXT DEFAULT NULL,
ADD site_url VARCHAR(255) DEFAULT NULL,
ADD synonyms LONGTEXT DEFAULT NULL;

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

ALTER TABLE `show`
DROP description,
DROP hashtag,
DROP cover_image_medium,
DROP cover_image_large,
DROP site_url,
DROP synonyms;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
EOF
        );
    }
}
