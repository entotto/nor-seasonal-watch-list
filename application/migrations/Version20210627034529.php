<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210627034529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the election_show_buff table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE election_show_buff (
    id INT AUTO_INCREMENT NOT NULL,
    election_id INT DEFAULT NULL,
    anime_show_id INT DEFAULT NULL,
    buff_rule VARCHAR(255) DEFAULT NULL,
    INDEX IDX_B78BD9CAA708DAFF (election_id),
    INDEX IDX_B78BD9CAC5ADDBA9 (anime_show_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

ALTER TABLE election_show_buff
    ADD CONSTRAINT FK_B78BD9CAA708DAFF FOREIGN KEY (election_id) REFERENCES election (id);

ALTER TABLE election_show_buff
    ADD CONSTRAINT FK_B78BD9CAC5ADDBA9 FOREIGN KEY (anime_show_id) REFERENCES anime_show (id);

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

DROP TABLE election_show_buff;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
