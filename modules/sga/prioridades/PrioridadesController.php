<?php

namespace modules\sga\prioridades;

use Novosga\Context;
use Novosga\Model\Prioridade;
use Novosga\Model\SequencialModel;
use Novosga\Controller\CrudController;

/**
 * PrioridadesController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class PrioridadesController extends CrudController
{
    protected function createModel()
    {
        return new Prioridade();
    }

    protected function requiredFields()
    {
        return array('nome', 'descricao', 'peso', 'status');
    }

    protected function search($arg)
    {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Prioridade e WHERE UPPER(e.nome) LIKE :arg OR UPPER(e.descricao) LIKE :arg");
        $query->setParameter('arg', $arg);

        return $query;
    }

    public function edit(Context $context, $id = 0)
    {
        $this->app()->view()->set('pesos', array(
            0 => _('Normal'),
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
        ));
        $this->app()->view()->set('status', array(
            '' => _('Selecione'),
            1 => _('Ativo'),
            0 => _('Inativo'),
        ));
        parent::edit($context, $id);
    }

    protected function preSave(Context $context, SequencialModel $model)
    {
        // nao pode alterar o peso do registro 1 (sem prioridade)
        if ($model->getId() == 1) {
            $model->setPeso(0);
        }
    }

    protected function preDelete(Context $context, SequencialModel $model)
    {
        if ($model->getId() == 1) {
            throw new \Exception(_('Não pode remover essa prioridade'));
        }
    }
}
