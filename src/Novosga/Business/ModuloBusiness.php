<?php
namespace Novosga\Business;

use Exception;
use Novosga\Model\Modulo;
use Novosga\Model\Util\ModuleManifest;
use ZipArchive;

/**
 * ModuloBusiness
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ModuloBusiness extends ModelBusiness {
    
    /**
     * 
     * @param string $zipname
     * @param string $ext
     * @return Modulo
     */
    public function install8($zipname, $ext = 'zip') {
        $moduleDir = $this->extract($zipname, $ext);
        $this->verify($moduleDir);

        $manifest = $this->parseManifest($moduleDir, $this->key($zipname, $ext));
        
        $this->em->persist($manifest->getModule());
        $this->em->flush();
        
        return $manifest->getModule();
    }
    
    public function uninstall($key) {
        $module = $this->em->createQuery('SELECT e FROM NovoSGA\Model\Modulo e WHERE e.chave = :key')
                ->setParameter('key', $key)
                ->getOneOrNullResult();
        if (!$module) {
            throw new Exception(sprintf(_('Módulo %s não instalado'), $key));
        }
        @rmdir($module->getRealPath());
        $this->em->remove($module);
        $this->em->flush();
    }
    
    /**
     * 
     * @param string $zipname
     * @param string $ext
     * @return string
     */
    public function key($zipname, $ext = 'zip') {
        return str_replace(".$ext", "", basename($zipname));
    }
    
    /**
     * 
     * @param string $zipname
     * @param string $ext file extension
     * @return array 
     * @throws Exception
     */
    public function extract($zipname, $ext) {
        $name = $this->key($zipname, $ext);
        $path = explode(".", $name);

        if (sizeof($path) !== 2) {
            @unlink($zipname);
            throw new Exception(sprintf(_('Formato inválido do nome do módulo: %s. Era esperado {vendorName}.{moduleName}.%s'), basename($zipname), $ext));
        }

        $dir = MODULES_PATH . DS . $path[0];
        $moduleDir = $dir . DS . $path[1];

        if (file_exists($moduleDir)) {
            throw new Exception(_('Já possui um módulo com o mesmo nome'));
        }

        // zip extract
        $zip = new ZipArchive(); 
        $zip->open($zipname);
        $zip->extractTo(NOVOSGA_CACHE);
        $zip->close();
        
        @unlink($zipname);

        // vendor dir
        if (!is_dir($dir) && !@mkdir($dir)) {
            @unlink(NOVOSGA_CACHE . DS . $name);
            throw new Exception(_('Não foi possível criar o diretório do módulo'));
        }

        if (!@rename(NOVOSGA_CACHE . DS . $name, $moduleDir)) {
            @unlink(NOVOSGA_CACHE . DS . $name);
            throw new Exception(_('Não foi possível mover os arquivos para o diretório dos módulos'));
        }
        @unlink(NOVOSGA_CACHE . DS . $name);
        return $moduleDir;
    }
    
    /**
     * Verifica se o módulo possui os arquivos necessários
     * @param string $moduleDir
     * @throws Exception
     */
    public function verify($moduleDir) {
        $moduleName = basename($moduleDir);
        // module structure
        $files = array(
            "manifest.json",
            ucfirst($moduleName) . "Controller.php",
            "public" . DS . "images" . DS . "icon.png",
            "public" . DS . "css" . DS . "style.css",
            "public" . DS . "js" . DS . "script.js",
            "views" . DS . "index.html.twig",
        );
        foreach ($files as $file) {
            if (!file_exists($moduleDir . DS . $file)) {
                throw new Exception(sprintf(_('Arquivo %s não encontrado'), $file));
            }
        }
    }
    
    /**
     * 
     * @param type $moduleDir
     * @param type $key
     * @return ModuleManifest
     * @throws Exception
     */
    public function parseManifest($moduleDir, $key) {
        $data = file_get_contents($moduleDir . DS . "manifest.json");
        $json = json_decode($json, true);
        if (!$json) {
            throw new Exception(_('O Manifest não contém um JSON válido'));
        }
        return new ModuleManifest($key, $json);
    }
    
}
