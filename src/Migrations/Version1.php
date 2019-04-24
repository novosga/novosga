<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use App\Migrations\AbstractVersion;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version1 extends AbstractVersion
{
    use ContainerAwareTrait;
    
    public function up(Schema $schema) : void
    {
        $this->updateViews();
    }

    public function down(Schema $schema) : void
    {
    }
}
