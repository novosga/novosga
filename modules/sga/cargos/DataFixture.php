<?php

namespace modules\sga\cargos;

use Doctrine\ORM\EntityManager;
use Exception;
use Novosga\Model\Cargo;

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
            $em->persist($this->create(_('Administrador'), _('Administrador geral do sistema')));
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
     * @return Cargo
     */
    public function create($name, $description)
    {
        $cargo = new Cargo();
        $cargo->setNome($name);
        $cargo->setDescricao($description);
        $cargo->setLeft(1);
        $cargo->setRight(2);
        $cargo->setLevel(0);

        return $cargo;
    }
}
