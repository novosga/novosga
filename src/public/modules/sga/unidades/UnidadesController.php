<?php
namespace modules\sga\unidades;

use \novosga\SGAContext;
use \novosga\model\SequencialModel;
use \novosga\model\Unidade;
use \novosga\controller\CrudController;

/**
 * UnidadesController
 *
 * @author rogeriolino
 */
class UnidadesController extends CrudController {

    protected function createModel() {
        return new Unidade();
    }

    protected function requiredFields() {
        return array('codigo', 'nome', 'status');
    }

    protected function preSave(SGAContext $context, SequencialModel $model) {
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM novosga\model\Unidade e WHERE e.codigo = :codigo AND e.id != :id");
        $query->setParameter('codigo', $model->getCodigo());
        $query->setParameter('id', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total']) {
            throw new \Exception(_('Código de Unidade já existe'));
        }
        $id_grupo = (int) $context->request()->getParameter('id_grupo');
        $grupo = $this->em()->find('novosga\model\Grupo', $id_grupo);
        if (!$grupo || !$grupo->isLeaf()) {
            throw new \Exception(_('Grupo inválido'));
        }
        $model->setGrupo($grupo);
    }

    protected function search($arg) {
        $query = $this->em()->createQuery("SELECT e FROM novosga\model\Unidade e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.codigo) LIKE :arg");
        $query->setParameter('arg', $arg);
        return $query->getResult();
    }
    
    public function edit(SGAContext $context, $id = 0) {
        parent::edit($context, $id);
        $this->app()->view()->assign('grupos', $this->getGruposFolhasDisponiveis($this->model));
    }
    
    /**
     * Retorna os grupos folhas que ainda não foram relacionados àlguma unidade
     * @param novosga\model\Unidade $atual
     */
    private function getGruposFolhasDisponiveis(Unidade $atual = null) {
        // grupos disponíveis
        $query = $this->em()->createQuery("
            SELECT 
                e 
            FROM 
                novosga\model\Grupo e 
            WHERE 
                e.right = e.left + 1 AND
                e NOT IN (
                    SELECT g FROM novosga\model\Unidade u JOIN u.grupo g WHERE u.id != :id
                )
        ");
        // se estiver editando, deve trazer o grupo da unidade atual tambem
        $id = ($atual ? $atual->getId() : 0);
        $query->setParameter('id', $id);
        return $query->getResult();
    }
    
    /**
     * Remove a unidade caso a mesma não possua atendimento. Se possuir uma 
     * exceção será lançada.
     * @param novosga\SGAContext $context
     * @param novosga\model\SequencialModel $model
     * @throws \Exception
     * @throws \modules\sga\unidades\Exception
     */
    protected function doDelete(SGAContext $context, SequencialModel $model) {
        // verificando se ja tem atendimentos
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM novosga\model\ViewAtendimento e WHERE e.unidade = :unidade");
        $query->setParameter('unidade', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception(_('Não pode excluir essa unidade porque a mesma já possui atendimentos.'));
        }
        $this->em()->beginTransaction();
        try {
            // removendo servicos
            $query = $this->em()->createQuery("DELETE FROM novosga\model\ServicoUnidade e WHERE e.unidade = :unidade");
            $query->setParameter('unidade', $model->getId());
            $query->execute();
            // removendo a unidade
            $this->em()->remove($model);
            $this->em()->commit();
            $this->em()->flush();
        } catch (\Exception $e) {
            $this->em()->rollback();
            throw $e;
        }
    }
    
}
