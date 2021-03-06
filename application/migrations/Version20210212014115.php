<?php /** @noinspection PhpUnused */
/** @noinspection UnknownInspectionInspection */
/** @noinspection SqlResolve */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210212014115 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds nickname and color_value fields to score.';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<EOF
ALTER TABLE score
    ADD nickname VARCHAR(50) NOT NULL,
    ADD color_value VARCHAR(20) NOT NULL;

# noinspection SqlWithoutWhere
UPDATE score SET nickname = name, color_value = '';

ALTER TABLE score
    CHANGE nickname nickname VARCHAR(50) NOT NULL,
    CHANGE color_value color_value VARCHAR(20) NOT NULL;
EOF
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE score DROP nickname, DROP color_value');
    }
}
