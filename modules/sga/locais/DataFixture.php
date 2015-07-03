<?php

namespace modules\sga\locais;

use Exception;
use Doctrine\ORM\EntityManager;
use Novosga\Model\Local;

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
            $em->persist($this->create(_('Guichê')));
            $em->persist($this->create(_('Sala')));
            $em->persist($this->create(_('Mesa')));
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
     *
     * @return \Novosga\Model\Local
     */
    public function create($name)
    {
        $local = new Local();
        $local->setNome($name);

        return $local;
    }
}
