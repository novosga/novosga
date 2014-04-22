<?php
namespace Novosga\Config;

use Novosga\Util\Arrays;

/**
 * Config File
 * 
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class ConfigFile {
    
    protected $data;
    protected $filename;
    
    public function __construct($name, $data = array()) {
        $this->filename = NOVOSGA_CONFIG . DS . "$name.php";
        if (!empty($data)) {
            $this->data = $data;
        } else if (file_exists($this->filename)) {
            $this->data = require $this->filename;
        }
    }
    
    public function set($name, $value) {
        $this->data[$name] = $value;
    }
    
    public function get($name) {
        return Arrays::value($this->data, $name, null);
    }
    
    /**
     * 
     * @return array
     */
    public function data() {
        return $this->data;
    }
    
    public function save() {
        // verifica se será possível escrever a configuração no arquivo de configuracao
        if (file_exists($this->filename) && !is_writable($this->filename)) {
            throw new Exception(sprintf(_('Arquivo de configuação (%s) somente leitura'), $this->filename));
        }
        $arr = Arrays::toString($this->data);
        file_put_contents($this->filename, "<?php\nreturn $arr;");
    }
    
}
