<?php
namespace modules\sga\grupos;

use \novosga\SGAContext;
use \novosga\util\Arrays;
use \novosga\model\SequencialModel;
use \novosga\model\Grupo;
use \novosga\controller\TreeModelController;

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
                        novosga\model\Unidade e 
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
                novosga\model\Grupo e 
            WHERE 
                UPPER(e.nome) LIKE :arg OR UPPER(e.descricao) LIKE :arg 
            ORDER BY 
                e.left, e.nome
        ");
        $query->setParameter('arg', $arg);
        return $query;
    }
    
    
    private function countUnidades(Grupo $grupo) {
        $query = $this->em()->createQuery("
            SELECT 
                COUNT(e) as total
            FROM 
                novosga\model\Unidade e 
            WHERE 
                e.grupo = :grupo
        ");
        $query->setParameter('grupo', $grupo->getId());
        $rs = $query->getSingleResult();
        return $rs['total'];
    }
    
    /**
     * Verifica se o grupo a ser excluído possui relacionamento com alguma unidade
     * @param novosga\SGAContext $context
     * @param novosga\model\SequencialModel $model
     */
    protected function preDelete(SGAContext $context, SequencialModel $model) {
        $query = $this->em()->createQuery("
            SELECT 
                COUNT(e) as total
            FROM 
                novosga\model\Unidade e
                INNER JOIN e.grupo g
            WHERE 
                g.left >= :esquerda AND
                g.right <= :direita
        ");
        $query->setParameter('esquerda', $model->getLeft());
        $query->setParameter('direita', $model->getRight());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception(_('Esse grupo não pode ser excluído porque possui relacionamento com uma ou mais unidades.'));
        }
    }
    
}
