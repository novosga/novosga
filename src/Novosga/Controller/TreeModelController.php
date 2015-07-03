<?php

namespace Novosga\Controller;

use Exception;
use Novosga\Context;
use Novosga\Model\TreeModel;
use Novosga\Model\SequencialModel;

/**
 * TreeModelController
 * Classe pai para cadastros de arvore aninhada.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class TreeModelController extends CrudController
{
    public function edit(Context $context, $id = 0)
    {
        parent::edit($context, $id);
        $className = get_class($this->model);
        $query = $this->em()->createQuery("SELECT e FROM $className e WHERE e.id != :id ORDER BY e.left");
        $query->setParameter('id', $this->model->getId());
        $this->app()->view()->set('pais', $query->getResult());
        $this->app()->view()->set('modelParent', $this->model->getParent($this->em()));
    }

    /**
     * Insere ou atualiza a entidade no banco.
     *
     * @param Novosga\Model\SequencialModel $model
     */
    protected function doSave(Context $context, SequencialModel $model)
    {
        if (!($model instanceof TreeModel)) {
            throw new Exception(sprintf(_('Modelo inválido passado como parâmetro. Era esperado TreeModel e passou %s'), get_class($model)));
        }
        $this->preSave($context, $model);
        if ($model->getId() > 0) {
            // update
            $this->merge($model);
        } else {
            // insert
            $this->persist($model);
        }
        $this->em()->flush();
        $this->postSave($context, $model);
    }

    private function persist(TreeModel $model)
    {
        $className = get_class($model);
        try {
            $this->em()->beginTransaction();
            // persiste a nova entidade
            $this->em()->persist($model);
            $right = $model->getParent($this->em())->getRight() - 1;
            // desloca todos elementos da arvore, para a direita (+2), abrindo um espaço de 2 a ser usado para inserir o nó
            $query = $this->em()->createQuery("UPDATE $className e SET e.right = e.right + 2 WHERE e.right > :right");
            $query->setParameter('right', $right);
            $query->execute();
            // continuação do deslocamento acima (agora para o "esquerda")
            $query = $this->em()->createQuery("UPDATE $className e SET e.left = e.left + 2 WHERE e.left > :right");
            $query->setParameter('right', $right);
            $query->execute();
            // atualiza lados
            $model->setLeft($right + 1);
            $model->setRight($right + 2);
            $model->setLevel($model->getParent($this->em())->getLevel() + 1);
            $this->em()->commit();
        } catch (Exception $e) {
            $this->em()->rollback();
            throw new Exception(sprintf(_('Erro ao inserir o registro: %s'), $e->getMessage()));
        }
    }

    private function merge(TreeModel $model)
    {
        try {
            $className = get_class($model);
            $this->em()->beginTransaction();
            // se nao for raiz, verifica o pai
            if ($model->getLeft() > 1) {
                $query = $this->em()->createQuery("
                    SELECT pai
                    FROM $className no
                    JOIN $className pai
                    WHERE
                        no.left > pai.left AND
                        no.right < pai.right AND
                        no.id = :id
                    ORDER BY
                        pai.left DESC
                ");
                $query->setParameter('id', $model->getId());
                $query->setMaxResults(1);
                $paiAtual = $query->getSingleResult();
                $novoPai = $model->getParent($this->em());

                // se mudou o pai
                if ($paiAtual->getId() != $novoPai->getId()) {
                    $tamanho = $model->getRight() - $model->getLeft() + 1;

                    $direita = $novoPai->getRight() - 1;
                    $query = $this->em()->createQuery("UPDATE $className e SET e.right = e.right + :tamanho WHERE e.right > :direita_pai");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita_pai', $direita);
                    $query->execute();

                    $query = $this->em()->createQuery("UPDATE $className e SET e.left = e.left + :tamanho WHERE e.left > :direita_pai");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita_pai', $direita);
                    $query->execute();

                    if ($model->getLeft() > $direita) {
                        $model->setLeft($model->getLeft() + $tamanho);
                    }
                    if ($model->getRight() > $direita) {
                        $model->setRight($model->getRight() + $tamanho);
                    }

                    $deslocamento = ($novoPai->getRight() + $tamanho) - $model->getRight() - 1;

                    $query = $this->em()->createQuery("UPDATE $className e SET e.right = e.right + :deslocamento, e.left = e.left + :deslocamento WHERE e.left >= :esquerda AND e.right <= :direita");
                    $query->setParameter('deslocamento', $deslocamento);
                    $query->setParameter('esquerda', $model->getLeft());
                    $query->setParameter('direita', $model->getRight());
                    $query->execute();

                    $query = $this->em()->createQuery("UPDATE $className e SET e.right = e.right - :tamanho WHERE e.right > :direita");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita', $model->getRight());
                    $query->execute();

                    $query = $this->em()->createQuery("UPDATE $className e SET e.left = e.left - :tamanho WHERE e.left > :direita");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita', $model->getRight());
                    $query->execute();

                    $query = $this->em()->createQuery("SELECT e.left, e.right FROM $className e WHERE e.id = :id");
                    $query->setParameter('id', $model->getId());
                    $rs = $query->getSingleResult();
                    $model->setLeft($rs['left']);
                    $model->setRight($rs['right']);
                    $newLevel = $model->getParent($this->em())->getLevel() + 1;
                    $delta = $newLevel - $model->getLevel();
                    $model->setLevel($newLevel);
                    $this->updateLevels($model, $delta);
                }
            }
            $this->em()->merge($model);
            $this->em()->commit();
        } catch (Exception $e) {
            $this->em()->rollback();
            throw new Exception(sprintf(_('Erro ao atualizar o registro: %s'), $e->getMessage()));
        }
    }

    protected function doDelete(Context $context, SequencialModel $model)
    {
        if ($model->getLeft() == 1) {
            throw new Exception(_('Não pode remover a raiz'));
        }
        try {
            $this->em()->beginTransaction();
            $this->preDelete($context, $model);
            $className = get_class($model);
            // apagando os filhos
            $query = $this->em()->createQuery("DELETE FROM $className e WHERE e.left > :esquerda AND e.left < :direita");
            $query->setParameter('esquerda', $model->getLeft());
            $query->setParameter('direita', $model->getRight());
            $query->execute();

            // atualizando os tamanhos
            $tamanho = $model->getRight() - $model->getLeft() + 1;
            $query = $this->em()->createQuery("UPDATE $className e SET e.right = e.right - :tamanho WHERE e.right > :direita");
            $query->setParameter('direita', $model->getRight());
            $query->setParameter('tamanho', $tamanho);
            $query->execute();

            // atualizando os tamanhos
            $query = $this->em()->createQuery("UPDATE $className e SET e.left = e.left - :tamanho WHERE e.left > :direita");
            $query->setParameter('direita', $model->getRight());
            $query->setParameter('tamanho', $tamanho);
            $query->execute();

            $this->em()->remove($model);
            $this->em()->commit();
            $this->em()->flush();
        } catch (Exception $e) {
            $this->em()->rollback();
            throw new Exception(sprintf(_('Erro ao apagar o registro: %s'), $e->getMessage()));
        }
        $this->postDelete($context, $model);
    }

    /**
     * Atualiza os niveis dos nós filhos da arvore.
     */
    private function updateLevels(TreeModel $model, $delta)
    {
        $className = get_class($model);
        // atualizando
        $query = $this->em()->createQuery("
            UPDATE $className e
            SET e.level = e.level + :delta
            WHERE e.left > :left AND e.right < :right
        ");
        $query->setParameter('left', $model->getLeft());
        $query->setParameter('right', $model->getRight());
        $query->setParameter('delta', $delta);
        $query->execute();
    }
}
