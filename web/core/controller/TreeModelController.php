<?php
namespace core\controller;

use \Exception;
use \core\SGAContext;
use \core\model\TreeModel;
use core\model\SequencialModel;

/**
 * TreeModelController
 * Classe pai para cadastros de arvore aninhada
 *
 * @author rogeriolino
 */
abstract class TreeModelController extends CrudController {
    
    public function edit(SGAContext $context) {
        parent::edit($context);
        $className = get_class($this->model);
        $query = $this->em()->createQuery("SELECT e FROM $className e WHERE e.id != :id ORDER BY e.left");
        $query->setParameter('id', $this->model->getId());
        $this->view()->assign('pais', $query->getResult());
    }
    
    /**
     * Insere ou atualiza a entidade no banco
     * @param \core\model\SequencialModel $model
     */
    protected function save(SequencialModel $model) {
        if (!($model instanceof TreeModel)) {
            throw new Exception(sprintf(_('Modelo inválido passado como parâmetro. Era esperado TreeModel e passou %s'), get_class($model)));
        }
        $this->preSave($model);
        if ($model->getId() > 0) {
            // update
            $this->merge($model);
        } else {
            // insert
            $this->persist($model);
        }
        $this->postSave($model);
        $this->em()->flush();
    }
    
    private function persist(TreeModel $model) {
        $className = get_class($model);
        try {
            $this->em()->beginTransaction();
            $query = $this->em()->createQuery("SELECT (e.right - 1) as right FROM $className e WHERE e.id = :id");
            $query->setParameter('id', $model->getParent()->getId());
            $rs = $query->getSingleResult();
            $right = $rs['right'];
            // Desloca todos elementos da arvore, para a direita (+2), abrindo um espaço de 2 a ser usado apra inserir o nó
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
            // salva
            $this->em()->persist($model);
            $this->em()->commit();
        } catch (Exception $e) {
            $this->em()->rollback();
            throw new Exception(sprintf(_('Erro ao inserir o registro: %s'), $e->getMessage()));
        }
    }
    
    private function merge(TreeModel $model) {
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
                $novoPai = $model->getParent();
            
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
                }
            }
            $this->em()->merge($model);
            $this->em()->commit();
        } catch (Exception $e) {
            $this->em()->rollback();
            throw new Exception(sprintf(_('Erro ao atualizar o registro: %s'), $e->getMessage()));
        }
    }
        
}


/*

 --- DELETE ---

CREATE FUNCTION sp_remover_cargo_cascata(p_id_cargo integer) RETURNS void
    AS $$
DECLARE
    v_esquerda INTEGER;
    v_direita INTEGER;
    v_tamanho INTEGER;

BEGIN

    SELECT esquerda, direita, direita - esquerda + 1
    INTO v_esquerda, v_direita, v_tamanho
    FROM cargos_aninhados
    WHERE id_cargo = p_id_cargo;

    DELETE FROM cargos_aninhados
    WHERE esquerda BETWEEN v_esquerda AND v_direita;

    UPDATE cargos_aninhados
    SET direita = (direita - v_tamanho)
    WHERE direita > v_direita;

    UPDATE cargos_aninhados
    SET esquerda = (esquerda - v_tamanho)
    WHERE esquerda > v_direita;

END
$$

 */