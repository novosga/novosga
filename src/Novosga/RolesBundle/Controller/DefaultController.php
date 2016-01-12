<?php

namespace Novosga\RolesBundle\Controller;

use AppBundle\Entity\Cargo as Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\TreeModel;

/**
 * DefaultController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 * 
 */
class DefaultController extends Controller
{

    /**
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/", name="novosga_roles_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $form = $this->getForm();
        
        $tree = $em
            ->getRepository(Entity::class)
            ->findAll();
        
        
        return $this->render('NovosgaRolesBundle:Default:index.html.twig', [
            'tree' => $tree,
            'form' => $form->createView()
        ]);
    }
    
    /**
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/edit", name="novosga_roles_edit")
     */
    public function editAction(Request $request)
    {
        $form = $this->getForm();
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $entity = $form->getData();
            $parent = $entity->getParent();
            $entity->setLevel($parent->getLevel() + 1);
            
            if ($entity->getId()) {
                $this->merge($entity);
            } else {
                $this->persist($entity);
            }
        }
        
        return $this->redirectToRoute('novosga_roles_index');
    }
    
    private function getForm()
    {
        $form = $this->createForm(\AppBundle\Form\CargoType::class, new Entity, [
            'action' => $this->generateUrl('novosga_roles_edit')
        ]);
        
        return $form;
    }
    
    private function persist(TreeModel $model)
    {
        $em = $this->getDoctrine()->getManager();
        $className = get_class($model);
        try {
            $em->beginTransaction();
            // persiste a nova entidade
            $em->persist($model);
            $right = $model->getParent($em)->getRight() - 1;
            // desloca todos elementos da arvore, para a direita (+2), abrindo um espaço de 2 a ser usado para inserir o nó
            $query = $em->createQuery("UPDATE $className e SET e.right = e.right + 2 WHERE e.right > :right");
            $query->setParameter('right', $right);
            $query->execute();
            // continuação do deslocamento acima (agora para o "esquerda")
            $query = $em->createQuery("UPDATE $className e SET e.left = e.left + 2 WHERE e.left > :right");
            $query->setParameter('right', $right);
            $query->execute();
            // atualiza lados
            $model->setLeft($right + 1);
            $model->setRight($right + 2);
            $model->setLevel($model->getParent($em)->getLevel() + 1);
            $em->commit();
            $em->flush();
        } catch (Exception $e) {
            $em->rollback();
            throw new Exception(sprintf(_('Erro ao inserir o registro: %s'), $e->getMessage()));
        }
    }

    private function merge(TreeModel $model)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $className = get_class($model);
            $em->beginTransaction();
            // se nao for raiz, verifica o pai
            if ($model->getLeft() > 1) {
                $query = $em->createQuery("
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
                $novoPai = $model->getParent($em);

                // se mudou o pai
                if ($paiAtual->getId() != $novoPai->getId()) {
                    $tamanho = $model->getRight() - $model->getLeft() + 1;

                    $direita = $novoPai->getRight() - 1;
                    $query = $em->createQuery("UPDATE $className e SET e.right = e.right + :tamanho WHERE e.right > :direita_pai");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita_pai', $direita);
                    $query->execute();

                    $query = $em->createQuery("UPDATE $className e SET e.left = e.left + :tamanho WHERE e.left > :direita_pai");
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

                    $query = $em->createQuery("UPDATE $className e SET e.right = e.right + :deslocamento, e.left = e.left + :deslocamento WHERE e.left >= :esquerda AND e.right <= :direita");
                    $query->setParameter('deslocamento', $deslocamento);
                    $query->setParameter('esquerda', $model->getLeft());
                    $query->setParameter('direita', $model->getRight());
                    $query->execute();

                    $query = $em->createQuery("UPDATE $className e SET e.right = e.right - :tamanho WHERE e.right > :direita");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita', $model->getRight());
                    $query->execute();

                    $query = $em->createQuery("UPDATE $className e SET e.left = e.left - :tamanho WHERE e.left > :direita");
                    $query->setParameter('tamanho', $tamanho);
                    $query->setParameter('direita', $model->getRight());
                    $query->execute();

                    $query = $em->createQuery("SELECT e.left, e.right FROM $className e WHERE e.id = :id");
                    $query->setParameter('id', $model->getId());
                    $rs = $query->getSingleResult();
                    $model->setLeft($rs['left']);
                    $model->setRight($rs['right']);
                    $newLevel = $model->getParent($em)->getLevel() + 1;
                    $delta = $newLevel - $model->getLevel();
                    $model->setLevel($newLevel);
                    $this->updateLevels($model, $delta);
                }
            }
            $em->merge($model);
            $em->commit();
            $em->flush();
        } catch (Exception $e) {
            $em->rollback();
            throw new Exception(sprintf(_('Erro ao atualizar o registro: %s'), $e->getMessage()));
        }
    }
}
