<?php
namespace modules\sga\servicos;

use \core\SGAContext;
use \core\util\Arrays;
use \core\model\SequencialModel;
use \core\model\Servico;
use \core\controller\CrudController;

/**
 * ServicosController
 *
 * @author rogeriolino
 */
class ServicosController extends CrudController {
    
    protected function createModel() {
        return new Servico();
    }
    
    protected function requiredFields() {
        return array('nome', 'descricao', 'status');
    }

    protected function preSave(SGAContext $context, SequencialModel $model) {
        $id_macro = (int) Arrays::value($_POST, 'id_macro');
        $macro = $this->em()->find("\core\model\Servico", $id_macro);
        $model->setMestre($macro);
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("
            SELECT 
                e 
            FROM 
                \core\model\Servico e 
                LEFT JOIN e.mestre m
            WHERE
                UPPER(e.nome) LIKE :arg OR 
                UPPER(e.descricao) LIKE :arg
        ");
        $query->setParameter('arg', $arg);
        return $query->getResult();
    }

    public function edit(SGAContext $context) {
        parent::edit($context);
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Servico e WHERE e.mestre IS NULL AND e.id != :id ORDER BY e.nome ASC");
        $query->setParameter('id', $this->model->getId());
        $this->view()->assign('macros', $query->getResult());
    }
    
}
