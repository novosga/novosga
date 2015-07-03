<?php

namespace modules\sga\cargos;

use Novosga\Context;
use Novosga\Model\SequencialModel;
use Novosga\Model\Cargo;
use Novosga\Controller\TreeModelController;

/**
 * CargosController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class CargosController extends TreeModelController
{
    protected function createModel()
    {
        return new Cargo();
    }

    protected function requiredFields()
    {
        return array('nome', 'descricao');
    }

    protected function preSave(Context $context, SequencialModel $model)
    {
        $id_pai = (int) $context->request()->post('id_pai', 0);
        $pai = $this->em()->find(get_class($model), $id_pai);
        if ($pai) {
            $model->setParent($pai);
        }
        if ($model->getId() == 0 && !$pai) {
            throw new \Exception(_('Favor escolher o Cargo pai'));
        }
    }

    protected function postSave(Context $context, SequencialModel $model)
    {
        // atualizando permissoes do cargo
        $query = $this->em()->createQuery("DELETE FROM Novosga\Model\Permissao e WHERE e.cargo = :cargo");
        $query->setParameter('cargo', $model->getId());
        $query->execute();
        $permissoes = $context->request()->post('permissoes');
        if (!empty($permissoes)) {
            foreach ($permissoes as $modulo) {
                $permissao = new \Novosga\Model\Permissao();
                $permissao->setModulo($this->em()->find('Novosga\Model\Modulo', $modulo));
                $permissao->setCargo($model);
                $permissao->setPermissao(3);
                $this->em()->persist($permissao);
            }
            $this->em()->flush();
        }
    }

    /**
     * Deletando vinculos (permissoes e lotacoes).
     *
     * @param Novosga\Context               $context
     * @param Novosga\Model\SequencialModel $model
     */
    protected function preDelete(Context $context, SequencialModel $model)
    {
        $query = $this->em()->createQuery("DELETE FROM Novosga\Model\Permissao p WHERE p.cargo = :cargo");
        $query->setParameter('cargo', $model->getId());
        $query->execute();
        $query = $this->em()->createQuery("DELETE FROM Novosga\Model\Lotacao l WHERE l.cargo = :cargo");
        $query->setParameter('cargo', $model->getId());
        $query->execute();
    }

    protected function search($arg)
    {
        $query = $this->em()->createQuery("
            SELECT
                e
            FROM
                Novosga\Model\Cargo e
            WHERE
                UPPER(e.nome) LIKE :arg OR UPPER(e.descricao) LIKE :arg
            ORDER BY
                e.left, e.nome
        ");
        $query->setParameter('arg', $arg);

        return $query;
    }

    public function edit(Context $context, $id = 0)
    {
        parent::edit($context, $id);
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Modulo e WHERE e.status = 1 AND e.tipo = :tipo ORDER BY e.nome");
        $query->setParameter('tipo', \Novosga\Model\Modulo::MODULO_UNIDADE);
        $modulosUnidade = $query->getResult();
        $query->setParameter('tipo', \Novosga\Model\Modulo::MODULO_GLOBAL);
        $modulosGlobal = $query->getResult();
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Permissao e WHERE e.cargo = :cargo");
        $query->setParameter('cargo', $this->model->getId());
        $permissoes = $query->getResult();
        $this->app()->view()->set('tipos', array(_('Unidade'), _('Global')));
        $this->app()->view()->set('modulos', array($modulosUnidade, $modulosGlobal));
        $this->app()->view()->set('permissoes', $permissoes);
    }
}
