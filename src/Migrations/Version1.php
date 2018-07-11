<?php declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\AbstractVersion;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version1 extends AbstractVersion
{
    use ContainerAwareTrait;
    
    public function up(Schema $schema) : void
    {
        if ($this->existsViewAtendimento()) {
            $this->dropViewAtendimento();
        }
        $this->createViewAtendimento();
    }

    public function down(Schema $schema) : void
    {
    }
}
