<?php

namespace Novosga\SettingsBundle\Controller;

use Exception;
use Novosga\Entity\Local;
use Novosga\Http\JsonResponse;
use Novosga\Service\AtendimentoService;
use Novosga\Service\ServicoService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * DefaultController
 *
 * Controlador do módulo de configuração da unidade
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DefaultController extends Controller
{
    const DEFAULT_SIGLA = 'A';

    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/", name="novosga_settings_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $unidade = $request->getSession()->get('unidade');
        
        $service = new ServicoService($em);

        // locais disponiveis
        $locais = $em
                    ->getRepository(Local::class)
                    ->findBy([], ['nome' => 'ASC']);

        if (count($locais)) {
            $local = $locais[0];
            $service->updateUnidade($unidade, $local, self::DEFAULT_SIGLA);
        }
        
        $form = $this->createForm(\AppBundle\Form\ServicoUnidadeType::class, new \Novosga\Entity\ServicoUnidade());

        return $this->render('NovosgaSettingsBundle:default:index.html.twig', [
            'unidade' => $unidade,
            'locais' => $locais,
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/servicos", name="novosga_settings_servicos")
     * @Method("GET")
     */
    public function servicosAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $unidade = $request->getSession()->get('unidade');
        
        $service = new ServicoService($em);
        $servicos = $service->servicosUnidade($unidade);
        
        return new JsonResponse($servicos);
    }
    
    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/contadores", name="novosga_settings_contadores")
     * @Method("GET")
     */
    public function contadoresAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $unidade = $request->getSession()->get('unidade');
        
        $contadores = $em
            ->createQueryBuilder()
            ->select('e')
            ->from(\Novosga\Entity\Contador::class, 'e')
            ->join('e.servico', 's')
            ->join(\Novosga\Entity\ServicoUnidade::class, 'su', 'WITH', 'su.servico = s')
            ->where('su.unidade = :unidade')
            ->setParameter('unidade', $unidade)
            ->getQuery()
            ->getResult();
        
        return new JsonResponse($contadores);
    }
    
    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/servicos/{id}", name="novosga_settings_servicos_update")
     * @Method("POST")
     */
    public function updateServicoAction(Request $request, $id)
    {
        $json = $request->getContent();
        $data = json_decode($json);
        
        $em = $this->getDoctrine()->getManager();
        $unidade = $request->getSession()->get('unidade');
        
        $service = new ServicoService($em);
        $su = $service->servicoUnidade($unidade, $id);
        
        if ($data->sigla) {
            $su->setSigla($data->sigla);
        }
        
        if ($data->local) {
            $local = $em->find(Local::class, (int) $data->local->id);
            if ($local) {
                $su->setLocal($local);
            }
        }
        
        if ($data->peso) {
            $su->setPeso(max(1, $data->peso));
        }
        
        $su->setStatus(!!$data->status);
        
        $em->merge($su);
        $em->flush();
        
        return new JsonResponse($su);
    }

    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/update_impressao", name="novosga_settings_update_impressao")
     * @Method("POST")
     */
    public function updateImpressaoAction(Request $request)
    {
        $response = new JsonResponse();
        try {
            $em = $this->getDoctrine()->getManager();
            
            $impressao = (int) $request->get('impressao');
            $mensagem = $request->get('mensagem', '');
            $unidade = $request->getSession()->get('unidade');
            
            $query = $em->createQuery("UPDATE Novosga\Entity\Unidade e SET e.statusImpressao = :status, e.mensagemImpressao = :mensagem WHERE e.id = :unidade");
            $query->setParameter('status', $impressao);
            $query->setParameter('mensagem', $mensagem);
            $query->setParameter('unidade', $unidade);
            
            if ($query->execute()) {
                // atualizando sessao
                $unidade = $em->find('Novosga\Entity\Unidade', $unidade->getId());
                $request->getSession()->set('unidade', $unidade);
                $response->success = true;
            }
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/toggle_servico/{status}", name="novosga_settings_toggle_servico")
     * @Method("POST")
     */
    public function toggleServicoAction(Request $request, $status)
    {
        $response = new JsonResponse();
        try {
            $em = $this->getDoctrine()->getManager();
            
            $id = (int) $request->get('id');
            $unidade = $request->getSession()->get('unidade');
            
            if (!$id || !$unidade) {
                return false;
            }

            $service = new ServicoService($em);
            $su = $service->servicoUnidade($unidade, $id);

            $su->setStatus($status);

            $em->merge($su);
            $em->flush();

            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/acumular_atendimentos", name="novosga_settings_acumular_atendimentos")
     * @Method("POST")
     */
    public function reiniciarAction(Request $request, $status)
    {
        $response = new JsonResponse();
        try {
            $em = $this->getDoctrine()->getManager();
            $unidade = $request->getSession()->get('unidade');
            $service = new AtendimentoService($em);
            $service->acumularAtendimentos($unidade);
            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }
}
