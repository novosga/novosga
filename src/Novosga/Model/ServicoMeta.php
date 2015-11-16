<?php

namespace Novosga\Model;

/**
 * Servico metadata.
 *
 * @Entity
 * @Table(name="serv_meta")
 */
class ServicoMeta extends Metadata
{
    /**
     * @Id
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="servico_id", referencedColumnName="id")
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
