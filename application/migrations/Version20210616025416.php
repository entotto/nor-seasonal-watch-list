<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210616025416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds title and description and maxVotes fields to Election';
    }

    public function up(Schema $schema): void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE election
    ADD title VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
    ADD description LONGTEXT DEFAULT NULL,
    ADD max_votes INTEGER DEFAULT NULL;

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

ALTER TABLE election
    DROP title,
    DROP description,
    DROP max_votes;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
