<?php

namespace Novosga\Service;

use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Local;
use AppBundle\Entity\Servico;
use AppBundle\Entity\Unidade;
use AppBundle\Entity\Usuario;
use AppBundle\Entity\Util\UsuarioSessao;

/**
 * ServicoService.
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ServicoService extends MetaModelService
{
    protected function getMetaClass()
    {
        return 'AppBundle\Entity\ServicoMeta';
    }

    protected function getMetaFieldname()
    {
        return 'servico';
    }

    /**
     * Cria ou retorna um metadado do serviço caso o $value seja null (ou ocultado).
     *
     * @param Servico $servico
     * @param string  $name
     * @param string  $value
     *
     * @return \AppBundle\Entity\ServicoMeta
     */
    public function meta(Servico $servico, $name, $value = null)
    {
        return $this->modelMetadata($servico, $name, $value);
    }

    /**
     * Retorna todos os serviços disponíveis.
     *
     * @return ArrayCollection
     */
    public function servicos()
    {
        // servicos globais
        return $this->em->createQuery('
                SELECT
                    e.id, e.nome
                FROM
                    AppBundle\Entity\Servico e
                ORDER BY
                    e.nome ASC
            ')->getResult();
    }

    /**
     * Retorna a lista de serviços ativos.
     *
     * @param Usuario|UsuarioSessao|int $usuario
     * @param Unidade|int               $unidade
     * @param string                    $where
     *
     * @return ArrayCollection
     */
    public function servicosUnidade($unidade, $where = '')
    {
        $dql = "SELECT e FROM AppBundle\Entity\ServicoUnidade e JOIN e.servico s WHERE e.unidade = :unidade ";
        if (!empty($where)) {
            $dql .= " AND $where";
        }
        $dql .= ' ORDER BY s.nome';

        return $this->em
                ->createQuery($dql)
                ->setParameter('unidade', $unidade)
                ->getResult();
    }

    /**
     * Retorna o relacionamento entre o serviço e a unidade.
     *
     * @param Unidade|int $unidade
     * @param Servico|int $servico
     *
     * @return \AppBundle\Entity\ServicoUnidade
     */
    public function servicoUnidade($unidade, $servico)
    {
        return $this->em
                ->createQuery('SELECT e FROM AppBundle\Entity\ServicoUnidade e WHERE e.servico = :servico AND e.unidade = :unidade')
                ->setParameter('servico', $servico)
                ->setParameter('unidade', $unidade)
                ->getOneOrNullResult();
    }

    /**
     * Atualiza a unidade com serviços ainda não liberados.
     *
     * @param Unidade|interger $unidade
     * @param Local|int        $local
     * @param string           $sigla
     */
    public function updateUnidade($unidade, $local, $sigla)
    {
        if ($unidade instanceof Unidade) {
            $unidade = $unidade->getId();
        }
        if ($local instanceof Local) {
            $local = $local->getId();
        }
        $uniServTableName = $this->em->getClassMetadata('AppBundle\Entity\ServicoUnidade')->getTableName();
        $servTableName = $this->em->getClassMetadata('AppBundle\Entity\Servico')->getTableName();

        // atualizando relacionamento entre unidade e servicos mestre
        $conn = $this->em->getConnection();
        $conn->executeUpdate("
            INSERT INTO $uniServTableName
                (unidade_id, servico_id, local_id, sigla, status, peso)
            SELECT
                :unidade, id, :local, :sigla, 0, peso
            FROM
                $servTableName
            WHERE
                macro_id IS NULL AND
                id NOT IN (SELECT servico_id FROM $uniServTableName WHERE unidade_id = :unidade)
        ", [
            'unidade' => $unidade,
            'local'   => $local,
            'sigla'   => $sigla,
        ]);
    }

    /**
     * Retorna os servicos do usuario na unidade.
     *
     * @param Unidade|int $unidade
     * @param Usuario|int $usuario
     *
     * @return ArrayCollection
     */
    public function servicosUsuario($unidade, $usuario)
    {
        return $this->em->createQuery("
                SELECT
                    e
                FROM
                    AppBundle\Entity\ServicoUsuario e
                    JOIN
                        e.servico s
                WHERE
                    e.usuario = :usuario AND
                    e.unidade = :unidade AND
                    s.status = 1
            ")
                ->setParameter('usuario', $usuario)
                ->setParameter('unidade', $unidade)
                ->getResult();
    }

    /**
     * Retorna os servicos que o usuario nao atende na unidade atual.
     *
     * @param Unidade|int $unidade
     * @param Usuario|int $usuario
     *
     * @return ArrayCollection
     */
    public function servicosIndisponiveis($unidade, $usuario)
    {
        return $this->em->createQuery("
                SELECT
                    e
                FROM
                    AppBundle\Entity\ServicoUnidade e
                    JOIN e.servico s
                WHERE
                    e.status = 1 AND
                    e.unidade = :unidade AND
                    s.id NOT IN (
                        SELECT s2.id FROM AppBundle\Entity\ServicoUsuario a JOIN a.servico s2 WHERE a.usuario = :usuario AND a.unidade = :unidade
                    )
            ")
                ->setParameter('usuario', $usuario)
                ->setParameter('unidade', $unidade)
                ->getResult();
    }
}
