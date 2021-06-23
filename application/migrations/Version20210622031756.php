<?php /** @noinspection UnknownInspectionInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210622031756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds related show fields.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE anime_show
    ADD first_show_id INT DEFAULT NULL;
    
ALTER TABLE anime_show
    ADD CONSTRAINT FK_9DFB0B55C34D9098 FOREIGN KEY (first_show_id) REFERENCES anime_show (id);
    
CREATE INDEX IDX_9DFB0B55C34D9098 ON anime_show (first_show_id);

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

ALTER TABLE anime_show
    DROP FOREIGN KEY FK_9DFB0B55C34D9098;
    
DROP INDEX IDX_9DFB0B55C34D9098 ON anime_show;

ALTER TABLE anime_show
    DROP first_show_id;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }
}
