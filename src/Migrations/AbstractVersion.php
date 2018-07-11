<?php

namespace App\Migrations;

use Doctrine\DBAL\Schema\View;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\AbstractAtendimento;
use Novosga\Entity\AbstractAtendimentoCodificado;
use Novosga\Entity\Atendimento;
use Novosga\Entity\AtendimentoCodificado;
use Novosga\Entity\AtendimentoHistorico;
use Novosga\Entity\AtendimentoCodificadoHistorico;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractVersion extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    
    const VIEW_ATENDIMENTOS             = "view_atendimentos";
    const VIEW_ATENDIMENTOS_CODIFICADOS = "view_atendimentos_codificados";
    
    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        
        return $em;
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
        return $this->existsView(self::VIEW_ATENDIMENTOS);
    }
    
    protected function existsViewAtendimentoCodificado(): bool
    {
        return $this->existsView(self::VIEW_ATENDIMENTOS_CODIFICADOS);
    }
    
    protected function createViewAtendimento(): void
    {
        $entity1     = Atendimento::class;
        $entity2     = AtendimentoHistorico::class;
        $name        = self::VIEW_ATENDIMENTOS;
        
        $this->createViewWithUnion($name, $entity1, $entity2);
    }
    
    protected function createViewAtendimentoCodificado(): void
    {
        $entity1     = AtendimentoCodificado::class;
        $entity2     = AtendimentoCodificadoHistorico::class;
        $name        = self::VIEW_ATENDIMENTOS_CODIFICADOS;
        
        $this->createViewWithUnion($name, $entity1, $entity2);
    }
    
    protected function dropViewAtendimento(): void
    {
        $sql = $this->platform->getDropViewSQL(self::VIEW_ATENDIMENTOS);
        
        $this->addSql($sql);
    }
    
    protected function dropViewAtendimentoCodificado(): void
    {
        $sql = $this->platform->getDropViewSQL(self::VIEW_ATENDIMENTOS_CODIFICADOS);
        
        $this->addSql($sql);
    }
    
    private function existsView($viewName): bool
    {
        $list   = $this->sm->listViews();
        $exists = !!($list[$viewName] ?? false);
        
        return $exists;
    }
    
    private function createViewWithUnion(string $viewName, string $entity1, string $entity2): void
    {
        $em          = $this->getEntityManager();
        $helper      = new \App\Helper\DoctrineHelper($em);
        $columns     = $helper->getEntityColumns($entity1);
        $table1      = $this->getTableName($entity1);
        $table2      = $this->getTableName($entity2);
        $columnsList = implode(',', $columns);
        $viewSql     = "SELECT {$columnsList} FROM {$table1} UNION ALL SELECT {$columnsList} FROM {$table2}";
        
        $view = new View($viewName, $viewSql);
        $sql  = $this->platform->getCreateViewSQL($view->getQuotedName($this->platform), $view->getSql());
        
        $this->addSql($sql);
    }
}
