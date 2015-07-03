<?php

namespace Novosga\Service;

use Doctrine\ORM\Query;
use Novosga\Model\Unidade;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * UnidadeService.
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UnidadeService extends MetaModelService
{
    protected function getMetaClass()
    {
        return 'Novosga\Model\UnidadeMeta';
    }

    protected function getMetaFieldname()
    {
        return 'unidade';
    }

    /**
     * Cria ou retorna um metadado da unidade caso o $value seja null (ou ocultado).
     *
     * @param Unidade $unidade
     * @param string  $name
     * @param string  $value
     *
     * @return \Novosga\Model\UnidadeMeta
     */
    public function meta(Unidade $unidade, $name, $value = null)
    {
        return $this->modelMetadata($unidade, $name, $value);
    }

    /**
     * @param Unidade|int $unidade
     *
     * @return ArrayCollection
     */
    public function lotacoes($unidade)
    {
        return $this
                ->lotacoesQuery()
                ->setParameter('unidade', $unidade)
                ->getResult();
    }

    /**
     * @param Unidade|int $unidade
     * @param string      $nomeServico
     *
     * @return ArrayCollection
     */
    public function lotacoesComServico($unidade, $nomeServico)
    {
        $where = "AND
            (
                :servico = ''
                OR
                :servico = '%%'
                OR
                EXISTS (
                    SELECT 1 FROM Novosga\Model\ServicoUsuario su1 JOIN su1.servico s1
                    WHERE su1.usuario = u AND su1.unidade = :unidade AND s1.status = 1 AND s1.nome LIKE :servico
                )
            )";

        return $this
                ->lotacoesQuery($where)
                ->setParameter('unidade', $unidade)
                ->setParameter('servico', $nomeServico)
                ->getResult()
        ;
    }

    /**
     * @param string $where
     *
     * @return Query
     */
    private function lotacoesQuery($where = '')
    {
        return $this->em
                ->createQuery("
                    SELECT
                        l
                    FROM
                        Novosga\Model\Lotacao l
                        LEFT JOIN l.usuario u
                        LEFT JOIN l.grupo g
                        LEFT JOIN l.cargo c
                    WHERE
                        g.left <= (
                            SELECT g2.left FROM Novosga\Model\Grupo g2 WHERE g2.id = (SELECT u2g.id FROM Novosga\Model\Unidade u2 INNER JOIN u2.grupo u2g WHERE u2.id = :unidade)
                        ) AND
                        g.right >= (
                            SELECT g3.right FROM Novosga\Model\Grupo g3 WHERE g3.id = (SELECT u3g.id FROM Novosga\Model\Unidade u3 INNER JOIN u3.grupo u3g WHERE u3.id = :unidade)
                        )
                        {$where}
                    ORDER BY
                        u.login
                ")
        ;
    }
}
