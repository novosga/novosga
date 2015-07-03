<?php

namespace modules\sga\locais;

use Novosga\Controller\CrudController;
use Novosga\Model\Local;

/**
 * LocaisController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LocaisController extends CrudController
{
    protected function createModel()
    {
        return new Local();
    }

    protected function requiredFields()
    {
        return array('nome');
    }

    protected function search($arg)
    {
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Local e WHERE UPPER(e.nome) LIKE :arg");
        $query->setParameter('arg', $arg);

        return $query;
    }
}
