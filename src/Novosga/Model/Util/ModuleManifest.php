<?php

namespace Novosga\Model\Util;

use Novosga\Model\Modulo;
use Novosga\Util\Arrays;

/**
 * Module Manifest.
 *
 * @author rogerio
 */
class ModuleManifest
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Modulo
     */
    private $module;

    public function __construct($key, array $data)
    {
        $this->key = $key;
        $this->data = $data;
    }

    /**
     * @return Modulo
     */
    public function getModule()
    {
        if (!$this->module) {
            $this->module = new Modulo();
            $this->module->setChave($this->key);
            $this->module->setTipo((int) Arrays::value($this->data, 'type', 0));
            $this->module->setNome(_(Arrays::value($this->data, 'name')));
            $this->module->setDescricao(_(Arrays::value($this->data, 'description')));
            $this->module->setStatus(0);
        }

        return $this->module;
    }

    /**
     * @return array
     */
    public function getScripts()
    {
        return Arrays::value($this->data, 'scripts', array());
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getScript($name)
    {
        $scripts = $this->getScripts();

        return (isset($scripts[$name])) ? $scripts[$name] : null;
    }
}
