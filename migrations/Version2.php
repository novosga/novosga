<?php declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\AbstractVersion;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version2 extends AbstractVersion
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        if (!$this->existsColumn('atendimentos', 'local_id')) {
            $this->addSql('ALTER TABLE atendimentos ADD local_id INT DEFAULT NULL');
            $this->addSql('UPDATE atendimentos SET local_id = (SELECT s.local_id FROM servicos s WHERE s.id = servico_id)');
        }

        if (!$this->existsColumn('historico_atendimentos', 'local_id')) {
            $this->addSql('ALTER TABLE historico_atendimentos ADD local_id INT DEFAULT NULL');
            $this->addSql('UPDATE historico_atendimentos SET local_id = (SELECT s.local_id FROM servicos s WHERE s.id = servico_id)');
        }

        $this->addSql('DELETE FROM usuarios_metadata WHERE name = \'atendimento.num_local\'');
        $this->addSql('UPDATE usuarios_metadata SET name = \'atendimento.num_local\' WHERE name = \'atendimento.local\'');

        $this->updateViews();
    }

    public function down(Schema $schema) : void
    {
        if ($this->existsColumn('atendimentos', 'local_id')) {
            $this->addSql('ALTER TABLE atendimentos DROP local_id');
        }

        if ($this->existsColumn('historico_atendimentos', 'local_id')) {
            $this->addSql('ALTER TABLE historico_atendimentos DROP local_id');
        }

        $this->addSql('DELETE FROM usuarios_metadata WHERE name = \'atendimento.local\'');
        $this->addSql('UPDATE usuarios_metadata SET name = \'atendimento.local\' WHERE name = \'atendimento.num_local\'');

        $this->updateViews();
    }
}
