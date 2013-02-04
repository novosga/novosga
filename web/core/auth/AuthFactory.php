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
        $methods = array(new DatabaseAuthentication($config));
        switch ($type) {
        case 'ldap':
            array_unshift($methods, new LdapAuthentication($config));
            break;
        }
        return $methods;
    }
    
}
