<?php

namespace Novosga\Auth;

use Novosga\Context;
use Novosga\Util\Arrays;

/**
 * Default Authentication provider factory.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DefaultAuthProviderFactory implements AuthProviderFactory
{
    public function create(Context $context, array $config = [])
    {
        $type = Arrays::value($config, 'type');
        $providerConfig = Arrays::value($config, $type, []);
        $em = $context->database()->createEntityManager();
        switch ($type) {
            case 'ldap':
                return new LdapProvider($em, $providerConfig);
            default:
                return new DatabaseProvider($em, $providerConfig);
        }
    }
}
