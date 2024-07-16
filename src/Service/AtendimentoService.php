<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use DateTime;
use Exception;
use App\Entity\Atendimento;
use App\Entity\AtendimentoCodificado;
use App\Entity\Cliente;
use App\Event\EventDispatcherInterface;
use App\Entity\Lotacao;
use App\Entity\PainelSenha;
use App\Entity\Prioridade;
use App\Entity\Servico;
use App\Entity\ServicoUnidade;
use App\Entity\Unidade;
use App\Entity\Usuario;
use App\Infrastructure\StorageInterface;
use App\Repository\AtendimentoMetadataRepository;
use App\Repository\AtendimentoRepository;
use App\Repository\ServicoUnidadeRepository;
use Novosga\Entity\AgendamentoInterface;
use Novosga\Entity\AtendimentoInterface;
use Novosga\Entity\ClienteInterface;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\LocalInterface;
use Novosga\Entity\PrioridadeInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Novosga\Service\AtendimentoServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * AtendimentoService.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AtendimentoService implements AtendimentoServiceInterface
{   
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
        private readonly HubInterface $hub,
        private readonly FilaService $filaService,
        private readonly AtendimentoRepository $atendimentoRepository,
        private readonly AtendimentoMetadataRepository $atendimentoMetaRepository,
        private readonly ServicoUnidadeRepository $servicoUnidadeRepository,
    ) {
    }

    public function getById(int $id): ?AtendimentoInterface
    {
        return $this->atendimentoRepository->find($id);
    }

    /** @return array<string,string> */
    public function getSituacoes(): array
    {
        return [
            self::SENHA_EMITIDA          => $this->translator->trans('ticket.status.generated'),
            self::CHAMADO_PELA_MESA      => $this->translator->trans('ticket.status.called'),
            self::ATENDIMENTO_INICIADO   => $this->translator->trans('ticket.status.started'),
            self::ATENDIMENTO_ENCERRADO  => $this->translator->trans('ticket.status.finished'),
            self::NAO_COMPARECEU         => $this->translator->trans('ticket.status.no_show'),
            self::SENHA_CANCELADA        => $this->translator->trans('ticket.status.cancelled'),
            self::ERRO_TRIAGEM           => $this->translator->trans('ticket.status.error'),
        ];
    }

    /** @return array<string,string> */
    public function getResolucoes(): array
    {
        return [
            self::RESOLVIDO  => $this->translator->trans('ticket.resolution.solved'),
            self::PENDENTE   => $this->translator->trans('ticket.resolution.pending'),
        ];
    }

    public function getNomeSituacao(string $status): string
    {
        $arr = $this->getSituacoes();

        return $arr[$status] ?? '';
    }

    /** {@inheritDoc} */
    public function meta(AtendimentoInterface $atendimento, string $namespace, string $name, ?string $value = null): EntityMetadataInterface
    {
        if ($value === null) {
            $metadata = $this
                ->atendimentoMetaRepository
                ->get($atendimento, $namespace, $name);
        } else {
            $metadata = $this
                ->atendimentoMetaRepository
                ->set($atendimento, $namespace, $name, $value);
        }

        return $metadata;
    }

    /** {@inheritDoc} */
    public function chamarSenha(UnidadeInterface $unidade, AtendimentoInterface $atendimento): void
    {
        $servico = $atendimento->getServico();
        $su = $this->servicoUnidadeRepository->get($unidade, $servico);

        $senha = new PainelSenha();
        $senha->setUnidade($unidade);
        $senha->setServico($servico);
        $senha->setNumeroSenha($atendimento->getSenha()->getNumero());
        $senha->setSiglaSenha($atendimento->getSenha()->getSigla());
        $senha->setMensagem($su->getMensagem() . '');
        // local
        $senha->setLocal($atendimento->getLocal()->getNome());
        $senha->setNumeroLocal($atendimento->getNumeroLocal());
        // prioridade
        $senha->setPeso($atendimento->getPrioridade()->getPeso());
        $senha->setPrioridade($atendimento->getPrioridade()->getNome());
        // cliente
        if ($atendimento->getCliente()) {
            $senha->setNomeCliente($atendimento->getCliente()->getNome());
            $senha->setDocumentoCliente($atendimento->getCliente()->getDocumento());
        }

        $this->dispatcher->createAndDispatch('panel.pre-call', [$atendimento, $senha], true);

        $em = $this->storage->getManager();
        $em->persist($senha);
        $em->flush();

        $this->dispatcher->createAndDispatch('panel.call', [$atendimento, $senha], true);

        $this->hub->publish(new Update([
            "/paineis",
            "/unidades/{$unidade->getId()}/painel",
        ], json_encode([ 'id' => $atendimento->getId() ])));
    }

    /** {@inheritDoc} */
    public function chamarProximo(
        UnidadeInterface $unidade,
        UsuarioInterface $usuario,
        LocalInterface $local,
        string $tipo,
        array $servicos,
        int $numeroLocal,
    ): ?AtendimentoInterface {
        $attempts = 5;
        $success = false;
        $proximo = null;
        do {
            $atendimentos = $this
                ->filaService
                ->getFilaAtendimento($unidade, $usuario, $servicos, $tipo, 1);
            if (empty($atendimentos)) {
                // nao existe proximo
                break;
            }
            $proximo = $atendimentos[0];
            $success = $this->chamarAtendimento($proximo, $usuario, $local, $numeroLocal);
            if (!$success) {
                usleep(100);
            }
            --$attempts;
        } while (!$success && $attempts > 0);

        return $proximo;
    }

    /** {@inheritDoc} */
    public function chamarAtendimento(
        AtendimentoInterface $atendimento,
        UsuarioInterface $usuario,
        LocalInterface $local,
        int $numeroLocal
    ): bool {
        $this->dispatcher->createAndDispatch('attending.pre-call', [$atendimento, $usuario, $local, $numeroLocal], true);

        $atendimento
            ->setUsuario($usuario)
            ->setLocal($local)
            ->setNumeroLocal($numeroLocal)
            ->setStatus(self::CHAMADO_PELA_MESA)
            ->setDataChamada(new DateTime());

        $tempoEspera = $atendimento->getDataChamada()->diff($atendimento->getDataChegada());
        $atendimento->setTempoEspera($tempoEspera);

        try {
            $this->storage->chamar($atendimento);

            $this->dispatcher->createAndDispatch('attending.call', [$atendimento, $usuario], true);
        } catch (Exception $e) {
            return false;
        }

        $this->hub->publish(new Update([
            "/atendimentos/{$atendimento->getId()}",
            "/unidades/{$atendimento->getUnidade()->getId()}/fila",
            "/usuarios/{$usuario->getId()}/fila",
        ], json_encode([ 'id' => $atendimento->getId() ])));

        return true;
    }

    /**
     * Move os registros da tabela atendimento para a tabela de historico de atendimentos.
     * Se a unidade não for informada, será acumulado serviços de todas as unidades.
     *
     * @param array<string,mixed> $ctx
     * @throws Exception
     */
    public function acumularAtendimentos(UnidadeInterface $unidade, array $ctx = []): void
    {
        $this->dispatcher->createAndDispatch('attending.pre-reset', $unidade, true);

        $this->storage->acumularAtendimentos($unidade, $ctx);

        $this->dispatcher->createAndDispatch('attending.reset', $unidade, true);

        if ($unidade) {
            $this->hub->publish(new Update([
                "/unidades/{$unidade->getId()}/fila",
            ], json_encode([ 'id' => $unidade->getId() ])));
        } else {
            $this->hub->publish(new Update([
                "/fila",
            ], json_encode([])));
        }
    }

    /** {@inheritDoc} */
    public function buscaAtendimento(UnidadeInterface $unidade, int $id): ?AtendimentoInterface
    {
        $atendimento = $this
            ->atendimentoRepository
            ->createQueryBuilder('e')
            ->where('e.id = :id')
            ->andWhere('e.unidade = :unidade')
            ->setParameter('id', $id)
            ->setParameter('unidade', $unidade->getId())
            ->getQuery()
            ->getOneOrNullResult();

        return $atendimento;
    }

    /** {@inheritDoc} */
    public function buscaAtendimentos(UnidadeInterface $unidade, string $senha): array
    {
        $i = 0;
        $sigla = '';
        do {
            $char = substr($senha, $i, 1);
            $isAlpha = ctype_alpha($char);
            if ($isAlpha) {
                $sigla .= strtoupper($char);
            }
            $i++;
        } while ($i < strlen($senha) && $isAlpha);

        $numero = (int) substr($senha, $i - 1);

        $qb = $this
            ->atendimentoRepository
            ->createQueryBuilder('e')
            ->select([
                'e', 's', 'ut', 'u'
            ])
            ->join('e.servico', 's')
            ->join('e.usuarioTriagem', 'ut')
            ->leftJoin('e.usuario', 'u')
            ->where(':numero = 0 OR e.senha.numero = :numero')
            ->andWhere('e.unidade = :unidade')
            ->orderBy('e.id', 'ASC')
            ->setParameter('numero', $numero)
            ->setParameter('unidade', $unidade->getId());
        
        if (!empty($sigla)) {
            $qb
                ->andWhere('e.senha.sigla = :sigla')
                ->setParameter('sigla', $sigla);
        }

        $rs = $qb
            ->getQuery()
            ->getResult();

        return $rs;
    }

    /**
     * Retorna o atendimento em andamento do usuario informado.
     */
    public function getAtendimentoAndamento(
        int|UsuarioInterface $usuario,
        int|UnidadeInterface|null $unidade
    ): ?AtendimentoInterface {
        $status = [
            self::CHAMADO_PELA_MESA,
            self::ATENDIMENTO_INICIADO,
        ];
        try {
            $qb = $this
                ->atendimentoRepository
                ->createQueryBuilder('e')
                ->where('e.usuario = :usuario')
                ->andWhere('e.status IN (:status)')
                ->setParameter('usuario', $usuario)
                ->setParameter('status', $status);

            if ($unidade) {
                $qb
                    ->andWhere('e.unidade = :unidade')
                    ->setParameter('unidade', $unidade);
            }

            return $qb
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Doctrine\ORM\NonUniqueResultException $e) {
            /*
             * caso tenha mais de um atendimento preso ao usuario,
             * libera os atendimentos e retorna null para o atendente chamar de novo.
             * BUG #213
             */
            $this
                ->atendimentoRepository
                ->createQueryBuilder('e')
                ->update()
                ->set('e.status', ':status')
                ->set('e.usuario', ':null')
                ->where('e.usuario = :usuario')
                ->andWhere('e.status IN (:status)')
                ->setParameter('status', 1)
                ->setParameter('null', null)
                ->setParameter('usuario', $usuario)
                ->setParameter('status', $status)
                ->getQuery()
                ->execute();

            return null;
        }
    }

    /**
     * Gera um novo atendimento.
     */
    public function distribuiSenha(
        int|UnidadeInterface $unidade,
        int|UsuarioInterface $usuario,
        int|ServicoInterface $servico,
        int|PrioridadeInterface $prioridade,
        ClienteInterface $cliente = null,
        AgendamentoInterface $agendamento = null,
    ): AtendimentoInterface {
        $om = $this->storage->getManager();
        
        // verificando a unidade
        if (!($unidade instanceof Unidade)) {
            $unidade = $om->find(Unidade::class, $unidade);
        }
        if (!$unidade) {
            $error = $this->translator->trans('error.invalid_unity');
            throw new Exception($error);
        }
        // verificando o usuario na sessao
        if (!($usuario instanceof Usuario)) {
            $usuario = $om->find(Usuario::class, $usuario);
        }
        if (!$usuario) {
            $error = $this->translator->trans('error.invalid_user');
            throw new Exception($error);
        }
        // verificando o servico
        if (!($servico instanceof Servico)) {
            $servico = $om->find(Servico::class, $servico);
        }
        if (!$servico) {
            $error = $this->translator->trans('error.invalid_service');
            throw new Exception($error);
        }
        // verificando a prioridade
        if (!($prioridade instanceof Prioridade)) {
            $prioridade = $om->find(Prioridade::class, $prioridade);
        }
        if (!$prioridade || !$prioridade->isAtivo()) {
            $error = $this->translator->trans('error.invalid_priority');
            throw new Exception($error);
        }
        
        if (!$usuario->isAdmin()) {
            $lotacao = $om
                ->getRepository(Lotacao::class)
                ->findOneBy([
                    'usuario' => $usuario,
                    'unidade' => $unidade,
                ]);

            if (!$lotacao) {
                $error = $this->translator->trans('error.user_unity_ticket_permission');
                throw new Exception($error);
            }
        }
        
        $su = $this->checkServicoUnidade($unidade, $servico);

        if (
            ($su->getTipo() === 2 && $prioridade->getPeso() > 0) ||
            ($su->getTipo() === 3 && $prioridade->getPeso() === 0)
         ) {
            $error = $this->translator->trans('error.invalid_attendance_priority');
            throw new Exception($error);
        }

        if ($su->getMaximo() > 0) {
            $count = $this->atendimentoRepository->count([
                'servico' => $servico,
                'unidade' => $unidade,
            ]);

            if ($count >= $su->getMaximo()) {
                $error = $this->translator->trans('error.maximum_attendance_reached');
                throw new Exception($error);
            }
        }
        
        $atendimento = new Atendimento();
        $atendimento
            ->setServico($servico)
            ->setUnidade($unidade)
            ->setPrioridade($prioridade)
            ->setUsuarioTriagem($usuario)
            ->setStatus(self::SENHA_EMITIDA)
            ->setLocal(null)
            ->setNumeroLocal(null);

        $atendimento->getSenha()->setSigla($su->getSigla());
        
        if ($agendamento) {
            $data = $agendamento->getData()->format('Y-m-d');
            $hora = $agendamento->getHora()->format('H:i');
            $dtAge = DateTime::createFromFormat('Y-m-d H:i', "{$data} {$hora}");
            $atendimento->setDataAgendamento($dtAge);
        }
        
        $clienteValido = $this->getClienteValido($cliente);
        if ($clienteValido) {
            $atendimento->setCliente($clienteValido);
        }

        $this->dispatcher->createAndDispatch('attending.pre-create', [$atendimento], true);
        
        try {
            $this->storage->distribui($atendimento, $agendamento);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
        
        if (!$atendimento->getId()) {
            $error = $this->translator->trans('error.new_ticket');
            $this->logger->error($error);
            throw new Exception($error);
        }

        $this->hub->publish(new Update([
            '/atendimentos',
            "/unidades/{$unidade->getId()}/fila",
        ], json_encode([ 'id' => $atendimento->getId() ])));
        
        return $atendimento;
    }

    /** {@inheritDoc} */
    public function iniciarAtendimento(AtendimentoInterface $atendimento, UsuarioInterface $usuario): void
    {
        $status = $atendimento->getStatus();
        
        if (!in_array($status, [ self::CHAMADO_PELA_MESA ])) {
            throw new Exception('Não pode iniciar esse atendimento.');
        }
        
        $atendimento
            ->setStatus(self::ATENDIMENTO_INICIADO)
            ->setDataInicio(new DateTime())
            ->setUsuario($usuario);
    
        $tempoDeslocamento = $atendimento->getDataInicio()->diff($atendimento->getDataChamada());

        $atendimento->setTempoDeslocamento($tempoDeslocamento);

        $em = $this->storage->getManager();
        $em->persist($atendimento);
        $em->flush();

        $this->hub->publish(new Update([
            "/atendimentos/{$atendimento->getId()}",
            "/unidades/{$atendimento->getUnidade()->getId()}/fila",
            "/usuarios/{$usuario->getId()}/fila",
        ], json_encode([ 'id' => $atendimento->getId() ])));
    }

    /** {@inheritDoc} */
    public function naoCompareceu(AtendimentoInterface $atendimento, UsuarioInterface $usuario): void
    {
        $status = $atendimento->getStatus();
        
        if (!in_array($status, [ self::CHAMADO_PELA_MESA ])) {
            throw new Exception('Não pode iniciar esse atendimento.');
        }
        
        $atendimento->setDataFim(new DateTime());
        $atendimento->setStatus(self::NAO_COMPARECEU);
        $atendimento->setUsuario($usuario);
        
        $tempoPermanencia  = $atendimento->getDataFim()->diff($atendimento->getDataChegada());
        $tempoAtendimento  = new \DateInterval('P0M');
        $tempoDeslocamento = new \DateInterval('P0M');
        
        $atendimento->setTempoPermanencia($tempoPermanencia);
        $atendimento->setTempoAtendimento($tempoAtendimento);
        $atendimento->setTempoDeslocamento($tempoDeslocamento);
        
        $em = $this->storage->getManager();
        $em->persist($atendimento);
        $em->flush();

        $this->hub->publish(new Update([
            "/atendimentos/{$atendimento->getId()}",
            "/unidades/{$atendimento->getUnidade()->getId()}/fila",
            "/usuarios/{$usuario->getId()}/fila",
        ], json_encode([ 'id' => $atendimento->getId() ])));
    }

    /** {@inheritDoc} */
    public function redirecionar(
        AtendimentoInterface $atendimento,
        UnidadeInterface $unidade,
        ServicoInterface|int $servico,
        UsuarioInterface|int $novoAtendente = null,
    ): AtendimentoInterface {
        $status = $atendimento->getStatus();

        if (!in_array($status, [ self::ATENDIMENTO_INICIADO, self::ATENDIMENTO_ENCERRADO ])) {
            throw new Exception('Não pode redirecionar esse atendimento.');
        }

        if (is_int($servico)) {
            $servico = $this
                ->storage
                ->getRepository(Servico::class)
                ->find($servico);
        }

        if (is_int($novoAtendente)) {
            $$novoAtendente = $this
                ->storage
                ->getRepository(Usuario::class)
                ->find($$novoAtendente);
        }
        
        $this->dispatcher->createAndDispatch('attending.pre-redirect', [$atendimento, $unidade, $servico, $usuario], true);
        
        $atendimento->setStatus(self::ERRO_TRIAGEM);
        $atendimento->setDataFim(new DateTime());
        
        $tempoPermanencia = $atendimento->getDataFim()->diff($atendimento->getDataChegada());
        $tempoAtendimento = new \DateInterval('P0M');
        
        $atendimento->setTempoPermanencia($tempoPermanencia);
        $atendimento->setTempoAtendimento($tempoAtendimento);
        
        $novo = $this->copyToRedirect($atendimento, $unidade, $servico, $usuario);

        $em = $this->storage->getManager();
        $em->persist($atendimento);
        $em->persist($novo);
        $em->flush();

        $this->dispatcher->createAndDispatch('attending.redirect', [$atendimento, $novo], true);

        $this->hub->publish(new Update([
            "/atendimentos/{$atendimento->getId()}",
            "/atendimentos/{$novo->getId()}",
            "/unidades/{$unidade->getId()}/fila",
        ], json_encode([ 'originalId' => $atendimento->getId(), 'novoId' => $novo->getId() ])));

        return $novo;
    }

    /** {@inheritDoc} */
    public function transferir(
        AtendimentoInterface $atendimento,
        UnidadeInterface $unidade,
        ServicoInterface|int $novoServico,
        PrioridadeInterface|int $novaPrioridade
    ): bool {
        $this->dispatcher->createAndDispatch('attending.pre-transfer', [ $atendimento, $unidade, $novoServico, $novaPrioridade ], true);

        // transfere apenas se a data fim for nula (nao finalizados)
        $success = $this
            ->atendimentoRepository
            ->createQueryBuilder('e')
            ->update()
            ->set('e.servico', ':servico')
            ->set('e.prioridade', ':prioridade')
            ->where('e.id = :id')
            ->andWhere('e.unidade = :unidade')
            ->andWhere('e.dataFim IS NULL')
            ->setParameter('servico', $novoServico)
            ->setParameter('prioridade', $novaPrioridade)
            ->setParameter('id', $atendimento)
            ->setParameter('unidade', $unidade)
            ->getQuery()
            ->execute() > 0;

        if ($success) {
            $em = $this->storage->getManager();
            $em->refresh($atendimento);

            $this->dispatcher->createAndDispatch('attending.transfer', [$atendimento], true);
        }

        $this->hub->publish(new Update([
            "/atendimentos/{$atendimento->getId()}",
            "/unidades/{$unidade->getId()}/fila",
        ], json_encode([ 'id' => $atendimento->getId() ])));

        return $success;
    }

    /** {@inheritDoc} */
    public function cancelar(AtendimentoInterface $atendimento): void
    {
        if ($atendimento->getDataFim() !== null) {
            throw new Exception('Erro ao tentar cancelar um serviço já encerrado.');
        }
        
        $this->dispatcher->createAndDispatch('attending.pre-cancel', $atendimento, true);
        
        $now = new DateTime();
        $atendimento->setDataFim($now);
        
        if ($atendimento->getDataChegada()) {
            $tempoPermanencia = $atendimento->getDataFim()->diff($atendimento->getDataChegada());
        } else {
            $tempoPermanencia = $atendimento->getDataFim()->diff($now);
        }
        
        if ($atendimento->getDataInicio()) {
            $tempoAtendimento = $atendimento->getDataFim()->diff($atendimento->getDataInicio());
        } else {
            $tempoAtendimento = null;
        }
        
        $atendimento->setTempoPermanencia($tempoPermanencia);
        $atendimento->setTempoAtendimento($tempoAtendimento);
        $atendimento->setStatus(self::SENHA_CANCELADA);

        $em = $this->storage->getManager();
        $em->persist($atendimento);
        $em->flush();

        $this->dispatcher->createAndDispatch('attending.cancel', $atendimento, true);

        $this->hub->publish(new Update([
            "/atendimentos/{$atendimento->getId()}",
            "/unidades/{$atendimento->getUnidade()->getId()}/fila",
        ], json_encode([ 'id' => $atendimento->getId() ])));
    }

    /** {@inheritDoc} */
    public function reativar(AtendimentoInterface $atendimento, UnidadeInterface $unidade): bool
    {
        $this->dispatcher->createAndDispatch('attending.pre-reactivate', $atendimento, true);

        // reativa apenas se estiver finalizada (data fim diferente de nulo)
        $success = $this
            ->atendimentoRepository
            ->createQueryBuilder('e')
            ->update()
            ->set('e.status', ':status')
            ->set('e.dataFim', ':data')
            ->set('e.usuario', ':usuario')
            ->where('e.id = :id')
            ->andWhere('e.unidade = :unidade')
            ->andWhere('e.status IN (:statuses)')
            ->setParameter('status', self::SENHA_EMITIDA)
            ->setParameter('data', null)
            ->setParameter('usuario', null)
            ->setParameter('id', $atendimento)
            ->setParameter('unidade', $unidade)
            ->setParameter('statuses', [self::SENHA_CANCELADA, self::NAO_COMPARECEU])
            ->getQuery()
            ->execute() > 0;

        if ($success) {
            $em = $this->storage->getManager();
            $em->refresh($atendimento);

            $this->dispatcher->createAndDispatch('attending.reactivate', $atendimento, true);
        }

        $this->hub->publish(new Update([
            "/atendimentos/{$atendimento->getId()}",
            "/unidades/{$unidade->getId()}/fila",
        ], json_encode([ 'id' => $atendimento->getId() ])));

        return $success;
    }
    
    /** {@inheritDoc} */
    public function encerrar(
        AtendimentoInterface $atendimento,
        UnidadeInterface $unidade,
        array $servicosRealizados,
        ServicoInterface|int $servicoRedirecionado = null,
        UsuarioInterface|int $novoUsuario = null,
    ): void {
        if ($atendimento->getStatus() !== AtendimentoService::ATENDIMENTO_INICIADO) {
            throw new Exception(
                sprintf(
                    'Erro ao tentar encerrar um atendimento nao iniciado (%s)',
                    $atendimento->getId()
                )
            );
        }

        $this->dispatcher->createAndDispatch('attending.pre-finish', $atendimento, true);

        $executados = [];
        $servicoRepository = $this->storage->getRepository(Servico::class);

        foreach ($servicosRealizados as $s) {
            if ($s instanceof Servico) {
                $servico = $s;
            } else {
                $servico = $servicoRepository->find($s);
            }

            if (!$servico) {
                $error = $this->translator->trans('error.invalid_service');
                throw new Exception($error);
            }

            $executado = new AtendimentoCodificado();
            $executado->setAtendimento($atendimento);
            $executado->setServico($servico);
            $executado->setPeso(1);
            $executados[] = $executado;
        }

        $novoAtendimento = null;

        // verifica se esta encerrando e redirecionando
        if ($servicoRedirecionado) {
            $novoAtendimento = $this->copyToRedirect($atendimento, $unidade, $servicoRedirecionado, $novoUsuario);
        }

        $atendimento->setDataFim(new DateTime);
        $atendimento->setStatus(AtendimentoService::ATENDIMENTO_ENCERRADO);

        $tempoPermanencia = $atendimento->getDataFim()->diff($atendimento->getDataChegada());
        $tempoAtendimento = $atendimento->getDataFim()->diff($atendimento->getDataInicio());

        $atendimento->setTempoPermanencia($tempoPermanencia);
        $atendimento->setTempoAtendimento($tempoAtendimento);

        $this->storage->encerrar($atendimento, $executados, $novoAtendimento);

        $this->hub->publish(new Update([
            "/atendimentos/{$atendimento->getId()}",
            "/unidades/{$unidade->getId()}/fila",
        ], json_encode([ 'id' => $atendimento->getId() ])));

        $this->dispatcher->createAndDispatch('attending.finish', $atendimento, true);
    }
    
    public function alteraStatusAtendimentoUsuario(Usuario $usuario, $novoStatus)
    {
        $atual = $this->getAtendimentoAndamento($usuario, null);

        if (!$atual) {
            $error = $this->translator->trans('error.no_servicing_available');
            throw new Exception($error);
        }
            
        $campoData = null;
        
        switch ($novoStatus) {
            case AtendimentoService::ATENDIMENTO_INICIADO:
                $statusAtual = [ AtendimentoService::CHAMADO_PELA_MESA ];
                $campoData   = 'dataInicio';
                break;
            case AtendimentoService::NAO_COMPARECEU:
                $statusAtual = [ AtendimentoService::CHAMADO_PELA_MESA ];
                $campoData   = 'dataFim';
                break;
            case AtendimentoService::ATENDIMENTO_ENCERRADO:
                $statusAtual = [ AtendimentoService::ATENDIMENTO_INICIADO ];
                $campoData   = 'dataFim';
                break;
            case AtendimentoService::ERRO_TRIAGEM:
                $statusAtual = [
                    AtendimentoService::ATENDIMENTO_INICIADO,
                    AtendimentoService::ATENDIMENTO_ENCERRADO,
                ];
                $campoData   = 'dataFim';
                break;
            default:
                throw new Exception('Novo status inválido.');
        }

        if (!is_array($statusAtual)) {
            $statusAtual = [$statusAtual];
        }

        $data = (new DateTime())->format('Y-m-d H:i:s');

        $qb = $this
            ->atendimentoRepository
            ->createQueryBuilder('e')
            ->update()
            ->set('e.status', ':novoStatus');

        if ($campoData !== null) {
            $qb
                ->set("e.{$campoData}", ':data')
                ->setParameter('data', $data);
        }

        $qb
            ->where('e.id = :id')
            ->andWhere('e.status IN (:statusAtual)')
            ->setParameter('novoStatus', $novoStatus)
            ->setParameter('id', $atual->getId())
            ->setParameter('statusAtual', $statusAtual);

        $success = $qb
            ->getQuery()
            ->execute() > 0;

        if (!$success) {
            $error = $this->translator->trans('error.change_status');
            throw new Exception($error);
        }

        $atual->setStatus($novoStatus);

        return $atual;
    }

    public function checkServicoUnidade(Unidade $unidade, Servico $servico): ServicoUnidade
    {
        // verificando se o servico esta disponivel na unidade
        $su = $this
            ->storage
            ->getRepository(ServicoUnidade::class)
            ->get($unidade, $servico);
        
        if (!$su) {
            $error = $this->translator->trans('error.service_unity_invalid');
            throw new Exception($error);
        }
        
        if (!$su->isAtivo()) {
            $error = $this->translator->trans('error.service_unity_inactive');
            throw new Exception($error);
        }
        
        return $su;
    }

    public function getClienteValido(ClienteInterface $cliente): ?ClienteInterface
    {
        // verificando se o cliente ja existe
        if ($cliente) {
            $clienteExistente = null;
            $clienteRepository = $this->storage->getRepository(Cliente::class);

            if ($cliente->getId()) {
                $clienteExistente = $clienteRepository->find($cliente->getId());
            }

            if (!$clienteExistente && $cliente->getEmail()) {
                $clienteExistente = $clienteRepository->findOneBy(['email' => $cliente->getEmail()]);
            }

            if (!$clienteExistente && $cliente->getDocumento()) {
                $clienteExistente = $clienteRepository->findOneBy(['documento' => $cliente->getDocumento()]);
            }

            if ($clienteExistente) {
                $cliente = $clienteExistente;
            }

            // evita gerar cliente sem nome e/ou documento
            if (!$cliente->getDocumento() || !$cliente->getNome()) {
                $cliente = null;
            }
        }
        
        return $cliente;
    }
    
    /** {@inheritDoc} */
    public function limparDados(UnidadeInterface $unidade): void
    {
        $this->storage->apagarDadosAtendimento($unidade);

        $this->hub->publish(new Update([
            "/unidades/{$unidade->getId()}/fila",
        ], json_encode([ 'id' => $unidade->getId() ])));
    }

    private function copyToRedirect(
        AtendimentoInterface $atendimento,
        UnidadeInterface $unidade,
        ServicoInterface $servico,
        UsuarioInterface $novoAtendente = null,
    ): AtendimentoInterface {
        // copiando a senha do atendimento atual
        $novo = new Atendimento();
        $novo
            ->setLocal(null)
            ->setNumeroLocal(null)
            ->setServico($servico)
            ->setUnidade($unidade)
            ->setPai($atendimento)
            ->setDataChegada(new DateTime())
            ->setStatus(self::SENHA_EMITIDA)
            ->setUsuario($novoAtendente)
            ->setUsuarioTriagem($atendimento->getUsuario())
            ->setPrioridade($atendimento->getPrioridade());

        $novo
            ->getSenha()
            ->setSigla($atendimento->getSenha()->getSigla())
            ->setNumero($atendimento->getSenha()->getNumero());

        if ($atendimento->getCliente()) {
            $novo->setCliente($atendimento->getCliente());
        }

        return $novo;
    }
}
