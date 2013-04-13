<?php
namespace core\model;

/**
 * Model Sequencial (id numérico)
 * 
 * @author rogeriolino
 * 
 * @MappedSuperClass
 */
abstract class SequencialModel extends Model {

    /** @Id @GeneratedValue @Column(type="integer", name="id", nullable=false) */
    protected $id = 0;

    /**
     * Define o id da entidade
     * @param int $id
     */
    public function setId($id) {
        $_id = (int) $id;
        if ($_id > 0) {
            $this->id = $_id;
        } else  {
            $msg = _('Erro ao definir id da entidade ("%s"): Deve ser maior que zero. Passado %s');
            throw new Exception(sprintf($msg, get_class($this), $id));
        }
    }

    /**
     * Retorna o id da entidade
     * @return int
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * @return String
     */
    public function toString() {
        return get_class($this) . '[id=' . $this->id . ']';
    }
    
}
