<?php
namespace core\auth;

use \core\util\Arrays;

/**
 * Authentication
 *
 * @author rogeriolino
 */
class AuthFactory {
    
    public static function create(array $config = null) {
        $config = ($config) ? $config : array();
        $type = Arrays::value($config, 'type');
        $config = Arrays::value($config, $type, array());
        switch ($type) {
        case 'ldap':
            return new LdapAuthentication($config);
            break;
        case 'db':
            return new DatabaseAuthentication($config);
        default:
            null;
        }
    }
    
    public static function createList(array $config = null) {
        $methods = array();
        $auth = self::create($config);
        if ($auth) {
            $methods[] = $auth;
        }
        $methods[] = new DatabaseAuthentication($config);
        return $methods;
    }
    
}
