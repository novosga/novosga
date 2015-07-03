<?php

namespace Novosga\Service;

use Novosga\Model\Unidade;
use Novosga\Model\Usuario;
use Novosga\Model\Util\UsuarioSessao;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * UsuarioService.
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UsuarioService extends MetaModelService
{
    const ATTR_ATENDIMENTO_LOCAL = 'atendimento.local';
    const ATTR_ATENDIMENTO_TIPO = 'atendimento.tipo';
    const ATTR_UNIDADE = 'unidade';

    protected function getMetaClass()
    {
        return 'Novosga\Model\UsuarioMeta';
    }

    protected function getMetaFieldname()
    {
        return 'usuario';
    }

    /**
     * Cria ou retorna um metadado do usuário caso o $value seja null (ou ocultado).
     *
     * @param Usuario $usuario
     * @param string  $name
     * @param string  $value
     *
     * @return \Novosga\Model\UsuarioMeta
     */
    public function meta(Usuario $usuario, $name, $value = null)
    {
        return $this->modelMetadata($usuario, $name, $value);
    }

    /**
     * @param Usuario|int $usuario
     * @param Unidade|int $unidade
     *
     * @return ArrayCollection
     */
    public function lotacoes($usuario, $unidade)
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
                ")
                ->setParameter('usuario', $usuario)
                ->setParameter('unidade', $unidade)
        ;
    }

    /**
     * Retorna a lista de serviços que o usuário atende na determinada unidade.
     *
     * @param Usuario|UsuarioSessao|int $usuario
     * @param Unidade|int               $unidade
     *
     * @return ArrayCollection
     */
    public function servicos($usuario, $unidade)
    {
        return $this->em
                ->createQuery("
                    SELECT
                        e
                    FROM
                        Novosga\Model\ServicoUsuario e
                        JOIN
                            e.servico s
                    WHERE
                        e.usuario = :usuario AND
                        e.unidade = :unidade AND
                        s.status = 1
                    ORDER BY
                        s.nome
                ")
                ->setParameter('usuario', $usuario)
                ->setParameter('unidade', $unidade)
                ->getResult();
    }

    public function isLocalLivre($unidade, $usuario, $numero)
    {
        $count = (int) $this->em
                ->createQuery('
                    SELECT
                        COUNT(1)
                    FROM
                        Novosga\Model\UsuarioMeta e
                    WHERE
                        (e.name = :metaLocal AND e.value = :numero AND e.usuario != :usuario)
                        AND EXISTS (SELECT e2 FROM Novosga\Model\UsuarioMeta e2 WHERE e2.name = :metaUnidade AND e2.value = :unidade AND e2.usuario = e.usuario)
                ')
                ->setParameters([
                    'metaLocal' => self::ATTR_ATENDIMENTO_LOCAL,
                    'numero' => $numero,
                    'usuario' => $usuario,
                    'metaUnidade' => self::ATTR_UNIDADE,
                    'unidade' => $unidade,
                ])
                ->getSingleScalarResult()
        ;

        return $count === 0;
    }
}
