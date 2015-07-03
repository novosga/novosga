<?php

namespace modules\sga\grupos;

use Novosga\Context;
use Novosga\Model\SequencialModel;
use Novosga\Model\Grupo;
use Novosga\Controller\TreeModelController;

/**
 * GruposController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class GruposController extends TreeModelController
{
    private $adicionando = false;

    protected function createModel()
    {
        return new Grupo();
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
        // adicionando
        if ($model->getId() == 0) {
            if (!$pai) {
                throw new \Exception(_('Favor escolher o Grupo pai'));
            }
            $this->adicionando = true;
        }
    }

    protected function postSave(Context $context, SequencialModel $model)
    {
        /*
         * Se o pai tem unidades vinculadas a ele, é porque esse é o primeiro filho.
         * Então move todas as unidades para esse novo grupo
         */
        if ($this->adicionando) {
            $em = $context->database()->createEntityManager();
            $unidades = $this->countUnidades($model->getParent($em));
            if ($unidades > 0) {
                $query = $this->em()->createQuery("
                    UPDATE
                        Novosga\Model\Unidade e
                    SET
                        e.grupo = :novo
                    WHERE
                        e.grupo = :grupo
                ");
                $query->setParameter('grupo', $model->getParent($em)->getId());
                $query->setParameter('novo', $model->getId());
                $query->execute();
            }
        }
    }

    protected function search($arg)
    {
        $query = $this->em()->createQuery("
            SELECT
                e
            FROM
                Novosga\Model\Grupo e
            WHERE
                UPPER(e.nome) LIKE :arg OR UPPER(e.descricao) LIKE :arg
            ORDER BY
                e.left, e.nome
        ");
        $query->setParameter('arg', $arg);

        return $query;
    }

    private function countUnidades(Grupo $grupo)
    {
        $query = $this->em()->createQuery("
            SELECT
                COUNT(e) as total
            FROM
                Novosga\Model\Unidade e
            WHERE
                e.grupo = :grupo
        ");
        $query->setParameter('grupo', $grupo->getId());
        $rs = $query->getSingleResult();

        return $rs['total'];
    }

    /**
     * Verifica se o grupo a ser excluído possui relacionamento com alguma unidade.
     *
     * @param Novosga\Context               $context
     * @param Novosga\Model\SequencialModel $model
     */
    protected function preDelete(Context $context, SequencialModel $model)
    {
        $query = $this->em()->createQuery("
            SELECT
                COUNT(e) as total
            FROM
                Novosga\Model\Unidade e
                INNER JOIN e.grupo g
            WHERE
                g.left >= :esquerda AND
                g.right <= :direita
        ");
        $query->setParameter('esquerda', $model->getLeft());
        $query->setParameter('direita', $model->getRight());
        $rs = $query->getSingleResult();
        if ($rs['total'] > 0) {
            throw new \Exception(_('Esse grupo não pode ser excluído porque possui relacionamento com uma ou mais unidades.'));
        }
    }
}
