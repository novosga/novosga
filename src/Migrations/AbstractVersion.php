<?php

namespace App\Migrations;

use Doctrine\DBAL\Schema\View;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\AbstractAtendimento;
use Novosga\Entity\Atendimento;
use Novosga\Entity\AtendimentoHistorico;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractVersion extends AbstractMigration implements ContainerAwareInterface
{
    const VIEW_ATENDIMENTOS = "view_atendimentos";
    
    use ContainerAwareTrait;
    
    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        
        return $em;
    }
    
    /**
     * Returns all table columns
     * @param string $entity
     * @return array
     */
    protected function getColumns(string $entity): array
    {
        $em            = $this->getEntityManager();
        $classMetadata = $em->getClassMetadata($entity);
        $columns       = $classMetadata->getColumnNames();
        $assocs        = $classMetadata->getAssociationNames();
        
        foreach ($assocs as $assoc) {
            $mapping = $classMetadata->getAssociationMapping($assoc);
            if (isset($mapping['joinColumns'])) {
                foreach ($mapping['joinColumns'] as $join) {
                    $columns[] = $join['name'];
                }
            }
        }
        
        return $columns;
    }
    
    /**
     * Returns the entity table name
     * @param string $entity
     * @return string
     */
    protected function getTableName(string $entity): string
    {
        $em            = $this->getEntityManager();
        $classMetadata = $em->getClassMetadata($entity);
        $tableName     = $classMetadata->getTableName();
        
        return $tableName;
    }
    
    protected function existsViewAtendimento(): bool
    {
        $list   = $this->sm->listViews();
        $exists = !!($list[self::VIEW_ATENDIMENTOS] ?? false);
        
        return $exists;
    }
    
    protected function createViewAtendimento(): void
    {
        $name        = self::VIEW_ATENDIMENTOS;
        $columns     = $this->getColumns(AbstractAtendimento::class);
        $table1      = $this->getTableName(Atendimento::class);
        $table2      = $this->getTableName(AtendimentoHistorico::class);
        $columnsList = implode(',', $columns);
        $viewSql     = "SELECT {$columnsList} FROM {$table1} UNION ALL SELECT {$columnsList} FROM {$table2}";
        
        $view = new View($name, $viewSql);
        $sql  = $this->platform->getCreateViewSQL($view->getQuotedName($this->platform), $view->getSql());
        
        $this->addSql($sql);
    }
    
    protected function dropViewAtendimento(): void
    {
        $sql = $this->platform->getDropViewSQL(self::VIEW_ATENDIMENTOS);
        
        $this->addSql($sql);
    }
}
