<?php

namespace Novosga\AttendanceBundle\Controller;

use Exception;
use Novosga\Config\AppConfig;
use Novosga\Http\Envelope;
use Novosga\Entity\Atendimento;
use Novosga\Entity\Usuario;
use Novosga\Entity\Unidade;
use Novosga\Service\AtendimentoService;
use Novosga\Service\FilaService;
use Novosga\Service\UsuarioService;
use Novosga\Util\Arrays;
use Novosga\Util\DateUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * DefaultController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DefaultController extends Controller
{
    
    public function __construct()
    {
    }

    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/", name="novosga_attendance_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $usuarioService = new UsuarioService($em);
        $atendimentoService = new AtendimentoService($em);
        
        $usuario = $this->getUser();
        $unidade = $request->getSession()->get('unidade');
        
        if (!$usuario || !$unidade) {
            return $this->redirectToRoute('home');
        }

        $local = $this->getNumeroLocalAtendimento($usuario);
        $tipo = $this->getTipoAtendimento($usuario);

        $tiposAtendimento = [
            1 => _('Todos'),
            2 => _('Convencional'),
            3 => _('Prioridade'),
        ];
        
        $atendimentoAtual = $atendimentoService->atendimentoAndamento($usuario->getId());
        
        $servicos = $usuarioService->servicos($usuario, $unidade);

        return $this->render('NovosgaAttendanceBundle:default:index.html.twig', [
            'time' => time() * 1000,
            'unidade' => $unidade,
            'atendimento' => $atendimentoAtual,
            'servicos' => $servicos,
            'servicosIndisponiveis' => [],
            'tiposAtendimento' => $tiposAtendimento,
            'local' => $local,
            'tipoAtendimento' => $tipo
        ]);
    }

    /**
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/set_local", name="novosga_attendance_setlocal")
     */
    public function setLocalAction(Request $request)
    {
        $envelope = new Envelope();
        try {
            $unidade = $request->getSession()->get('unidade');
            $usuario = $this->getUser();
            $numero = (int) $request->get('local');
            $tipo = (int) $request->get('tipo');

            AppConfig::getInstance()->hook('sga.atendimento.pre-setlocal', [$unidade, $usuario, $numero, $tipo]);

            $usuarioService = new UsuarioService($this->getDoctrine()->getManager());
            $usuarioService->meta($usuario, UsuarioService::ATTR_ATENDIMENTO_LOCAL, $numero);
            $usuarioService->meta($usuario, UsuarioService::ATTR_ATENDIMENTO_TIPO, $tipo);
            
            AppConfig::getInstance()->hook('sga.atendimento.setlocal', [$unidade, $usuario, $numero, $tipo]);
        } catch (\Exception $e) {
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }

        return $this->json($envelope);
    }

    /**
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/ajax_update", name="novosga_attendance_ajaxupdate")
     */
    public function ajaxUpdateAction(Request $request)
    {
        $envelope = new Envelope();
        $unidade = $request->getSession()->get('unidade');
        $usuario = $this->getUser();
        
        $em = $this->getDoctrine()->getManager();
        
        
        $filaService = new FilaService($em);
        $usuarioService = new UsuarioService($em);
        
        $servicos = $usuarioService->servicos($usuario, $unidade);
        
        if ($unidade && $usuario) {
            // long polling
            $maxtime = 10;
            $starttime = time();
            
            do {
                $atendimentos = $filaService->filaAtendimento($unidade, $servicos);

                // fila de atendimento do atendente atual
                $data = [
                    'atendimentos' => $atendimentos,
                    'usuario'      => [
                        'numeroLocal'     => $this->getNumeroLocalAtendimento($usuario),
                        'tipoAtendimento' => $this->getTipoAtendimento($usuario),
                    ],
                ];
                
                $envelope->setData($data);
                
                $elapsed = time() - $starttime;
            } while (empty($atendimentos) && $elapsed < $maxtime);
        }

        return $this->json($envelope);
    }

    /**
     * Chama ou rechama o próximo da fila.
     *
     * @param Novosga\Request $request
     * 
     * @Route("/chamar", name="novosga_attendance_chamar")
     * @Method("POST")
     */
    public function chamarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $envelope = new Envelope();
        
        try {
            $attempts = 5;
            $proximo = null;
            $success = false;
            $usuario = $this->getUser();
            $unidade = $em->getReference(Unidade::class, $request->getSession()->get('unidade')->getId());
            
            $filaService = new FilaService($em);
            $atendimentoService = new AtendimentoService($em);
            $usuarioService = new UsuarioService($em);
            
            if (!$usuario) {
                throw new Exception(_('Nenhum usuário na sessão'));
            }
            
            // verifica se ja esta atendendo alguem
            $atual = $atendimentoService->atendimentoAndamento($usuario->getId());
            
            // se ja existe um atendimento em andamento (chamando senha novamente)
            if ($atual) {
                $success = true;
                $proximo = $atual;
            } else {
                $local = $this->getNumeroLocalAtendimento($usuario);
                $servicos = $usuarioService->servicos($usuario, $unidade);
                
                do {
                    $atendimentos = $filaService->filaAtendimento($unidade, $servicos, 1, 1);
                    if (count($atendimentos)) {
                        $proximo = $atendimentos[0];
                        $success = $atendimentoService->chamar($proximo, $usuario, $local);
                        if (!$success) {
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
            
            $atendimentoService->chamarSenha($unidade, $proximo);
            
            $data = $proximo->jsonSerialize();
            $envelope->setData($data);
        } catch (Exception $e) {
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }

        return $this->json($envelope);
    }

    /**
     * Inicia o atendimento com o proximo da fila.
     *
     * @param Novosga\Request $request
     * 
     * @Route("/iniciar", name="novosga_attendance_iniciar")
     * @Method("POST")
     */
    public function iniciarAction(Request $request)
    {
        return $this->mudaStatusAtualResponse($request, AtendimentoService::CHAMADO_PELA_MESA, AtendimentoService::ATENDIMENTO_INICIADO, 'dataInicio');
    }

    /**
     * Marca o atendimento como nao compareceu.
     *
     * @param Novosga\Request $request
     * 
     * @Route("/nao_compareceu", name="novosga_attendance_naocompareceu")
     * @Method("POST")
     */
    public function naoCompareceuAction(Request $request)
    {
        return $this->mudaStatusAtualResponse($request, AtendimentoService::CHAMADO_PELA_MESA, AtendimentoService::NAO_COMPARECEU, 'dataFim');
    }

    /**
     * Marca o atendimento como encerrado.
     *
     * @param Novosga\Request $request
     * 
     * @Route("/encerrar", name="novosga_attendance_encerrar")
     * @Method("POST")
     */
    public function encerrarAction(Request $request)
    {
        return $this->mudaStatusAtualResponse($request, AtendimentoService::ATENDIMENTO_INICIADO, AtendimentoService::ATENDIMENTO_ENCERRADO, null);
    }

    /**
     * Marca o atendimento como encerrado e codificado.
     *
     * @param Novosga\Request $request
     * 
     * @Route("/codificar", name="novosga_attendance_codificar")
     * @Method("POST")
     */
    public function codificarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $envelope = new Envelope();
        
        try {
            $unidade = $request->getSession()->get('unidade');
            if (!$unidade) {
                throw new Exception(_('Nenhum unidade escolhida'));
            }
            $unidade = $em->getReference(Unidade::class, $unidade->getId());
            
            $usuario = $this->getUser();
            $atendimentoService = new AtendimentoService($em);
            $atual = $atendimentoService->atendimentoAndamento($usuario->getId());
            
            if (!$atual) {
                throw new Exception(_('Nenhum atendimento em andamento'));
            }
            $servicos = $request->get('servicos');
            $servicos = Arrays::valuesToInt(explode(',', $servicos));
            if (empty($servicos)) {
                throw new Exception(_('Nenhum serviço selecionado'));
            }

            $em->beginTransaction();
            foreach ($servicos as $s) {
                $codificado = new \Novosga\Entity\AtendimentoCodificado();
                $codificado->setAtendimento($atual);
                $codificado->setServico($em->find('Novosga\Entity\Servico', $s));
                $codificado->setPeso(1);
                $em->persist($codificado);
            }
            // verifica se esta encerrando e redirecionando
            $redirecionar = $request->get('redirecionar');
            if ($redirecionar) {
                $servico = $request->get('novoServico');
                $redirecionado = $atendimentoService->redirecionar($atual, $usuario, $unidade, $servico);
                if (!$redirecionado->getId()) {
                    throw new Exception(sprintf(_('Erro ao redirecionar atendimento %s para o serviço %s'), $atual->getId(), $servico));
                }
            }
            $success = $this->mudaStatusAtendimento($atual, AtendimentoService::ATENDIMENTO_ENCERRADO, AtendimentoService::ATENDIMENTO_ENCERRADO_CODIFICADO, 'dataFim');
            if (!$success) {
                throw new Exception(sprintf(_('Erro ao codificar o atendimento %s'), $atual->getId()));
            }

            $em->commit();
            $em->flush();
        } catch (Exception $e) {
            try {
                $em->rollback();
            } catch (Exception $ex) {
            }
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }

        return $this->json($envelope);
    }

    /**
     * Marca o atendimento como erro de triagem. E gera um novo atendimento para
     * o servico informado.
     *
     * @param Novosga\Request $request
     * 
     * @Route("/redirecionar", name="novosga_attendance_redirecionar")
     * @Method("POST")
     */
    public function redirecionarAction(Request $request)
    {
        $unidade = $request->getSession()->get('unidade');
        $envelope = new Envelope();
        try {
            if (!$unidade) {
                throw new Exception(_('Nenhum unidade escolhida'));
            }
            $usuario = $this->getUser();
            $servico = (int) $request->get('servico');
            $em = $this->getDoctrine()->getManager();
            $atendimentoService = new AtendimentoService($em);
            $atual = $atendimentoService->atendimentoAndamento($usuario->getId());
            
            if (!$atual) {
                throw new Exception(_('Nenhum atendimento em andamento'));
            }
            $redirecionado = $atendimentoService->redirecionar($atual, $usuario, $unidade, $servico);
            if (!$redirecionado->getId()) {
                throw new Exception(sprintf(_('Erro ao redirecionar atendimento %s para o serviço %s'), $atual->getId(), $servico));
            }
            $success = $this->mudaStatusAtendimento($atual, [AtendimentoService::ATENDIMENTO_INICIADO, AtendimentoService::ATENDIMENTO_ENCERRADO], AtendimentoService::ERRO_TRIAGEM, 'dataFim');
            if (!$success) {
                throw new Exception(sprintf(_('Erro ao mudar status do atendimento %s para encerrado'), $atual->getId()));
            }
        } catch (Exception $e) {
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }

        return $this->json($envelope);
    }

    /**
     * 
     * @param Request $request
     * @return Response
     * 
     * @Route("/info_senha", name="novosga_attendance_infosenha")
     */
    public function infoSenhaAction(Request $request)
    {
        $envelope = new Envelope();
        $unidade = $request->getSession()->get('unidade');
        
        try {
            if (!$unidade) {
                throw new Exception(_('Nenhuma unidade escolhida'));
            }
            $id = (int) $request->get('id');
            $em = $this->getDoctrine()->getManager();
            $atendimentoService = new AtendimentoService($em);
            $atendimento = $atendimentoService->buscaAtendimento($unidade, $id);
            
            if (!$atendimento) {
                throw new Exception(_('Atendimento inválido'));
            }
            
            $data = $atendimento->jsonSerialize();
            $envelope->setData($data);
            
        } catch (Exception $e) {
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }

        return $this->json($envelope);
    }

    /**
     * Busca os atendimentos a partir do número da senha.
     *
     * @param Novosga\Request $request
     * 
     * @Route("/consulta_senha", name="novosga_attendance_consultasenha")
     */
    public function consultaSenhaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $unidade = $request->getSession()->get('unidade');
        $envelope = new Envelope();
        
        try {
            if ($unidade) {
                throw new Exception(_('Nenhuma unidade selecionada'));
            }
            $numero = $request->get('numero');
            $atendimentoService = new AtendimentoService($em);
            $atendimentos = $atendimentoService->buscaAtendimentos($unidade, $numero);
            $data = [
                'total' => count($atendimentos)
            ];
            foreach ($atendimentos as $atendimento) {
                $data['atendimentos'][] = $atendimento->jsonSerialize();
            }
            $envelope->setData($data);
        } catch (Exception $e) {
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }
        
        return $this->json($envelope);
    }

    /**
     * Muda o status do atendimento atual.
     *
     * @param mixed  $statusAtual (array[int] | int)
     * @param int    $novoStatus
     * @param string $campoData
     *
     * @return Response
     */
    private function mudaStatusAtualResponse(Request $request, $statusAtual, $novoStatus, $campoData)
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->redirectToRoute('home');
        }
        
        $envelope = new Envelope();
        $em = $this->getDoctrine()->getManager();
        $atendimentoService = new AtendimentoService($em);
        $atual = $atendimentoService->atendimentoAndamento($usuario->getId());
        
        try {
            if ($atual) {
                throw new Exception(_('Nenhum atendimento disponível'));
            }
            // atualizando atendimento
            $success = $this->mudaStatusAtendimento($atual, $statusAtual, $novoStatus, $campoData);
            if ($success) {
                throw new Exception(_('Erro desconhecido'));
            }
            
            $data = $atual->jsonSerialize();
            $envelope->setData($data);
        } catch (Exception $e) {
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }
        

        return $this->json($envelope);
    }

    /**
     * @param Atendimento $atendimento
     * @param mixed       $statusAtual (array[int] | int)
     * @param int         $novoStatus
     * @param string      $campoData
     *
     * @return bool
     */
    private function mudaStatusAtendimento(Atendimento $atendimento, $statusAtual, $novoStatus, $campoData = null)
    {
        $em = $this->getDoctrine()->getManager();
        
        $cond = '';
        if ($campoData !== null) {
            $cond = ", e.$campoData = :data";
        }
        if (!is_array($statusAtual)) {
            $statusAtual = [$statusAtual];
        }
        // atualizando atendimento
        $query = $em->createQuery("
            UPDATE
                Novosga\Entity\Atendimento e
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

    private function getNumeroLocalAtendimento(Usuario $usuario)
    {
        $em = $this->getDoctrine()->getManager();
        $service = new UsuarioService($em);
        
        $numeroLocalMeta = $service->meta($usuario, 'atendimento.local');
        $numero = $numeroLocalMeta ? (int) $numeroLocalMeta->getValue() : '';
        
        return $numero;
    }
     
    private function getTipoAtendimento(Usuario $usuario)
    {
        $em = $this->getDoctrine()->getManager();
        $service = new UsuarioService($em);
        
        $tipoAtendimentoMeta = $service->meta($usuario, 'atendimento.tipo');
        $tipoAtendimento = $tipoAtendimentoMeta ? (int) $tipoAtendimentoMeta->getValue() : 1;
        
        return $tipoAtendimento;
    }
}
