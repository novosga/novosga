<?php
namespace Novosga\Config;

use Novosga\Util\Arrays;

/**
 * Configuration file
 * 
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class ConfigFile {
    
    private $data = array();
    
    public function __construct($prop = null) {
        if (is_array($prop)) {
            $this->data = $prop;
        } else {
            $this->load();
        }
    }
    
    public abstract function name();

    public function load() {
        $filename = NOVOSGA_ROOT . '/config/' . $this->name();
        if (file_exists($filename)) {
            $this->data = require $filename;
        }
    }

    public function set($name, $value) {
        $this->data[$name] = $value;
    }
    
    public function get($name) {
        return Arrays::value($this->data, $name, null);
    }
    
    public function values() {
        return $this->data;
    }
    
}
