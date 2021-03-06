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
final class Version20210214035256 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds slug column to score';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
ALTER TABLE score ADD slug VARCHAR(255) NULL;
# noinspection SqlWithoutWhere
UPDATE score SET slug = \'\';
ALTER TABLE score CHANGE slug slug VARCHAR(255) NOT NULL;
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE score DROP slug');
    }
}
