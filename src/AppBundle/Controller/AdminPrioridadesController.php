<?php

namespace AppBundle\Controller;

use Exception;
use Novosga\Entity\Prioridade;
use Novosga\Http\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminPrioridadesController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/prioridades")
 */
class AdminPrioridadesController extends Controller
{
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="admin_prioridades_index")
     */
    public function indexAction(Request $request)
    {
        return $this->render('admin/prioridades.html.twig', [
            'tab' => 'prioridades',
        ]);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/list", name="admin_prioridades_list")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $locais = $em
                ->getRepository(Prioridade::class)
                ->findBy([], ['nome' => 'ASC']);
        
        return new JsonResponse($locais);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/save", name="admin_prioridades_save")
     */
    public function saveAction(Request $request)
    {
        try {
            $json = $request->getContent();
            $data = json_decode($json);

            if (!isset($data->nome) || !(isset($data->peso))) {
                throw new Exception('Json inválido');
            }
            
            $em = $this->getDoctrine()->getManager();

            if (isset($data->id)) {
                $prioridade = $em->find(Prioridade::class, $data->id);
                $prioridade->setNome($data->nome);
                $prioridade->setPeso($data->peso);
                $em->merge($prioridade);
            } else {
                $prioridade = new Prioridade();
                $prioridade->setNome($data->nome);
                $prioridade->setPeso($data->peso);
                $prioridade->setDescricao('');
                $prioridade->setStatus(1);
                $em->persist($prioridade);
            }
            
            $em->flush();
            
            return new JsonResponse($prioridade);
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), false);
        }
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/delete/{id}", name="admin_prioridades_delete")
     */
    public function deleteAction(Request $request, Prioridade $prioridade)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($prioridade);
            $em->flush();
            
            return new JsonResponse();
        } catch (Exception $e) {
            return new JsonResponse($e->getMessage(), false);
        }
    }

}
