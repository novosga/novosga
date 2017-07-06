<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller\Admin;

use Exception;
use Novosga\Entity\Prioridade;
use Novosga\Http\Envelope;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PrioridadesController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/prioridades")
 */
class PrioridadesController extends Controller
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
        return $this->render('admin/prioridades/index.html.twig', [
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
        
        $prioridades = $em
                ->getRepository(Prioridade::class)
                ->findBy([], ['nome' => 'ASC']);
        
        $envelope = new Envelope();
        $envelope->setData($prioridades);
        
        return $this->json($envelope);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/save", name="admin_prioridades_save")
     * @Method("POST")
     */
    public function saveAction(Request $request)
    {
        $envelope = new Envelope();
        
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
                $prioridade->setPeso((int) $data->peso);
                $em->merge($prioridade);
            } else {
                $prioridade = new Prioridade();
                $prioridade->setNome($data->nome);
                $prioridade->setPeso((int) $data->peso);
                $prioridade->setDescricao('');
                $prioridade->setAtivo(true);
                $em->persist($prioridade);
            }
            
            $em->flush();
            
            $envelope->setData($prioridade);
        } catch (Exception $e) {
            $envelope->exception($e);
        }
        
        return $this->json($envelope);
    }
    
    /**
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/delete/{id}", name="admin_prioridades_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, Prioridade $prioridade)
    {
        $envelope = new Envelope();
        
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($prioridade);
            $em->flush();
            
            $envelope->setData($prioridade);
        } catch (Exception $e) {
            $envelope->exception($e);
        }
        
        return $this->json($envelope);
    }
}
