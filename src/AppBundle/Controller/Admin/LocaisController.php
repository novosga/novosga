<?php

namespace AppBundle\Controller\Admin;

use Novosga\Entity\Local;
use Novosga\Http\Envelope;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminLocaisController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/locais")
 */
class LocaisController extends Controller
{
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/", name="admin_locais_index")
     */
    public function indexAction(Request $request)
    {
        return $this->render('admin/locais.html.twig', [
            'tab' => 'locais',
        ]);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/list", name="admin_locais_list")
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $locais = $em
                ->getRepository(Local::class)
                ->findBy([], ['nome' => 'ASC']);
        
        return $this->json($locais);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/save", name="admin_locais_save")
     */
    public function saveAction(Request $request)
    {
        try {
            $json = $request->getContent();
            $data = json_decode($json);

            if (!isset($data->nome)) {
                throw new \Exception('Json inválido');
            }
            
            $em = $this->getDoctrine()->getManager();

            if (isset($data->id)) {
                $local = $em->find(Local::class, $data->id);
                $local->setNome($data->nome);
                $em->merge($local);
            } else {
                $local = new Local();
                $local->setNome($data->nome);
                $em->persist($local);
            }
            
            $em->flush();
            
            return $this->json($local);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), false);
        }
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/delete/{id}", name="admin_locais_delete")
     */
    public function deleteAction(Request $request, Local $local)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($local);
            $em->flush();
            
            return $this->json();
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), false);
        }
    }

}
