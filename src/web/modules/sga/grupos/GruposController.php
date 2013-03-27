<?php
namespace modules\sga\grupos;

use \core\SGAContext;
use \core\util\Arrays;
use \core\model\SequencialModel;
use \core\model\Grupo;
use \core\controller\TreeModelController;

/**
 * GruposController
 *
 * @author rogeriolino
 */
class GruposController extends TreeModelController {
    
    private $adicionando = false;

    protected function createModel() {
        return new Grupo();
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
        // adicionando
        if ($model->getId() == 0) {
            if (!$pai) {
                throw new \Exception(_('Favor escolher o Grupo pai'));
            }
            $this->adicionando = true;
        }
    }
    
    protected function postSave(SGAContext $context, SequencialModel $model) {
        /* 
         * Se o pai tem unidades vinculadas a ele, é porque esse é o primeiro filho.
         * Então move todas as unidades para esse novo grupo
         */
        if ($this->adicionando) {
            $unidades = $this->countUnidades($model->getParent());
            if ($unidades > 0) {
                $query = $this->em()->createQuery("
                    UPDATE 
                        \core\model\Unidade e 
                    SET
                        e.grupo = :novo
                    WHERE 
                        e.grupo = :grupo
                ");
                $query->setParameter('grupo', $model->getParent()->getId());
                $query->setParameter('novo', $model->getId());
                $query->execute();
            }
        }
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("
            SELECT 
                e 
            FROM 
                \core\model\Grupo e 
            WHERE 
                UPPER(e.nome) LIKE :arg OR UPPER(e.descricao) LIKE :arg 
            ORDER BY 
                e.left, e.nome
        ");
        $query->setParameter('arg', $arg);
        return $query->getResult();
    }
    
    
    private function countUnidades(Grupo $grupo) {
        $query = $this->em()->createQuery("
            SELECT 
                COUNT(e) as total
            FROM 
                \core\model\Unidade e 
            WHERE 
                e.grupo = :grupo
        ");
        $query->setParameter('grupo', $grupo->getId());
        $rs = $query->getSingleResult();
        return $rs['total'];
    }
    
}
