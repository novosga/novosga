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
        if ($model->getId() == 0 && !$pai) {
            throw new \Exception(_('Favor escolher o Grupo pai'));
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
    
}
