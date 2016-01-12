<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Servico metadata.
 *
 * @ ORM\Entity
 * @ ORM\Table(name="serv_meta")
 */
class ServicoMeta extends Metadata
{
    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Servico")
     * @ ORM\JoinColumn(name="servico_id", referencedColumnName="id")
     *
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
