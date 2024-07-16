<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version1 extends AbstractMigration
{   
    public function up(Schema $schema) : void
    {
        $this->updateViews();
    }

    public function down(Schema $schema) : void
    {
        // do nothing
    }
}
