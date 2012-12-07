<?php
namespace modules\sga\prioridades;

use \core\SGAContext;
use \core\util\Arrays;
use \core\model\SequencialModel;
use \core\model\Prioridade;
use \core\controller\CrudController;

/**
 * PrioridadesController
 *
 * @author rogeriolino
 */
class PrioridadesController extends CrudController {
    
    protected function createModel() {
        return new Prioridade();
    }
    
    protected function requiredFields() {
        return array('nome', 'peso');
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Prioridade e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.descricao) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query->getResult();
    }

    public function edit(SGAContext $context) {
        parent::edit($context);
    }
    
}
