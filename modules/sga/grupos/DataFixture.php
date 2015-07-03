<?php

namespace modules\sga\grupos;

use Doctrine\ORM\EntityManager;
use Exception;
use Novosga\Model\Grupo;

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
            $em->beginTransaction();
            $em->persist($this->create(_('Raíz'), _('Grupo Raíz')));
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
    public function create($name, $description)
    {
        $grupo = new Grupo();
        $grupo->setNome($name);
        $grupo->setDescricao($description);
        $grupo->setLeft(1);
        $grupo->setRight(2);
        $grupo->setLevel(0);

        return $grupo;
    }
}
