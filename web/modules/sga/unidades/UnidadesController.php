<?php
namespace modules\sga\unidades;

use \core\SGAContext;
use \core\model\SequencialModel;
use \core\model\Unidade;
use \core\controller\CrudController;

/**
 * UnidadesController
 *
 * @author rogeriolino
 */
class UnidadesController extends CrudController {
    
    public function __construct() {
        $this->title = _('Unidades');
        $this->subtitle = _('Gerencie as unidades do sistema');
    }

    protected function createModel() {
        return new Unidade();
    }

    protected function requiredFields() {
        return array('codigo', 'nome', 'status');
    }

    protected function preSave(SequencialModel $model) {
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM \core\model\Unidade e WHERE e.codigo = :codigo AND e.id != :id");
        $query->setParameter('codigo', $model->getCodigo());
        $query->setParameter('id', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total']) {
            throw new \Exception(_('Código de Unidade já existe'));
        }
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Unidade e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.codigo) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query->getResult();
    }
    
    public function edit(SGAContext $context) {
        parent::edit($context);
        // grupos disponíveis
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Grupo e WHERE e NOT IN (SELECT g FROM \core\model\Unidade u JOIN u.grupo g WHERE u.id != :id)");
        $query->setParameter('id', $this->model->getId());
        $this->view()->assign('grupos', $query->getResult());
    }
    
}
