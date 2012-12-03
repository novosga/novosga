<?php

namespace Doctrine\DBAL\Driver\PDODblib;

/**
 * The PDO-based DBLib driver.
 *
 * @author rogeriolino
 */
class Driver implements \Doctrine\DBAL\Driver
{
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        return new Connection(
            $this->_constructPdoDsn($params),
            $username,
            $password,
            $driverOptions
        );
    }

    /**
     * Constructs the Sqlsrv PDO DSN.
     *
     * @return string  The DSN.
     */
    private function _constructPdoDsn(array $params)
    {
        $dsn = 'dblib:host=';

        if (isset($params['host'])) {
            $dsn .= $params['host'];
        }

        if (isset($params['port']) && !empty($params['port'])) {
            $dsn .= ',' . $params['port'];
        }

        if (isset($params['dbname'])) {;
            $dsn .= ';dbname=' .  $params['dbname'];
        }

        if (isset($params['MultipleActiveResultSets'])) {
            $dsn .= '; MultipleActiveResultSets=' . ($params['MultipleActiveResultSets'] ? 'true' : 'false');
        }

        return $dsn;
    }

    public function getDatabasePlatform()
    {
        return new \Doctrine\DBAL\Platforms\SQLServer2008Platform();
    }

    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        return new \Doctrine\DBAL\Schema\SQLServerSchemaManager($conn);
    }

    public function getName()
    {
        return 'pdo_dblib';
    }

    public function getDatabase(\Doctrine\DBAL\Connection $conn)
    {
        $params = $conn->getParams();
        return $params['dbname'];
    }
}
