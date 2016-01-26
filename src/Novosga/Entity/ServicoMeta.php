<?php

namespace Novosga\Entity;

/**
 * Servico metadata.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ServicoMeta extends Metadata
{
    /**
     * @var Servico
     */
    protected $servico;

    public function getEntity()
    {
        return $this->getServico();
    }

    public function setEntity($entity)
    {
        $this->setServico($entity);
    }

    public function getServico()
    {
        return $this->servico;
    }

    public function setServico(Servico $servico)
    {
        $this->servico = $servico;

        return $this;
    }
}
