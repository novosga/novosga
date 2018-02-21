<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Form\PerfilType as EntityType;
use Novosga\Entity\Perfil as Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PerfisController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/perfis")
 */
class PerfisController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="admin_perfis_index")
     */
    public function index(Request $request)
    {
        $perfis = $this
                ->getDoctrine()
                ->getManager()
                ->createQueryBuilder()
                ->select('e')
                ->from(Entity::class, 'e')
                ->getQuery()
                ->getResult();

        return $this->render('admin/perfis/index.html.twig', [
            'tab'    => 'perfis',
            'perfis' => $perfis
        ]);
    }

    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/new", name="admin_perfis_new")
     * @Route("/{id}", name="admin_perfis_edit")
     * @Method({"GET", "POST"})
     */
    public function form(Request $request, Entity $entity = null)
    {
        if (!$entity) {
            $entity = new Entity;
        }

        $kernel = $this->get('kernel');
        $modulos = array_filter($kernel->getBundles(), function ($module) {
            return ($module instanceof \Novosga\Module\ModuleInterface);
        });

        $form = $this->createForm(EntityType::class, $entity, [
            'modulos' => $modulos,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $trans = $this->get('translator');

            $this->addFlash('success', $trans->trans('Perfil salvo com sucesso!'));

            return $this->redirectToRoute('admin_perfis_edit', [ 'id' => $entity->getId() ]);
        }

        return $this->render('admin/perfis/form.html.twig', [
            'tab'    => 'perfis',
            'entity' => $entity,
            'form'   => $form->createView(),
        ]);
    }

    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/{id}", name="admin_perfis_delete")
     * @Method("DELETE")
     */
    public function delete(Request $request, Entity $perfil)
    {
        $trans = $this->get('translator');

        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($perfil);
            $em->flush();

            $this->addFlash('success', $trans->trans('Perfil removido com sucesso!'));

            return $this->redirectToRoute('admin_perfis_index');
        } catch (\Exception $e) {
            if ($e instanceof \Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
                $message = 'O perfil não pode ser removido porque está sendo utilizado.';
            } else {
                $message = $e->getMessage();
            }

            $this->addFlash('error', $trans->trans($message));

            return $this->redirect($request->headers->get('REFERER'));
        }
    }
}
