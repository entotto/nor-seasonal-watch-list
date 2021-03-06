<?php
/** @noinspection UnknownInspectionInspection */
/** @noinspection SqlResolve */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210212145915 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds icon field to the score table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
ALTER TABLE score
    ADD icon VARCHAR(50) NOT NULL,
    CHANGE color_value color_value VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`;

# noinspection SqlWithoutWhere
UPDATE score SET icon = "";

ALTER TABLE score
    CHANGE icon icon VARCHAR(50) NOT NULL;
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('
ALTER TABLE score
    DROP icon,
    CHANGE color_value color_value VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`
');
    }
}
