<?php

namespace Novosga\Model\Util;

use Novosga\Model\Permissao;

/**
 * Usuario utilizado para salvar na sessao. Assim evitar de salvar
 * as entidades do Doctrine.
 */
class PermissaoSessao
{
    protected $usuarioId;
    protected $modulo;
    protected $moduloId;
    protected $cargo;
    protected $cargoId;

    public function __construct($usuarioId, Permissao $permissao)
    {
        $this->usuarioId = $usuarioId;
        $this->modulo = $permissao->getModulo();
        $this->moduloId = $permissao->getModulo()->getId();
        $this->cargo = $permissao->getCargo();
        $this->cargoId = $permissao->getCargo()->getId();
    }

    public function getModuloId()
    {
        return $this->moduloId;
    }

    public function getCargoId()
    {
        return $this->cargoId;
    }

    public function __sleep()
    {
        return array('usuarioId', 'moduloId', 'cargoId');
    }
}
