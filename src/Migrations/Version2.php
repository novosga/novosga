<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version2 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE atendimentos ADD local_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE historico_atendimentos ADD local_id INT DEFAULT NULL');
        $this->addSql('UPDATE atendimentos SET local_id = (SELECT s.local_id FROM servicos s WHERE s.id = servico_id)');
        $this->addSql('UPDATE historico_atendimentos SET local_id = (SELECT s.local_id FROM servicos s WHERE s.id = servico_id)');
        $this->addSql('UPDATE usuarios_metadata SET name = \'atendimento.num_local\' WHERE name = \'atendimento.local\'');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE atendimentos DROP local_id');
        $this->addSql('ALTER TABLE historico_atendimentos DROP local_id');
        $this->addSql('DELETE FROM usuarios_metadata WHERE name = \'atendimento.local\'');
        $this->addSql('UPDATE usuarios_metadata SET name = \'atendimento.local\' WHERE name = \'atendimento.num_local\'');
    }
}
