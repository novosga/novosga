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

        // todos servicos da unidade
        $servicos = $service->servicosUnidade($unidade);
        
        return $this->render('NovosgaSettingsBundle:default:index.html.twig', [
            'unidade' => $unidade,
            'servicos' => $servicos,
            'locais' => $locais
        ]);
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
     * @Route("/update_servico", name="novosga_settings_update_servico")
     * @Method("POST")
     */
    public function updateServicoAction(Request $request)
    {
        $response = new JsonResponse();
        try {
            $em = $this->getDoctrine()->getManager();
            
            $id = (int) $request->get('id');
            $unidade = $request->getSession()->get('unidade');

            $service = new ServicoService($em);
            $su = $service->servicoUnidade($unidade, $id);

            $sigla = $request->get('sigla');
            $peso = (int) $request->get('peso');
            $peso = max(1, $peso);
            $local = $em->find(Local::class, (int) $request->get('local'));

            $su->setSigla(strtoupper($sigla));
            $su->setPeso($peso);
            if ($local) {
                $su->setLocal($local);
            }
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
