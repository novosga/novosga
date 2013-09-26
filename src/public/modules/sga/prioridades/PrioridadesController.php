<?php
namespace modules\sga\prioridades;

use \novosga\SGAContext;
use \novosga\model\Prioridade;
use \novosga\model\SequencialModel;
use \novosga\controller\CrudController;

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
        return array('nome', 'descricao', 'peso', 'status');
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM novosga\model\Prioridade e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.descricao) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query;
    }
    
    public function edit(SGAContext $context, $id = 0) {
        $this->app()->view()->assign('pesos', array(
            0 => _('Normal'), 
            1 => '1', 
            2 => '2', 
            3 => '3', 
            4 => '4', 
            5 => '5'
        ));
        $this->app()->view()->assign('status', array(
            '' => _('Selecione'), 
            1 => _('Ativo'), 
            0 => _('Inativo')
        ));
        parent::edit($context, $id);
    }

    protected function preDelete(SGAContext $context, SequencialModel $model) {
        if ($model->getId() == 1) {
            throw new \Exception(_('Não pode remover essa prioridade'));
        }
    }
    
}
