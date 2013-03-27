<?php
namespace modules\sga\cargos;

use \core\SGAContext;
use \core\util\Arrays;
use \core\model\SequencialModel;
use \core\model\Cargo;
use \core\controller\TreeModelController;

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
        $query = $this->em()->createQuery("DELETE FROM \core\model\Permissao e WHERE e.cargo = :cargo");
        $query->setParameter('cargo', $model->getId());
        $query->execute();
        $permissoes = Arrays::value($_POST, 'permissoes');
        $conn = $this->em()->getConnection();
        $stmt = $conn->prepare("INSERT INTO cargos_mod_perm (id_mod, id_cargo, permissao) VALUES (:modulo, :cargo, :permissao)");
        foreach ($permissoes as $modulo) {
            $stmt->bindValue('modulo', $modulo, \PDO::PARAM_INT);
            $stmt->bindValue('cargo', $model->getId(), \PDO::PARAM_INT);
            $stmt->bindValue('permissao', 3, \PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("
            SELECT 
                e 
            FROM 
                \core\model\Cargo e 
            WHERE 
                UPPER(e.nome) LIKE :arg OR UPPER(e.descricao) LIKE :arg  
            ORDER BY 
                e.left, e.nome
        ");
        $query->setParameter('arg', $arg);
        return $query->getResult();
    }
    
    public function edit(SGAContext $context) {
        parent::edit($context);
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Modulo e WHERE e.status = 1 ORDER BY e.tipo, e.nome");
        $modulos = $query->getResult();
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Permissao e WHERE e.cargo = :cargo");
        $query->setParameter('cargo', $this->model->getId());
        $permissoes = $query->getResult();
        $this->view()->assign('modulos', $modulos);
        $this->view()->assign('permissoes', $permissoes);
    }

}
