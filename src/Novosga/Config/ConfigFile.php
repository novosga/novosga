<?php

namespace Novosga\Config;

use Novosga\Util\Arrays;

/**
 * Configuration file.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class ConfigFile
{
    private $data = array();

    public function __construct($prop = null)
    {
        if (is_array($prop)) {
            $this->data = $prop;
        } else {
            $this->load();
        }
    }

    abstract public function name();

    final public function filename()
    {
        return NOVOSGA_CONFIG.DS.$this->name();
    }

    public function load()
    {
        $filename = $this->filename();
        if (file_exists($filename)) {
            $this->data = require $filename;
        }
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function get($name)
    {
        if (strpos($name, '.') !== false) {
            $tokens = explode('.', $name);
            $value = $this->data;
            foreach ($tokens as $token) {
                if ($value === null) {
                    break;
                }
                $value = Arrays::value($value, $token, null);
            }

            return $value;
        }

        return Arrays::value($this->data, $name, null);
    }

    public function values()
    {
        return $this->data;
    }

    public function save()
    {
        $filename = $this->filename();
        // verifica se será possível escrever a configuração no arquivo de configuracao
        if (file_exists($filename) && !is_writable($filename)) {
            throw new Exception(sprintf(_('Arquivo de configuação (%s) somente leitura'), $this->filename));
        }
        $arr = Arrays::toString($this->data);
        file_put_contents($filename, "<?php\nreturn $arr;");
    }
}
