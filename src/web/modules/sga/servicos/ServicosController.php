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
            ORDER BY
                e.nome
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
    
    /**
     * Verifica se já existe unidade usando o serviço.
     * @param \core\model\SequencialModel $model
     */
    protected function preDelete(SGAContext $context, SequencialModel $model) {
        $error = _('Já existem atendimentos para o serviço que está tentando remover');
        // quantidade de atendimentos do servico
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM \core\model\Atendimento e JOIN e.servicoUnidade su WHERE su.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception($error);
        }
        // quantidade de atendimentos do servico, no historico
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM \core\model\ViewAtendimento e WHERE e.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception($error);
        }
        // apagando vinculo com as unidades
        $this->em()->beginTransaction();
        $query = $this->em()->createQuery("DELETE FROM \core\model\ServicoUnidade e WHERE e.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $query->execute();
    }
    
    protected function postDelete(SGAContext $context, SequencialModel $model) {
        $this->em()->commit();
    }
    
}
