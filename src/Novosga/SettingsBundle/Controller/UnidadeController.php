<?php

namespace Novosga\UnidadeBundle\Controller;

use Exception;
use Novosga\Context;
use Novosga\Http\JsonResponse;
use Novosga\Service\AtendimentoService;
use Novosga\Service\ServicoService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * UnidadeController.
 *
 * Controlador do módulo de configuração da unidade
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UnidadeController extends Controller
{
    const DEFAULT_SIGLA = 'A';

    public function index(Context $context)
    {
        $unidade = $context->getUnidade();
        $this->app()->view()->set('unidade', $unidade);
        if ($unidade) {
            $service = new ServicoService($this->em());

            // locais disponiveis
            $locais = $this->em()
                    ->createQuery("SELECT e FROM AppBundle\Entity\Local e ORDER BY e.nome")
                    ->getResult();

            if (count($locais)) {
                $local = $locais[0];
                $service->updateUnidade($unidade, $local, self::DEFAULT_SIGLA);
            }

            // todos servicos da unidade
            $servicos = $service->servicosUnidade($unidade);

            $this->app()->view()->set('servicos', $servicos);
            $this->app()->view()->set('locais', $locais);
        }
    }

    public function update_impressao(Context $context)
    {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $impressao = (int) $context->request()->post('impressao');
            $mensagem = $context->request()->post('mensagem', '');
            $unidade = $context->getUser()->getUnidade();
            $query = $this->em()->createQuery("UPDATE AppBundle\Entity\Unidade e SET e.statusImpressao = :status, e.mensagemImpressao = :mensagem WHERE e.id = :unidade");
            $query->setParameter('status', $impressao);
            $query->setParameter('mensagem', $mensagem);
            $query->setParameter('unidade', $unidade->getId());
            if ($query->execute()) {
                // atualizando sessao
                $unidade = $this->em()->find('AppBundle\Entity\Unidade', $unidade->getId());
                $context->setUnidade($unidade);
                $response->success = true;
            }
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function toggle_servico(Context $context, $status)
    {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $id = (int) $context->request()->post('id');
            $unidade = $context->getUser()->getUnidade();
            if (!$id || !$unidade) {
                return false;
            }

            $service = new ServicoService($this->em());
            $su = $service->servicoUnidade($unidade, $id);

            $su->setStatus($status);

            $this->em()->merge($su);
            $this->em()->flush();

            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function update_servico(Context $context)
    {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $id = (int) $context->request()->post('id');

            $service = new ServicoService($this->em());
            $su = $service->servicoUnidade($context->getUser()->getUnidade(), $id);

            $sigla = $context->request()->post('sigla');
            $peso = (int) $context->request()->post('peso');
            $peso = max(1, $peso);
            $local = $this->em()->find("AppBundle\Entity\Local", (int) $context->request()->post('local'));

            $su->setSigla($sigla);
            $su->setPeso($peso);
            if ($local) {
                $su->setLocal($local);
            }
            $this->em()->merge($su);
            $this->em()->flush();
            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function acumular_atendimentos(Context $context)
    {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new \Exception(_('Somente via POST'));
            }
            $unidade = $context->getUnidade();
            if (!$unidade) {
                throw new Exception(_('Nenhum unidade definida'));
            }
            $service = new AtendimentoService($this->em());
            $service->acumularAtendimentos($unidade);
            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }
}
