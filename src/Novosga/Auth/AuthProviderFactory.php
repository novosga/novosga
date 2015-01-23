<?php
namespace Novosga\Auth;

use Novosga\Util\Arrays;
use Novosga\Context;

/**
 * Authentication provider factory
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AuthProviderFactory {
    
    public static function createList(Context $context, array $config = array()) {
        $methods = array();
        $type = Arrays::value($config, 'type');
        $auth = self::create($context, $type, $config);
        if ($auth) {
            $methods[] = $auth;
        }
        // sempre tenta via banco no ultimo caso
        if ($type !== 'db') {
            $methods[] = new DatabaseProvider($context->database()->createEntityManager(), $config);
        }
        return $methods;
    }
    
    public static function create(Context $context, $type, array $config = array()) {
        $config = Arrays::value($config, $type, array());
        $em = $context->database()->createEntityManager();
        switch ($type) {
        case 'ldap':
            return new LdapProvider($em, $config);
            break;
        case 'db':
            return new DatabaseProvider($em, $config);
        default:
            null;
        }
    }
    
}
