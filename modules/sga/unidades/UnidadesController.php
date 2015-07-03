<?php

namespace modules\sga\unidades;

use Novosga\Context;
use Novosga\Model\SequencialModel;
use Novosga\Model\Unidade;
use Novosga\Model\Contador;
use Novosga\Controller\CrudController;

/**
 * UnidadesController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UnidadesController extends CrudController
{
    private $isNew = false;

    protected function createModel()
    {
        return new Unidade();
    }

    protected function requiredFields()
    {
        return array('codigo', 'nome', 'status');
    }

    protected function preSave(Context $context, SequencialModel $model)
    {
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM Novosga\Model\Unidade e WHERE e.codigo = :codigo AND e.id != :id");
        $query->setParameter('codigo', $model->getCodigo());
        $query->setParameter('id', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total']) {
            throw new \Exception(_('Código de Unidade já existe'));
        }
        $grupo_id = (int) $context->request()->post('grupo_id');
        $grupo = $this->em()->find('Novosga\Model\Grupo', $grupo_id);
        if (!$grupo || !$grupo->isLeaf()) {
            throw new \Exception(_('Grupo inválido'));
        }

        $this->isNew = !$model->getId();

        if ($this->isNew) {
            $model->setStatusImpressao(1);
            $model->setMensagemImpressao('Novo SGA');
        }
        $model->setGrupo($grupo);
    }

    protected function postSave(Context $context, SequencialModel $model)
    {
        if ($this->isNew) {
            $contador = new Contador();
            $contador->setUnidade($model);
            $contador->setTotal(0);
            $this->em()->persist($contador);
            $this->em()->flush();
        }
        $this->isNew = false;
    }

    protected function search($arg)
    {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Unidade e JOIN e.grupo g WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.codigo) LIKE :arg");
        $query->setParameter('arg', $arg);

        return $query;
    }

    public function edit(Context $context, $id = 0)
    {
        parent::edit($context, $id);
        $this->app()->view()->set('grupos', $this->getGruposFolhasDisponiveis($this->model));
    }

    /**
     * Retorna os grupos folhas que ainda não foram relacionados àlguma unidade.
     *
     * @param Novosga\Model\Unidade $atual
     */
    private function getGruposFolhasDisponiveis(Unidade $atual = null)
    {
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

    /**
     * Remove a unidade caso a mesma não possua atendimento. Se possuir uma
     * exceção será lançada.
     *
     * @param Novosga\Context               $context
     * @param Novosga\Model\SequencialModel $model
     *
     * @throws \Exception
     * @throws \modules\sga\unidades\Exception
     */
    protected function doDelete(Context $context, SequencialModel $model)
    {
        // verificando se ja tem atendimentos
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM Novosga\Model\ViewAtendimento e WHERE e.unidade = :unidade");
        $query->setParameter('unidade', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception(_('Não pode excluir essa unidade porque a mesma já possui atendimentos.'));
        }
        $this->em()->beginTransaction();
        try {
            // removendo servicos
            $query = $this->em()->createQuery("DELETE FROM Novosga\Model\ServicoUnidade e WHERE e.unidade = :unidade");
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
