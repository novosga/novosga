<?php
namespace novosga\install;

/**
 * Classe para guardar os dados da instalacao na sessao
 * @author rogeriolino
 */
class InstallData {
    
    const SESSION_KEY = 'SGA_INSTALL_DATA';
    
    public static $dbTypes = array(
        'pgsql' => array(
            'label' => 'PDO PgSQL', 
            'rdms' => 'PostgreSQL', 
            'driver' => 'pdo_pgsql', 
            'version' => '1.0.2',
            'port' => '5432'
        ), 
        'mysql' => array(
            'label' => 'PDO MySQL', 
            'rdms' => 'MySQL', 
            'driver' => 'pdo_mysql', 
            'version' => '1.0.0',
            'port' => '3306'
        ), 
        'mssql_linux' => array(
            'label' => 'PDO SyBase', 
            'rdms' => 'MS SQL Server', 
            'driver' => 'pdo_dblib', 
            'version' => '1.0.0',
            'port' => '1433'
        ),
        'mssql_win' => array(
            'label' => 'Microsoft SQLSRV', 
            'rdms' => 'MS SQL Server', 
            'driver' => 'pdo_sqlsrv', 
            'version' => '1.0.0',
            'port' => '1433'
        )
    );
    
    public static $dbFields = array(
        'db_type' => 'O tipo do Banco de Dados deve ser informado.',
        'db_host' => 'O endereço do Banco de Dados deve ser informado.',
        'db_port' => 'A porta do Banco de Dados deve ser informado.',
        'db_user' => 'O usuário do Banco de Dados deve ser informado.',
        'db_pass' => 'A senha do Banco de Dados deve ser informado.',
        'db_name' => 'O nome do Banco de Dados deve ser informado.'
    );
    
    public static $adminFields = array(
        'nm_usu'     => 'O nome do usuário deve ser informado.',
        'ult_nm_usu' => 'O sobrenome do usuário deve ser informado.',
        'login_usu'  => 'O login do usuário deve ser informado.',
        'senha_usu'  => 'A senha do usuário deve ser informada.',
        'senha_usu_2'  => 'A confirmação da senha deve ser informada.'
    );
    
    public $database = array();
    public $admin = array();
    
    public function __construct() {
        foreach (self::$dbFields as $k => $v) {
            $this->database[$k] = '';
        }
        foreach (self::$adminFields as $k => $v) {
            $this->admin[$k] = '';
        }
    }
    
}
