<?php

namespace modules\sga\unidade;

use Doctrine\ORM\EntityManager;
use Exception;
use Novosga\Model\Grupo;
use Novosga\Model\Unidade;

/**
 * Prioridades DataFixture.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DataFixture
{
    /**
     * @param EntityManager $em
     *
     * @throws Exception
     */
    public function install(EntityManager $em)
    {
        try {
            $grupo = $em->find('Novosga\Model\Grupo', 1);
            $em->beginTransaction();
            $em->persist($this->create(_('Unidade Padrão'), _('Novo SGA'), $grupo));
            $em->commit();
            $em->flush();
        } catch (Exception $e) {
            try {
                $em->rollback();
            } catch (Exception $ex) {
            }
            throw $e;
        }
    }

    /**
     * @param string $name
     * @param string $description
     * @param int    $weight
     *
     * @return Grupo
     */
    public function create($name, $message, Grupo $grupo)
    {
        $unidade = new Unidade();
        $unidade->setCodigo('1');
        $unidade->setNome($name);
        $unidade->setGrupo($grupo);
        $unidade->setMensagemImpressao($message);
        $unidade->setStatus(1);
        $unidade->setStatusImpressao(1);

        return $unidade;
    }
}
