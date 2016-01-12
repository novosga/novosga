<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Classe Atendimento
 * contem o Cliente, o Servico e o Status do atendimento.
 *
 * @ ORM\Entity
 * @ ORM\Table(name="atendimentos")
 */
class Atendimento extends AbstractAtendimento
{
    /**
     * @ ORM\OneToMany(targetEntity="AtendimentoCodificado", mappedBy="atendimento")
     *
     * @var AtendimentoCodificado[]
     */
    protected $codificados;

    public function __construct()
    {
        $this->codificados = new ArrayCollection();
    }

    public function getCodificados()
    {
        return $this->codificados;
    }

    public function setCodificados(Collection $codificados)
    {
        $this->codificados = $codificados;

        return $this;
    }

    /**
     * Atendimento hash.
     *
     * @return string
     */
    public function hash()
    {
        return sha1("{$this->getId()}:{$this->getDataChegada()->getTimestamp()}");
    }

    public function jsonSerialize($minimal = false)
    {
        $arr = parent::jsonSerialize($minimal);
        $arr['hash'] = $this->hash();

        return $arr;
    }
}
