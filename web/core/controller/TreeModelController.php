<?php
namespace core\controller;

use \Exception;
use \core\model\TreeModel;
use core\model\SequencialModel;

/**
 * TreeModelController
 * Classe pai para cadastros de arvore aninhada
 *
 * @author rogeriolino
 */
abstract class TreeModelController extends CrudController {
    
    /**
     * Insere ou atualiza a entidade no banco
     * @param \core\model\SequencialModel $model
     */
    protected function save(SequencialModel $model) {
        if (!($model instanceof TreeModel)) {
            throw new Exception(sprintf(_('Modelo inválido passado como parâmetro. Era esperado TreeModel e passou %s'), get_class($model)));
        }
        $this->preSave($model);
        $className = get_class($model);
        if ($model->getId() > 0) {
            // update
            // TODO: fazer de acordo com o sql no rodape (removidas as procedures)
        } else {
            // insert
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
            }
        }
        $this->postSave($model);
        $this->em()->flush();
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


 --- UPDATE ---


UPDATE grupos_aninhados
    SET nm_grupo = p_nm_grupo, desc_grupo = p_desc_grupo
    WHERE id_grupo = p_id_grupo;

    SELECT pai.id_grupo, pai.esquerda, pai.direita
    INTO v_id_pai_atual, v_esq_pai_atual, v_dir_pai_atual
    FROM grupos_aninhados AS no,
    grupos_aninhados AS pai
    WHERE no.esquerda > pai.esquerda
        AND no.direita < pai.direita
    AND no.id_grupo = p_id_grupo
    ORDER BY pai.esquerda DESC
    LIMIT 1;

    IF v_id_pai_atual != p_id_pai THEN

        SELECT esquerda, direita, (direita - esquerda + 1)
        INTO v_esq_grupo, v_dir_grupo, v_len_grupo
        FROM grupos_aninhados
        WHERE id_grupo = p_id_grupo
        LIMIT 1;


        SELECT (direita - 1)
        INTO v_pai_direita
        FROM grupos_aninhados
        WHERE id_grupo = p_id_pai;


        UPDATE grupos_aninhados
        SET direita = direita + v_len_grupo
        WHERE direita > v_pai_direita;

        UPDATE grupos_aninhados
        SET esquerda = esquerda + v_len_grupo
        WHERE esquerda > v_pai_direita;


        SELECT esquerda, direita, (direita - esquerda + 1)
        INTO v_esq_novo_pai, v_dir_novo_pai, v_len_novo_pai
        FROM grupos_aninhados
        WHERE id_grupo = p_id_pai
        LIMIT 1;


        SELECT direita
        INTO v_dir_pai_atual
        FROM grupos_aninhados
        WHERE id_grupo = v_id_pai_atual
        LIMIT 1;

        SELECT esquerda, direita
        INTO v_esq_grupo, v_dir_grupo
        FROM grupos_aninhados
        WHERE id_grupo = p_id_grupo
        LIMIT 1;

        v_deslocamento := v_dir_novo_pai - v_dir_grupo - 1;

        UPDATE grupos_aninhados
        SET direita = direita + v_deslocamento,
            esquerda = esquerda + v_deslocamento
        WHERE esquerda >= v_esq_grupo
            AND direita <= v_dir_grupo;


        UPDATE grupos_aninhados
        SET direita = direita - v_len_grupo
        WHERE direita > v_dir_grupo;

        UPDATE grupos_aninhados
        SET esquerda = esquerda - v_len_grupo WHERE esquerda > v_dir_grupo;
    END IF;

 */