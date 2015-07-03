<?php

namespace modules\sga\unidade;

use Exception;
use Novosga\Service\AtendimentoService;
use Novosga\Context;
use Novosga\Controller\ModuleController;
use Novosga\Http\JsonResponse;
use Novosga\Service\ServicoService;

/**
 * UnidadeController.
 *
 * Controlador do módulo de configuração da unidade
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UnidadeController extends ModuleController
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
                    ->createQuery("SELECT e FROM Novosga\Model\Local e ORDER BY e.nome")
                    ->getResult()
            ;

            if (sizeof($locais)) {
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
            $query = $this->em()->createQuery("UPDATE Novosga\Model\Unidade e SET e.statusImpressao = :status, e.mensagemImpressao = :mensagem WHERE e.id = :unidade");
            $query->setParameter('status', $impressao);
            $query->setParameter('mensagem', $mensagem);
            $query->setParameter('unidade', $unidade->getId());
            if ($query->execute()) {
                // atualizando sessao
                $unidade = $this->em()->find('Novosga\Model\Unidade', $unidade->getId());
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
            $local = $this->em()->find("Novosga\Model\Local", (int) $context->request()->post('local'));

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
