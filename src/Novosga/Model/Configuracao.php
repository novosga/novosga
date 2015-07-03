<?php

namespace Novosga\Model;

use Doctrine\ORM\EntityManager;

/**
 * @Entity
 * @Table(name="config")
 */
class Configuracao extends Model implements \JsonSerializable
{
    const STRING = 1;
    const NUMERIC = 2;
    const COMPLEX = 3;

    /**
     * @Id @Column(type="string", name="chave", length=150, nullable=false)
     */
    protected $chave;

    /**
     * @Column(type="text", name="valor", nullable=false)
     */
    protected $valor;

    /**
     * @Column(type="integer", name="tipo", nullable=false)
     */
    protected $tipo;

    // transient
    private $_valor;

    public function __construct($chave = '', $valor = '')
    {
        $this->setChave($chave);
        $this->setValor($valor);
    }

    public function getChave()
    {
        return $this->chave;
    }

    public function setChave($chave)
    {
        $this->chave = $chave;
    }

    public function getValor()
    {
        if (!$this->_valor) {
            $this->_valor = ($this->tipo == self::COMPLEX) ? unserialize($this->valor) : $this->valor;
        }

        return $this->_valor;
    }

    public function setValor($valor)
    {
        $this->_valor = $valor;
        $this->tipo = self::tipo($valor);
        $this->valor = ($this->tipo == self::COMPLEX) ? serialize($valor) : $valor;
    }

    public function toString()
    {
        return $this->getChave().'='.$this->getValor();
    }

    private static function tipo($valor)
    {
        if (is_numeric($valor)) {
            return self::NUMERIC;
        } elseif (is_string($valor)) {
            return self::STRING;
        } else {
            return self::COMPLEX;
        }
    }

    /**
     * Retorna a configuração a partir da chave informada.
     *
     * @param type $key
     *
     * @return Novosga\Model\Configuracao
     */
    public static function get(EntityManager $em,  $key)
    {
        try {
            $query = $em->createQuery("SELECT e FROM Novosga\Model\Configuracao e WHERE e.chave = :key");
            $query->setParameter('key', $key);

            return $query->getOneOrNullResult();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Cria ou atualiza uma configuração.
     *
     * @param string $key
     *
     * @return Novosga\Model\Configuracao
     */
    public static function set(EntityManager $em, $key, $value)
    {
        try {
            $query = $em->createQuery("SELECT e FROM Novosga\Model\Configuracao e WHERE e.chave = :key");
            $query->setParameter('key', $key);
            $config = $query->getSingleResult();
            $config->setValor($value);
            $em->merge($config);
            $em->flush();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $config = new self($key, $value);
            $em->persist($config);
            $em->flush();
        }
    }

    /**
     * Apaga uma configuração.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function del(EntityManager $em, $key)
    {
        $query = $em->createQuery("DELETE FROM Novosga\Model\Configuracao e WHERE e.chave = :key");
        $query->setParameter('key', $key);

        return $query->execute();
    }

    public function jsonSerialize()
    {
        return array(
            'chave' => $this->getChave(),
            'valor' => $this->getValor(),
        );
    }
}
