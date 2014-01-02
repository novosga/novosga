<?php
namespace Novosga\Auth;

use \Novosga\Util\Arrays;
use \Novosga\SGAContext;

/**
 * Authentication
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AuthFactory {
    
    public static function createList(SGAContext $context, array $config = array()) {
        $methods = array();
        $type = Arrays::value($config, 'type');
        $auth = self::create($context, $type, $config);
        if ($auth) {
            $methods[] = $auth;
        }
        // sempre tenta via banco no ultimo caso
        if ($type !== 'db') {
            $methods[] = new DatabaseAuthentication($context->database()->createEntityManager(), $config);
        }
        return $methods;
    }
    
    public static function create(SGAContext $context, $type, array $config = array()) {
        $config = Arrays::value($config, $type, array());
        $em = $context->database()->createEntityManager();
        switch ($type) {
        case 'ldap':
            return new LdapAuthentication($em, $config);
            break;
        case 'db':
            return new DatabaseAuthentication($em, $config);
        default:
            null;
        }
    }
    
}
