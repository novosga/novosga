<?php

namespace Novosga\Install;

/**
 * Classe para guardar os dados da instalacao na sessao.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class InstallData
{
    const SESSION_KEY = 'SGA_INSTALL_DATA';

    public static $dbTypes = array(
        'pgsql' => array(
            'label' => 'PDO PgSQL',
            'rdms' => 'PostgreSQL',
            'version' => '1.0.2',
            'port' => '5432',
            'driver' => array(
                'linux' => 'pdo_pgsql',
                'win' => 'pdo_pgsql',
            ),
        ),
        'mysql' => array(
            'label' => 'PDO MySQL',
            'rdms' => 'MySQL',
            'version' => '1.0.0',
            'port' => '3306',
            'driver' => array(
                'linux' => 'pdo_mysql',
                'win' => 'pdo_mysql',
            ),
        ),
    );

    public static $dbFields = array(
        'driver' => 'O tipo do Banco de Dados deve ser informado.',
        'host' => 'O endereço do Banco de Dados deve ser informado.',
        'port' => 'A porta do Banco de Dados deve ser informado.',
        'user' => 'O usuário do Banco de Dados deve ser informado.',
        'password' => 'A senha do Banco de Dados deve ser informado.',
        'dbname' => 'O nome do Banco de Dados deve ser informado.',
        'charset' => 'A codificação (charset) do banco deve ser informada.',
    );

    public static $adminFields = array(
        'nome' => 'O nome do usuário deve ser informado.',
        'sobrenome' => 'O sobrenome do usuário deve ser informado.',
        'login' => 'O login do usuário deve ser informado.',
        'senha' => 'A senha do usuário deve ser informada.',
        'senha_2' => 'A confirmação da senha deve ser informada.',
    );

    public $database = array();
    public $admin = array();

    public function __construct()
    {
        foreach (self::$dbFields as $k => $v) {
            $this->database[$k] = '';
        }
        foreach (self::$adminFields as $k => $v) {
            $this->admin[$k] = '';
        }
    }
}
