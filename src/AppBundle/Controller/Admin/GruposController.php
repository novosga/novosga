<?php

namespace AppBundle\Controller\Admin;

use Novosga\Entity\Grupo as Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * GruposController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 * 
 * @Route("/admin/grupos")
 */
class GruposController extends Controller
{

    /**
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/", name="admin_grupos_index")
     */
    public function indexAction(Request $request)
    {
        $form = $this->getForm(new Entity);
        
        return $this->render('admin/grupos/index.html.twig', [
            'tab' => 'grupos',
            'form' => $form->createView()
        ]);
    }

    /**
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/list", name="admin_grupos_list")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $groups = $em
            ->getRepository(Entity::class)
            ->findAll();
        
        return $this->json($groups);
    }
    
    /**
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/edit", name="admin_grupos_edit")
     */
    public function editAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $id = (int) $request->get('id');
        $entity = $em->find(Entity::class, $id);
        if (!$entity) {
            $entity = new Entity;
        }
        
        $form = $this->getForm($entity);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $parent = $entity->getParent();
            if ($parent) {
                $level = $parent->getLevel() + 1;
            } else {
                $level = 0;
            }
            $entity->setLevel($level);
            
            if ($entity->getId()) {
                $this->merge($entity);
            } else {
                $this->persist($entity);
            }
        }
        
        return $this->redirectToRoute('admin_grupos_index');
    }
    
    private function getForm($entity)
    {
        $form = $this->createForm(\AppBundle\Form\GrupoType::class, $entity, [
            'action' => $this->generateUrl('admin_grupos_edit')
        ]);
        
        return $form;
    }
    
    private function persist(Entity $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $className = get_class($entity);
        try {
            $em->beginTransaction();
            // persiste a nova entidade
            $em->persist($entity);
            $right = $entity->getParent($em)->getRight() - 1;
            // desloca todos elementos da arvore, para a direita (+2), abrindo um espaço de 2 a ser usado para inserir o nó
            $query = $em->createQuery("UPDATE $className e SET e.right = e.right + 2 WHERE e.right > :right");
            $query->setParameter('right', $right);
            $query->execute();
            // continuação do deslocamento acima (agora para o "esquerda")
            $query = $em->createQuery("UPDATE $className e SET e.left = e.left + 2 WHERE e.left > :right");
            $query->setParameter('right', $right);
            $query->execute();
            // atualiza lados
            $entity->setLeft($right + 1);
            $entity->setRight($right + 2);
            $entity->setLevel($entity->getParent($em)->getLevel() + 1);
            $em->commit();
            $em->flush();
        } catch (Exception $e) {
            $em->rollback();
            throw new Exception(sprintf(_('Erro ao inserir o registro: %s'), $e->getMessage()));
        }
    }

    private function merge(Entity $entity)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $className = get_class($entity);
            $em->beginTransaction();
            // se nao for raiz, verifica o pai
            if ($entity->getLeft() > 1) {
                $query = $em->createQuery("
                    SELECT pai
                    FROM $className pai
                    WHERE
                        pai.id IN (SELECT p2.id FROM $className no JOIN no.parent p2 WHERE no.id = :id)
                ");
                $query->setParameter('id', $entity->getId());
                $query->setMaxResults(1);
                $paiAtual = $query->getSingleResult();
                $novoPai = $entity->getParent();

                // se mudou o pai
                if ($paiAtual->getId() != $novoPai->getId()) {
                    $tamanho = $entity->getRight() - $entity->getLeft() + 1;

                    $direita = $novoPai->getRight() - 1;
                    $query = $em->createQuery("UPDATE $className e SET e.right = e.right + :tamanho WHERE e.right > :direita_pai");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita_pai', $direita);
                    $query->execute();

                    $query = $em->createQuery("UPDATE $className e SET e.left = e.left + :tamanho WHERE e.left > :direita_pai");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita_pai', $direita);
                    $query->execute();

                    if ($entity->getLeft() > $direita) {
                        $entity->setLeft($entity->getLeft() + $tamanho);
                    }
                    if ($entity->getRight() > $direita) {
                        $entity->setRight($entity->getRight() + $tamanho);
                    }

                    $deslocamento = ($novoPai->getRight() + $tamanho) - $entity->getRight() - 1;

                    $query = $em->createQuery("UPDATE $className e SET e.right = e.right + :deslocamento, e.left = e.left + :deslocamento WHERE e.left >= :esquerda AND e.right <= :direita");
                    $query->setParameter('deslocamento', $deslocamento);
                    $query->setParameter('esquerda', $entity->getLeft());
                    $query->setParameter('direita', $entity->getRight());
                    $query->execute();

                    $query = $em->createQuery("UPDATE $className e SET e.right = e.right - :tamanho WHERE e.right > :direita");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita', $entity->getRight());
                    $query->execute();

                    $query = $em->createQuery("UPDATE $className e SET e.left = e.left - :tamanho WHERE e.left > :direita");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita', $entity->getRight());
                    $query->execute();

                    $query = $em->createQuery("SELECT e.left, e.right FROM $className e WHERE e.id = :id");
                    $query->setParameter('id', $entity->getId());
                    $rs = $query->getSingleResult();
                    $entity->setLeft($rs['left']);
                    $entity->setRight($rs['right']);
                    $newLevel = $entity->getParent($em)->getLevel() + 1;
                    $delta = $newLevel - $entity->getLevel();
                    $entity->setLevel($newLevel);
                    $this->updateLevels($entity, $delta);
                }
            }
            $em->merge($entity);
            $em->commit();
            $em->flush();
        } catch (Exception $e) {
            $em->rollback();
            throw new Exception(sprintf(_('Erro ao atualizar o registro: %s'), $e->getMessage()));
        }
    }
    
    /**
     * Atualiza os niveis dos nós filhos da arvore.
     */
    private function updateLevels(Entity $entity, $delta)
    {
        $em = $this->getDoctrine()->getManager();
        $className = get_class($entity);
        // atualizando
        $query = $em->createQuery("
            UPDATE $className e
            SET e.level = e.level + :delta
            WHERE e.left > :left AND e.right < :right
        ");
        $query->setParameter('left', $entity->getLeft());
        $query->setParameter('right', $entity->getRight());
        $query->setParameter('delta', $delta);
        $query->execute();
    }
}
