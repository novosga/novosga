<?php
namespace modules\sga\cargos;

use \Novosga\SGAContext;
use \Novosga\Util\Arrays;
use \Novosga\Model\SequencialModel;
use \Novosga\Model\Cargo;
use \Novosga\Controller\TreeModelController;

/**
 * CargosController
 *
 * @author rogeriolino
 */
class CargosController extends TreeModelController {

    protected function createModel() {
        return new Cargo();
    }
    
    protected function requiredFields() {
        return array('nome', 'descricao');
    }

    protected function preSave(SGAContext $context, SequencialModel $model) {
        $id_pai = (int) Arrays::value($_POST, 'id_pai', 0);
        $pai = $this->em()->find(get_class($model), $id_pai);
        if ($pai) {
            $model->setParent($pai);
        }
        if ($model->getId() == 0 && !$pai) {
            throw new \Exception(_('Favor escolher o Cargo pai'));
        }
    }

    protected function postSave(SGAContext $context, SequencialModel $model) {
        // atualizando permissoes do cargo
        $query = $this->em()->createQuery("DELETE FROM Novosga\Model\Permissao e WHERE e.cargo = :cargo");
        $query->setParameter('cargo', $model->getId());
        $query->execute();
        $permissoes = Arrays::value($_POST, 'permissoes');
        $conn = $this->em()->getConnection();
        $stmt = $conn->prepare("INSERT INTO cargos_mod_perm (modulo_id, cargo_id, permissao) VALUES (:modulo, :cargo, :permissao)");
        if (!empty($permissoes)) {
            foreach ($permissoes as $modulo) {
                $stmt->bindValue('modulo', $modulo, \PDO::PARAM_INT);
                $stmt->bindValue('cargo', $model->getId(), \PDO::PARAM_INT);
                $stmt->bindValue('permissao', 3, \PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    }

    /**
     * Deletando vinculos (permissoes e lotacoes)
     * @param Novosga\SGAContext $context
     * @param Novosga\Model\SequencialModel $model
     */
    protected function preDelete(SGAContext $context, SequencialModel $model) {
        $query = $this->em()->createQuery("DELETE FROM Novosga\Model\Permissao p WHERE p.cargo = :cargo");
        $query->setParameter('cargo', $model->getId());
        $query->execute();
        $query = $this->em()->createQuery("DELETE FROM Novosga\Model\Lotacao l WHERE l.cargo = :cargo");
        $query->setParameter('cargo', $model->getId());
        $query->execute();
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("
            SELECT 
                e 
            FROM 
                Novosga\Model\Cargo e 
            WHERE 
                UPPER(e.nome) LIKE :arg OR UPPER(e.descricao) LIKE :arg  
            ORDER BY 
                e.left, e.nome
        ");
        $query->setParameter('arg', $arg);
        return $query;
    }
    
    public function edit(SGAContext $context, $id = 0) {
        parent::edit($context, $id);
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Modulo e WHERE e.status = 1 AND e.tipo = :tipo ORDER BY e.nome");
        $query->setParameter('tipo', \Novosga\Model\Modulo::MODULO_UNIDADE);
        $modulosUnidade = $query->getResult();
        $query->setParameter('tipo', \Novosga\Model\Modulo::MODULO_GLOBAL);
        $modulosGlobal = $query->getResult();
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Permissao e WHERE e.cargo = :cargo");
        $query->setParameter('cargo', $this->model->getId());
        $permissoes = $query->getResult();
        $this->app()->view()->assign('tipos', array(_('Unidade'), _('Global')));
        $this->app()->view()->assign('modulos', array($modulosUnidade, $modulosGlobal));
        $this->app()->view()->assign('permissoes', $permissoes);
    }

}
