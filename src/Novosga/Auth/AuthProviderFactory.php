<?php

namespace Novosga\Auth;

use Novosga\Context;

/**
 * Authentication provider factory.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
interface AuthProviderFactory
{
    /**
     * Retorna uma implementação do AuthenticationProvider a partir dos parâmetros informados.
     *
     * @param \Novosga\Auth\Context $context
     * @param array                 $config
     *
     * @return AuthenticationProvider
     */
    public function create(Context $context, array $config = array());
}
