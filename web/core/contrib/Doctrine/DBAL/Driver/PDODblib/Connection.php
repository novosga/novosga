<?php
namespace Doctrine\DBAL\Driver\PDODblib;

use PDO;

/**
 * PDODblib Connection implementation.
 *
 * @since 2.0
 */
class Connection extends \Doctrine\DBAL\Driver\PDOConnection implements \Doctrine\DBAL\Driver\Connection
{
    
    public function __construct($dsn, $user = null, $password = null, array $options = null) {
        parent::__construct($dsn, $user, $password, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('Doctrine\DBAL\Driver\PDODblib\Statement', array()));
    }


    /**
     * @override
     */
    public function quote($value, $type=\PDO::PARAM_STR)
    {
        $val = parent::quote($value, $type);
        // Fix for a driver version terminating all values with null byte
        if (strpos($val, "\0") !== false) {
                $val = substr($val, 0, -1);
        }
        return $val;
    }
}
