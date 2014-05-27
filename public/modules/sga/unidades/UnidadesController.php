<?php
namespace modules\sga\unidades;

use \Novosga\SGAContext;
use \Novosga\Model\SequencialModel;
use \Novosga\Model\Unidade;
use \Novosga\Controller\CrudController;

/**
 * UnidadesController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UnidadesController extends CrudController {

    protected function createModel() {
        return new Unidade();
    }

    protected function requiredFields() {
        return array('codigo', 'nome', 'status');
    }

    protected function preSave(SGAContext $context, SequencialModel $model) {
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM Novosga\Model\Unidade e WHERE e.codigo = :codigo AND e.id != :id");
        $query->setParameter('codigo', $model->getCodigo());
        $query->setParameter('id', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total']) {
            throw new \Exception(_('Código de Unidade já existe'));
        }
        $grupo_id = (int) $context->request()->getParameter('grupo_id');
        $grupo = $this->em()->find('Novosga\Model\Grupo', $grupo_id);
        if (!$grupo || !$grupo->isLeaf()) {
            throw new \Exception(_('Grupo inválido'));
        }
        if (!$model->getId()) {
            $model->setStatusImpressao(1);
            $model->setMensagemImpressao('Novo SGA');
        }
        $model->setGrupo($grupo);
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Unidade e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.codigo) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query;
    }
    
    public function edit(SGAContext $context, $id = 0) {
        parent::edit($context, $id);
        $this->app()->view()->set('grupos', $this->getGruposFolhasDisponiveis($this->model));
    }
    
    /**
     * Retorna os grupos folhas que ainda não foram relacionados àlguma unidade
     * @param Novosga\Model\Unidade $atual
     */
    private function getGruposFolhasDisponiveis(Unidade $atual = null) {
        // grupos disponíveis
        $query = $this->em()->createQuery("
            SELECT 
                e 
            FROM 
                Novosga\Model\Grupo e 
            WHERE 
                e.right = e.left + 1 AND
                e NOT IN (
                    SELECT g FROM Novosga\Model\Unidade u JOIN u.grupo g WHERE u.id != :id
                )
        ");
        // se estiver editando, deve trazer o grupo da unidade atual tambem
        $id = ($atual ? $atual->getId() : 0);
        $query->setParameter('id', $id);
        return $query->getResult();
    }
    
    protected function preDelete(SGAContext $context, SequencialModel $model) {
        throw new \Exception(\Novosga\SGA::DEMO_ALERT);
    }
    
}
