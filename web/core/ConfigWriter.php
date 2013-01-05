<?php
namespace core;

use \core\util\Strings;

/**
 * Config Writer
 * 
 * @author rogeriolino
 */
class ConfigWriter {

    const CONFIG_LAYOUT = '<?php
namespace {namespace};
/* Arquivo de configuracao gerado automaticamente via {generator} */
class Config {
    const SGA_INSTALLED = true;
    const DB_TYPE = "{db_type}";
    const DB_HOST = "{db_host}";
    const DB_PORT = "{db_port}";
    const DB_USER = "{db_user}";
    const DB_PASS = "{db_pass}";
    const DB_NAME = "{db_name}";
}
';
    
    public static function write(array $values) {
        $values['namespace'] = __NAMESPACE__;
        $values['generator'] = 'ConfigWriter';
        $config = Strings::format(self::CONFIG_LAYOUT, $values);
        // atualizando o arquivo de configuracao
        file_put_contents(self::filename(), $config);
    }
    
    public static function filename() {
        return dirname(__FILE__) . DS . 'Config.php';
    }

}