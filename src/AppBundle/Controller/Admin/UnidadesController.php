<?php

namespace AppBundle\Controller\Admin;

use Exception;
use Novosga\Entity\Unidade;
use Novosga\Http\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * UnidadesController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/unidades")
 */
class UnidadesController extends Controller
{
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="admin_unidades_index")
     */
    public function indexAction(Request $request)
    {
        return $this->render('admin/unidades.html.twig', [
            'tab' => 'unidades',
        ]);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/list", name="admin_unidades_list")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $unidades = $em
                ->getRepository(Unidade::class)
                ->findBy([], ['nome' => 'ASC']);
        
        return new JsonResponse($unidades);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/save", name="admin_unidades_save")
     */
    public function saveAction(Request $request)
    {
        try {
            $json = $request->getContent();
            $data = json_decode($json);

            if (!isset($data->nome)) {
                throw new Exception('Json inválido');
            }
            
            $em = $this->getDoctrine()->getManager();

            if (isset($data->id)) {
                $unidade = $em->find(Unidade::class, $data->id);
                $unidade->setNome($data->nome);
                $em->merge($unidade);
            } else {
                $unidade = new Unidade();
                $unidade->setNome($data->nome);
                $unidade->setStatus(1);
                $em->persist($unidade);
            }
            
            $em->flush();
            
            return new JsonResponse($unidade);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), false);
        }
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/delete/{id}", name="admin_unidades_delete")
     */
    public function deleteAction(Request $request, Unidade $unidade)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($unidade);
            $em->flush();
            
            return new JsonResponse();
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), false);
        }
    }

}
