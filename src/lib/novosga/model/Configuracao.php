<?php
namespace novosga\model;

/**
 * 
 * @Entity
 * @Table(name="config")
 */
class Configuracao extends Model {
    
    const STRING  = 1;
    const NUMERIC = 2;
    const COMPLEX = 3;
    
    /** @Id @Column(type="string", name="chave", length=20, nullable=false) */
    protected $chave;
    /** @Column(type="string", name="valor", length=20, nullable=false) */
    protected $valor;
    /** @Column(type="integer", name="tipo", nullable=false) */
    protected $tipo;
    
    // transient
    private $_valor;

    public function __construct($chave, $valor) {
        $this->setChave($chave);
        $this->setValor($valor);
    }
    
    public function getChave() {
        return $this->chave;
    }

    public function setChave($chave) {
        $this->chave = $chave;
    }

    public function getValor() {
        if (!$this->_valor) {
            $this->_valor = ($this->tipo == self::COMPLEX) ? unserialize($this->valor) : $this->valor;
        }
        return $this->_valor;
    }

    public function setValor($valor) {
        $this->_valor = $valor;
        $this->tipo = self::tipo($valor);
        $this->valor =($this->tipo == self::COMPLEX) ? serialize($valor) : $valor;
    }

    public function toString() {
        return $this->getChave() . '=' . $this->getValor();
    }
    
    private static function tipo($valor) {
        if (is_numeric($valor)) {
            return self::NUMERIC;
        }
        else if (is_string($valor)) {
            return self::STRING;
        } else {
            return self::COMPLEX;
        }
    }
    
    /**
     * Retorna a configuração a partir da chave informada
     * @param type $key
     * @return novosga\model\Configuracao
     */
    public static function get($key) {
        $em = \novosga\db\DB::getEntityManager();
        $query = $em->createQuery("SELECT e FROM novosga\model\Configuracao e WHERE e.chave = :key");
        $query->setParameter('key', $key);
        $config = $query->getOneOrNullResult();
        return $config;
    }
    
    /**
     * Cria ou atualiza uma configuração
     * @param type $key
     * @return novosga\model\Configuracao
     */
    public static function set($key, $value) {
        $em = \novosga\db\DB::getEntityManager();
        try {
            $query = $em->createQuery("SELECT e FROM novosga\model\Configuracao e WHERE e.chave = :key");
            $query->setParameter('key', $key);
            $config = $query->getSingleResult();
            $config->setValor($value);
            $em->merge($config);
            $em->flush();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $config = new Configuracao($key, $value);
            $em->persist($config);
            $em->flush();
        }
    }

}
