<?php

namespace modules\sga\atendimento;

use Exception;
use Novosga\App;
use Novosga\Context;
use Novosga\Util\Arrays;
use Novosga\Util\DateUtil;
use Novosga\Service\UsuarioService;
use Novosga\Service\AtendimentoService;
use Novosga\Service\FilaService;
use Novosga\Controller\ModuleController;
use Novosga\Model\Atendimento;
use Novosga\Model\Modulo;
use Novosga\Model\Util\UsuarioSessao;
use Novosga\Http\JsonResponse;
use Novosga\Config\AppConfig;

/**
 * AtendimentoController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AtendimentoController extends ModuleController
{
    private $filaService;
    private $usuarioService;
    private $atendimentoService;

    public function __construct(App $app, Modulo $modulo)
    {
        parent::__construct($app, $modulo);
        $this->filaService = new FilaService($this->em());
        $this->usuarioService = new UsuarioService($this->em());
        $this->atendimentoService = new AtendimentoService($this->em());
    }

    public function index(Context $context)
    {
        $usuario = $context->getUser();
        $unidade = $context->getUnidade();
        if (!$usuario || !$unidade) {
            $this->app()->gotoHome();
        }

        $localMeta = $this->usuarioService->meta($usuario->getWrapped(), UsuarioService::ATTR_ATENDIMENTO_LOCAL);
        if ($localMeta) {
            $usuario->setLocal((int) $localMeta->getValue());
        }
        $tipoMeta = $this->usuarioService->meta($usuario->getWrapped(), UsuarioService::ATTR_ATENDIMENTO_TIPO);
        if ($tipoMeta) {
            $usuario->setTipoAtendimento((int) $tipoMeta->getValue());
        }

        $this->app()->view()->set('time', time() * 1000);
        $this->app()->view()->set('unidade', $unidade);
        $this->app()->view()->set('atendimento', $this->atendimentoService->atendimentoAndamento($usuario->getId()));
        $this->app()->view()->set('servicos', $usuario->getServicos());
        $this->app()->view()->set('servicosIndisponiveis', $usuario->getServicosIndisponiveis());

        $tiposAtendimento = array(
            UsuarioSessao::ATEND_TODOS => _('Todos'),
            UsuarioSessao::ATEND_CONVENCIONAL => _('Convencional'),
            UsuarioSessao::ATEND_PRIORIDADE => _('Prioridade'),
        );

        $this->app()->view()->set('tiposAtendimento', $tiposAtendimento);
        $this->app()->view()->set('local', $usuario->getLocal());
        $this->app()->view()->set('tipoAtendimento', $usuario->getTipoAtendimento());
    }

    public function set_local(Context $context)
    {
        $response = new JsonResponse();
        try {
            $unidade = $context->getUnidade();
            $usuario = $context->getUser();
            $numero = (int) $context->request()->post('local');
            $tipo = (int) $context->request()->post('tipo');

            AppConfig::getInstance()->hook('sga.atendimento.pre-setlocal', array($unidade, $usuario, $numero, $tipo));
            
            $this->usuarioService->meta($usuario->getWrapped(), UsuarioService::ATTR_ATENDIMENTO_LOCAL, $numero);
            $this->usuarioService->meta($usuario->getWrapped(), UsuarioService::ATTR_ATENDIMENTO_TIPO, $tipo);
            $usuario->setLocal($numero);
            $usuario->setTipoAtendimento($tipo);
            $context->setUser($context->getUser());
            
            AppConfig::getInstance()->hook('sga.atendimento.setlocal', array($unidade, $usuario, $numero, $tipo));

            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
            $response->success = false;
        }

        return $response;
    }

    public function ajax_update(Context $context)
    {
        $response = new JsonResponse();
        $unidade = $context->getUnidade();
        $usuarioSessao = $context->getUser();
        if ($unidade && $usuarioSessao) {
            // retorna configuracao do usuario para conferir possiveis alteracoes
            $this->checkUserConfig($context, $usuarioSessao);

            // fila de atendimento do atendente atual
            $response->data = array(
                'atendimentos' => $this->filaService->atendimentos($usuarioSessao),
                'usuario' => array(
                    'numeroLocal' => $usuarioSessao->getLocal(),
                    'tipoAtendimento' => $usuarioSessao->getTipoAtendimento(),
                ),
            );
            $response->success = true;
        }

        return $response;
    }

    /**
     * Chama ou rechama o próximo da fila.
     *
     * @param Novosga\Context $context
     */
    public function chamar(Context $context)
    {
        $response = new JsonResponse();
        try {
            if (!$context->request()->isPost()) {
                throw new Exception(_('Somente via POST'));
            }
            $attempts = 5;
            $proximo = null;
            $success = false;
            $usuario = $context->getUser();
            $unidade = $context->getUnidade();
            if (!$usuario) {
                throw new Exception(_('Nenhum usuário na sessão'));
            }
            // verifica se ja esta atendendo alguem
            $atual = $this->atendimentoService->atendimentoAndamento($usuario->getId());
            // se ja existe um atendimento em andamento (chamando senha novamente)
            if ($atual) {
                $success = true;
                $proximo = $atual;
            } else {
                do {
                    $atendimentos = $this->filaService->atendimentos($usuario, 1);
                    if (sizeof($atendimentos)) {
                        $proximo = $atendimentos[0];
                        $success = $this->atendimentoService->chamar($proximo, $usuario->getWrapped(), $usuario->getLocal());
                        if ($success) {
                            // incrementando contadores
                            if ($proximo->getPrioridade()->getPeso() > 0) {
                                $usuario->setSequenciaPrioridade($usuario->getSequenciaPrioridade() + 1);
                            } else {
                                $usuario->setSequenciaPrioridade(0);
                            }
                            $context->setUser($usuario);
                        } else {
                            usleep(100);
                        }
                        --$attempts;
                    } else {
                        // nao existe proximo
                        break;
                    }
                } while (!$success && $attempts > 0);
            }
            // response
            if (!$success) {
                if (!$proximo) {
                    throw new Exception(_('Fila vazia'));
                } else {
                    throw new Exception(_('Já existe um atendimento em andamento'));
                }
            }
            // response
            $response->success = $success;
            $this->atendimentoService->chamarSenha($unidade, $proximo);
            $response->data = $proximo->jsonSerialize();
        } catch (Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();
        }

        return $response;
    }

    private function checkUserConfig(Context $context, UsuarioSessao $usuario)
    {
        $service = new UsuarioService($this->em());
        $numeroLocalMeta = $service->meta($usuario->getWrapped(), 'atendimento.local');
        $numero = $numeroLocalMeta ? (int) $numeroLocalMeta->getValue() : $usuario->getLocal();
        $tipoAtendimentoMeta = $service->meta($usuario->getWrapped(), 'atendimento.tipo');
        $tipoAtendimento = $tipoAtendimentoMeta ? (int) $tipoAtendimentoMeta->getValue() : $usuario->getTipoAtendimento();

        if ($numero != $usuario->getLocal()) {
            $usuario->setLocal($numero);
        }
        if ($tipoAtendimento != $usuario->getTipoAtendimento()) {
            $usuario->setTipoAtendimento($tipoAtendimento);
        }

        $context->setUser($usuario);
    }

    /**
     * Muda o status do atendimento atual.
     *
     * @param mixed  $statusAtual (array[int] | int)
     * @param int    $novoStatus
     * @param string $campoData
     *
     * @return JsonResponse
     */
    private function mudaStatusAtualResponse(Context $context, $statusAtual, $novoStatus, $campoData)
    {
        $usuario = $context->getUser();
        if (!$usuario) {
            $this->app()->gotoHome();
        }
        $response = new JsonResponse();
        $atual = $this->atendimentoService->atendimentoAndamento($usuario->getId());
        if ($atual) {
            // atualizando atendimento
            $response->success = $this->mudaStatusAtendimento($atual, $statusAtual, $novoStatus, $campoData);
        }
        if ($response->success) {
            $response->data = $atual->jsonSerialize();
        } else {
            $response->message = _('Nenhum atendimento disponível');
        }

        return $response;
    }

    /**
     * @param Atendimento $atendimento
     * @param mixed       $statusAtual (array[int] | int)
     * @param int         $novoStatus
     * @param string      $campoData
     *
     * @return bool
     */
    private function mudaStatusAtendimento(Atendimento $atendimento, $statusAtual, $novoStatus, $campoData)
    {
        $cond = '';
        if ($campoData !== null) {
            $cond = ", e.$campoData = :data";
        }
        if (!is_array($statusAtual)) {
            $statusAtual = array($statusAtual);
        }
        // atualizando atendimento
        $query = $this->em()->createQuery("
            UPDATE
                Novosga\Model\Atendimento e
            SET
                e.status = :novoStatus $cond
            WHERE
                e.id = :id AND
                e.status IN (:statusAtual)
        ");
        if ($campoData !== null) {
            $query->setParameter('data', DateUtil::nowSQL());
        }
        $query->setParameter('novoStatus', $novoStatus);
        $query->setParameter('id', $atendimento->getId());
        $query->setParameter('statusAtual', $statusAtual);

        return $query->execute() > 0;
    }

    /**
     * Inicia o atendimento com o proximo da fila.
     *
     * @param Novosga\Context $context
     */
    public function iniciar(Context $context)
    {
        return $this->mudaStatusAtualResponse($context, AtendimentoService::CHAMADO_PELA_MESA, AtendimentoService::ATENDIMENTO_INICIADO, 'dataInicio');
    }

    /**
     * Marca o atendimento como nao compareceu.
     *
     * @param Novosga\Context $context
     */
    public function nao_compareceu(Context $context)
    {
        return $this->mudaStatusAtualResponse($context, AtendimentoService::CHAMADO_PELA_MESA, AtendimentoService::NAO_COMPARECEU, 'dataFim');
    }

    /**
     * Marca o atendimento como encerrado.
     *
     * @param Novosga\Context $context
     */
    public function encerrar(Context $context)
    {
        return $this->mudaStatusAtualResponse($context, AtendimentoService::ATENDIMENTO_INICIADO, AtendimentoService::ATENDIMENTO_ENCERRADO, null);
    }

    /**
     * Marca o atendimento como encerrado e codificado.
     *
     * @param Novosga\Context $context
     */
    public function codificar(Context $context)
    {
        $response = new JsonResponse(false);
        try {
            if (!$context->request()->isPost()) {
                throw new Exception(_('Somente via POST'));
            }
            $unidade = $context->getUnidade();
            if (!$unidade) {
                throw new Exception(_('Nenhum unidade escolhida'));
            }
            $usuario = $context->getUser();
            $atual = $this->atendimentoService->atendimentoAndamento($usuario->getId());
            if (!$atual) {
                throw new Exception(_('Nenhum atendimento em andamento'));
            }
            $servicos = $context->request()->post('servicos');
            $servicos = Arrays::valuesToInt(explode(',', $servicos));
            if (empty($servicos)) {
                throw new Exception(_('Nenhum serviço selecionado'));
            }

            $this->em()->beginTransaction();
            foreach ($servicos as $s) {
                $codificado = new \Novosga\Model\AtendimentoCodificado();
                $codificado->setAtendimento($atual);
                $codificado->setServico($this->em()->find('Novosga\Model\Servico', $s));
                $codificado->setPeso(1);
                $this->em()->persist($codificado);
            }
            // verifica se esta encerrando e redirecionando
            $redirecionar = $context->request()->post('redirecionar');
            if ($redirecionar) {
                $servico = $context->request()->post('novoServico');
                $redirecionado = $this->atendimentoService->redirecionar($atual, $usuario->getWrapped(), $unidade, $servico);
                if (!$redirecionado->getId()) {
                    throw new Exception(sprintf(_('Erro ao redirecionar atendimento %s para o serviço %s'), $atual->getId(), $servico));
                }
            }
            $response->success = $this->mudaStatusAtendimento($atual, AtendimentoService::ATENDIMENTO_ENCERRADO, AtendimentoService::ATENDIMENTO_ENCERRADO_CODIFICADO, 'dataFim');
            if (!$response->success) {
                throw new Exception(sprintf(_('Erro ao codificar o atendimento %s'), $atual->getId()));
            }

            $this->em()->commit();
            $this->em()->flush();
        } catch (Exception $e) {
            try {
                $this->em()->rollback();
            } catch (Exception $ex) {
            }
            $response->message = $e->getMessage();
        }

        return $response;
    }

    /**
     * Marca o atendimento como erro de triagem. E gera um novo atendimento para
     * o servico informado.
     *
     * @param Novosga\Context $context
     */
    public function redirecionar(Context $context)
    {
        $unidade = $context->getUnidade();
        $response = new JsonResponse(false);
        try {
            if (!$context->request()->isPost()) {
                throw new Exception(_('Somente via POST'));
            }
            if (!$unidade) {
                throw new Exception(_('Nenhum unidade escolhida'));
            }
            $usuario = $context->getUser();
            $servico = (int) $context->request()->post('servico');
            $atual = $this->atendimentoService->atendimentoAndamento($usuario->getId());
            if (!$atual) {
                throw new Exception(_('Nenhum atendimento em andamento'));
            }
            $redirecionado = $this->atendimentoService->redirecionar($atual, $usuario->getWrapped(), $unidade, $servico);
            if (!$redirecionado->getId()) {
                throw new Exception(sprintf(_('Erro ao redirecionar atendimento %s para o serviço %s'), $atual->getId(), $servico));
            }
            $response->success = $this->mudaStatusAtendimento($atual, array(AtendimentoService::ATENDIMENTO_INICIADO, AtendimentoService::ATENDIMENTO_ENCERRADO), AtendimentoService::ERRO_TRIAGEM, 'dataFim');
            if (!$response->success) {
                throw new Exception(sprintf(_('Erro ao mudar status do atendimento %s para encerrado'), $atual->getId()));
            }
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function info_senha(Context $context)
    {
        $response = new JsonResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $id = (int) $context->request()->get('id');
            $atendimento = $this->atendimentoService->buscaAtendimento($unidade, $id);
            if ($atendimento) {
                $response->data = $atendimento->jsonSerialize();
                $response->success = true;
            } else {
                $response->message = _('Atendimento inválido');
            }
        }

        return $response;
    }

    /**
     * Busca os atendimentos a partir do número da senha.
     *
     * @param Novosga\Context $context
     */
    public function consulta_senha(Context $context)
    {
        $response = new JsonResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $numero = $context->request()->get('numero');
            $atendimentos = $this->atendimentoService->buscaAtendimentos($unidade, $numero);
            $response->data['total'] = sizeof($atendimentos);
            foreach ($atendimentos as $atendimento) {
                $response->data['atendimentos'][] = $atendimento->jsonSerialize();
            }
            $response->success = true;
        } else {
            $response->message = _('Nenhuma unidade selecionada');
        }

        return $response;
    }
}
