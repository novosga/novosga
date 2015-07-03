<?php

namespace modules\sga\servicos;

use Novosga\Context;
use Novosga\Model\SequencialModel;
use Novosga\Model\Servico;
use Novosga\Controller\CrudController;

/**
 * ServicosController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ServicosController extends CrudController
{
    protected function createModel()
    {
        $servico = new Servico();
        $servico->setPeso(1);

        return $servico;
    }

    protected function requiredFields()
    {
        return array('nome', 'descricao', 'status');
    }

    protected function preSave(Context $context, SequencialModel $model)
    {
        $id_macro = (int) $context->request()->post('id_macro');
        $macro = $this->em()->find("Novosga\Model\Servico", $id_macro);
        $model->setMestre($macro);
    }

    protected function search($arg)
    {
        $query = $this->em()->createQuery("
            SELECT
                e
            FROM
                Novosga\Model\Servico e
                LEFT JOIN
                    e.mestre m
            WHERE
                (
                    UPPER(e.nome) LIKE :arg OR
                    UPPER(e.descricao) LIKE :arg
                )
            ORDER BY
                e.nome
        ");
        $query->setParameter('arg', $arg);

        return $query;
    }

    public function edit(Context $context, $id = 0)
    {
        parent::edit($context, $id);
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Servico e WHERE e.mestre IS NULL AND e.id != :id ORDER BY e.nome ASC");
        $query->setParameter('id', $this->model->getId());
        $this->app()->view()->set('macros', $query->getResult());
    }

    /**
     * Verifica se já existe unidade usando o serviço.
     *
     * @param Novosga\Model\SequencialModel $model
     */
    protected function preDelete(Context $context, SequencialModel $model)
    {
        $error = _('Já existem atendimentos para o serviço que está tentando remover');
        // quantidade de atendimentos do servico
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM Novosga\Model\Atendimento e JOIN e.servicoUnidade su WHERE su.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception($error);
        }
        // quantidade de atendimentos do servico, no historico
        $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM Novosga\Model\ViewAtendimento e WHERE e.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception($error);
        }
        // apagando vinculo com as unidades
        $this->em()->beginTransaction();
        $query = $this->em()->createQuery("DELETE FROM Novosga\Model\ServicoUnidade e WHERE e.servico = :servico");
        $query->setParameter('servico', $model->getId());
        $query->execute();
    }

    protected function postDelete(Context $context, SequencialModel $model)
    {
        $this->em()->commit();
    }

    public function subservicos(Context $context)
    {
        $response = new \Novosga\Http\JsonResponse();
        $id = $context->request()->get('id');
        $servico = $this->findById($id);
        if ($servico) {
            foreach ($servico->getSubServicos() as $sub) {
                $response->data[] = array(
                    'id' => $sub->getId(),
                    'nome' => $sub->getNome(),
                );
            }
            $response->success = true;
        }
        echo $response->toJson();
        exit();
    }
}
