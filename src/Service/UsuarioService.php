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

use App\Entity\ServicoUsuario;
use App\Entity\Usuario;
use App\Repository\ServicoUsuarioRepository;
use App\Repository\UsuarioMetadataRepository;
use App\Repository\UsuarioRepository;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\ServicoUsuarioInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Novosga\Infrastructure\StorageInterface;
use Novosga\Service\FilaServiceInterface;
use Novosga\Service\UsuarioServiceInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

/**
 * UsuarioService.
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UsuarioService implements UsuarioServiceInterface
{
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly UsuarioRepository $usuarioRepository,
        private readonly ServicoUsuarioRepository $servicoUsuarioRepository,
        private readonly UsuarioMetadataRepository $usuarioMetadataRepository,
        private readonly HubInterface $hub,
    ) {
    }

    public function getById(int $id): ?UsuarioInterface
    {
        return $this->usuarioRepository->find($id);
    }

    public function build(): UsuarioInterface
    {
        return new Usuario();
    }

    /** {@inheritDoc} */
    public function meta(UsuarioInterface $usuario, string $name, mixed $value = null): ?EntityMetadataInterface
    {
        if ($value === null) {
            $metadata = $this->usuarioMetadataRepository->get($usuario, self::ATTR_NAMESPACE, $name);
        } else {
            $metadata = $this->usuarioMetadataRepository->set($usuario, self::ATTR_NAMESPACE, $name, $value);
        }

        return $metadata;
    }

    public function getServicoUsuario(
        UsuarioInterface $usuario,
        ServicoInterface $servico,
        UnidadeInterface $unidade
    ): ?ServicoUsuarioInterface {
        $servico = $this->storage
            ->getManager()
            ->createQueryBuilder()
            ->select('e')
            ->from(ServicoUsuario::class, 'e')
            ->join('e.servico', 's')
            ->where('e.usuario = :usuario')
            ->andWhere('e.servico = :servico')
            ->andWhere('e.unidade = :unidade')
            ->andWhere('s.ativo = TRUE')
            ->orderBy('s.nome', 'ASC')
            ->setParameter('usuario', $usuario)
            ->setParameter('servico', $servico)
            ->setParameter('unidade', $unidade)
            ->getQuery()
            ->getOneOrNullResult();

        return $servico;
    }

    /**
     * Retorna a lista de serviços que o usuário atende na determinada unidade.
     * @return ServicoUsuario[]
     */
    public function getServicosUnidade(UsuarioInterface $usuario, UnidadeInterface $unidade): array
    {
        $servicos = $this
            ->servicoUsuarioRepository
            ->createQueryBuilder('e')
            ->join('e.servico', 's')
            ->where('e.usuario = :usuario')
            ->andWhere('e.unidade = :unidade')
            ->andWhere('s.ativo = TRUE')
            ->orderBy('s.nome', 'ASC')
            ->setParameter('usuario', $usuario)
            ->setParameter('unidade', $unidade)
            ->getQuery()
            ->getResult();

        return $servicos;
    }

    public function isLocalLivre(UnidadeInterface|int $unidade, UsuarioInterface|int $usuario, string $numero): bool
    {
        $count = (int) $this->storage
            ->getManager()
            ->createQuery('
                SELECT
                    COUNT(1)
                FROM
                    App\Entity\UsuarioMeta e
                WHERE
                    (e.name = :metaLocal AND e.value = :numero AND e.usuario != :usuario)
                    AND EXISTS (
                        SELECT e2
                        FROM App\Entity\UsuarioMeta e2
                        WHERE
                            e2.name = :metaUnidade AND
                            e2.value = :unidade AND
                            e2.usuario = e.usuario
                    )
            ')
            ->setParameter('metaLocal', self::ATTR_ATENDIMENTO_LOCAL)
            ->setParameter('numero', $numero)
            ->setParameter('usuario', $usuario)
            ->setParameter('metaUnidade', self::ATTR_SESSION_UNIDADE)
            ->setParameter('unidade', $unidade)
            ->getSingleScalarResult();

        return $count === 0;
    }

    public function updateAtendente(
        UsuarioInterface $usuario,
        ?string $tipoAtendimento,
        ?int $local,
        ?int $numero
    ): void {
        if ($tipoAtendimento && in_array($tipoAtendimento, FilaServiceInterface::TIPOS_ATENDIMENTO)) {
            $this->meta($usuario, UsuarioService::ATTR_ATENDIMENTO_TIPO, $tipoAtendimento);
        }

        if ($local > 0) {
            $this->meta($usuario, UsuarioServiceInterface::ATTR_ATENDIMENTO_LOCAL, $local);
        }

        if ($numero > 0) {
            $this->meta($usuario, UsuarioServiceInterface::ATTR_ATENDIMENTO_NUM_LOCAL, $numero);
        }

        $this->hub->publish(new Update([
            "/usuarios/{$usuario->getId()}/fila",
        ], json_encode([ 'id' => $usuario->getId() ])));
    }

    public function addServicoUsuario(
        UsuarioInterface $usuario,
        ServicoInterface $servico,
        UnidadeInterface $unidade
    ): ServicoUsuario {
        $em = $this->storage->getManager();

        $servicoUsuario = new ServicoUsuario();
        $servicoUsuario->setUsuario($usuario);
        $servicoUsuario->setServico($servico);
        $servicoUsuario->setUnidade($unidade);
        $servicoUsuario->setPeso(1);

        $em->persist($servicoUsuario);
        $em->flush();

        $this->hub->publish(new Update([
            "/usuarios/{$usuario->getId()}/fila",
        ], json_encode([ 'id' => $usuario->getId() ])));

        return $servicoUsuario;
    }

    public function removeServicoUsuario(
        UsuarioInterface $usuario,
        ServicoInterface $servico,
        UnidadeInterface $unidade
    ): ?ServicoUsuario {
        $em = $this->storage->getManager();
        $servicoUsuario = $this->servicoUsuarioRepository->findOneBy([
            'usuario' => $usuario,
            'servico' => $servico,
            'unidade' => $unidade,
        ]);

        if ($servicoUsuario) {
            $em->remove($servicoUsuario);
            $em->flush();
        }

        $this->hub->publish(new Update([
            "/usuarios/{$usuario->getId()}/fila",
        ], json_encode([ 'id' => $usuario->getId() ])));

        return $servicoUsuario;
    }

    public function updateServicoUsuario(
        UsuarioInterface $usuario,
        ServicoInterface $servico,
        UnidadeInterface $unidade,
        int $peso,
    ): ?ServicoUsuario {
        $em = $this->storage->getManager();
        $servicoUsuario = $this->servicoUsuarioRepository->findOneBy([
            'usuario' => $usuario,
            'servico' => $servico,
            'unidade' => $unidade,
        ]);

        if ($servicoUsuario && $peso > 0) {
            $servicoUsuario->setPeso($peso);
            $em->persist($servicoUsuario);
            $em->flush();
        }

        $this->hub->publish(new Update([
            "/usuarios/{$usuario->getId()}/fila",
        ], json_encode([ 'id' => $usuario->getId() ])));

        return $servicoUsuario;
    }
}
