<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210214061554 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql( <<<EOF

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

REPLACE INTO `score` (`id`, `name`, `rank_order`, `value`, `nickname`, `color_value`, `icon`, `slug`) VALUES
(1, 'Plan to watch', 5, '0.1', 'PTW', 'info', '<i class="bi bi-bookmark"></i>', 'ptw'),
(2, 'Watching', 6, '1.0', 'Watching', 'primary', '<i class="bi bi-star"></i>', 'watching'),
(3, 'Suggested to all', 7, '2.0', 'Suggested', 'primary', '<i class="bi bi-star"></i> <i class="bi bi-star"></i>', 'suggested'),
(4, 'Th8a should cover', 8, '3.0', 'Th8a', 'success', '<i class="bi bi-star"></i> <i class="bi bi-star"></i> <i class="bi bi-star"></i>', 'th8a'),
(5, 'Dropped', 3, '0.0', 'Dropped', 'secondary', '<i class="bi bi-exclamation-triangle"></i>', 'dropped'),
(6, 'Disliked', 2, '-1.0', 'Disliked', 'danger', '<i class="bi bi-x-octagon"></i>', 'disliked');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

EOF
        );
    }

    public function down(Schema $schema) : void
    {
    }
}
