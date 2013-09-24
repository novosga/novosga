<?php
namespace novosga\install;

use \Exception;
use \novosga\SGA;
use \novosga\Config;
use \novosga\ConfigWriter;
use \novosga\Security;
use \novosga\SGAContext;
use \novosga\db\DB;
use \novosga\http\AjaxResponse;
use \novosga\util\Arrays;
use \novosga\util\Strings;
use \novosga\controller\InternalController;

/**
 * InstallController
 * 
 * @author rogeriolino
 */
class InstallController extends InternalController {
    
    private static $steps = array();
    
    const STEPS = 'steps';
    const TOTAL_STEPS = 'totalSteps';
    const CURR_STEP_IDX = 'currStepIdx';
    const CURR_STEP = 'currStep';
    
    public function __construct() {
    }
    
    private function getSteps() {
        if (empty(self::$steps)) {
            self::$steps[] = new InstallStep(0, _('Início')); // install welcome
            self::$steps[] = new InstallStep(1, _('Verificação de Requisitos')); // install check
            self::$steps[] = new InstallStep(2, _('Licença')); // license
            self::$steps[] = new InstallStep(3, _('Configurar Banco de Dados')); // DB
            self::$steps[] = new InstallStep(4, _('Configurar Administrador')); // Admin
            self::$steps[] = new InstallStep(5, _('Aplicar')); // Aplicar
        }
        return self::$steps;
    }

    public function index(SGAContext $context) {
        if (Config::SGA_INSTALLED) {
            SGA::redirect('/');
        }
        $steps = $this->getSteps();
        $index = (int) $context->request()->getParameter(SGA::K_INSTALL);
        // após o step 3 (banco de dados) verifica se já tem uma versão do sga instalada
        if ($index == 4) {
            $this->checkMigration($context);
        }
        $context->setParameter(self::STEPS, $steps);
        $context->setParameter(self::TOTAL_STEPS, sizeof($steps));
        $context->setParameter(self::CURR_STEP_IDX, $index);
        $context->setParameter(self::CURR_STEP, $steps[$index]);
    }
    
    public function doStep(SGAContext $context, $step) {
        $steps = $this->getSteps();
        $data = array(
            'steps' => $steps,
            'totalSteps' => sizeof($steps),
            'index' => $step,
            'currStep' => $steps[$step],
            'error' => $context->session()->get('error')
        );
        if ($step > 0) {
            $data['prevStep'] = $steps[$step - 1];
        }
        if ($step < sizeof($steps) - 1) {
            $data['nextStep'] = $steps[$step + 1];
        }
        switch ($step) {
            case 0:
                $this->step0($context, $data);
                break;
            case 1:
                $this->step1($context, $data);
                break;
            case 2:
                $this->step2($context, $data);
                break;
            case 3:
                $this->step3($context, $data);
                break;
            case 4:
                // após o step 3 (banco de dados) verifica se já tem uma versão do sga instalada
                $this->checkMigration($context);
                $this->step4($context, $data);
                break;
            case 5:
                $this->step5($context, $data);
                break;
        }
        return $data;
    }
    
    /**
     * Step 0
     * 
     * Passo para escolher o banco de dados a ser utilizado
     * na instalação
     * 
     * @param \novosga\SGAContext $context
     * @param array $data
     */
    public function step0(SGAContext $context, array &$data) {
        $currAdapter = $context->session()->get('adapter');
        // desabilita o proximo, para liberar so quando marcar uma opção
        $context->session()->set('error', $currAdapter == null);
        $data['version'] = SGA::VERSION;
        $scriptHeader = function($file) {
            $header = array();
            $lines = file($file);
            $prefix = '-- @';
            foreach ($lines as $line) {
                if (strcmp(substr($line, 0, strlen($prefix)), $prefix) !== 0) {
                    break;
                }
                preg_match_all('/@(.*)=(.*)\n/', $line, $matches); 
                if (sizeof($matches) >= 3) {
                    $header[$matches[1][0]] = $matches[2][0];
                }
            }
            return $header;
        };

        $scripts = array();
        $files = glob(__DIR__ . DS . 'sql' . DS . 'create' . DS . '*.sql');
        foreach ($files as $file) {
            $header = $scriptHeader($file);
            $header['id'] = current(explode('.', basename($file)));
            $scripts[] = $header;
        }
        $data['scripts'] = $scripts;
        $data['currAdapter'] = $context->session()->get('adapter');
    }
    
    /**
     * Step 1
     * Passo para verificação de requisitos mínimos para rodar o Novo SGA
     * 
     * @param \novosga\SGAContext $context
     * @param array $data
     */
    public function step1(SGAContext $context, array &$data) {
        $fatal = false;
        $adapter = InstallData::$dbTypes[$context->session()->get('adapter_driver')];
        $data['adapter'] = $adapter;
        /*
         * minimum requirements
         */
        $requiredsSetup = array(
            array('name' => 'PHP', 'key' => 'php', 'version_required' => '5.3.0', 'ext' => false),
            array('name' => 'PDO', 'key' => 'pdo', 'version_required' => '1.0.0', 'ext' => true),
            array('name' => $adapter['label'], 'key' => $adapter['driver'], 'version_required' => $adapter['version'], 'ext' => true),
            array('name' => 'Multibyte String', 'key' => 'mbstring', 'ext' => true)
        );
        foreach ($requiredsSetup as &$req) {
            $success = true;
            if ($req['ext']) {
                $success = extension_loaded($req['key']);
                if ($success) { 
                    // if loaded then check version
                    if (isset($req['version_required'])) {
                        $req['version'] = phpversion($req['key']);
                        $success = version_compare($req['version'], $req['version_required'], '>=');
                        $req['result'] = $req['version'];
                    } else {
                        $req['version_required'] = '*';
                        $req['result'] = _('Instalado');
                    }
                } else {
                    $req['result'] = _('Não instalado');
                }
            } else if ($req['key'] == 'php') {
                $req['version'] = phpversion();
                $success = version_compare($req['version'], $req['version_required'], '>=');
                $req['result'] = $req['version'];
            }
            if ($success) {
                $req['class'] = ''; //'success'
            } else {
                $fatal = true;
                $req['class'] =  'error';
            }
        }
        $data['requiredsSetup'] = $requiredsSetup;
        /*
         * file permissions
         */
        $configFile = CORE_PATH . DS . 'Config.php';
        $requiredsPermission = array(
            array('label' => _('Configuração do SGA'), 'file' => $configFile, 'required' => _('Escrita')),
            array('label' => _('Diretório temporário'), 'file' => sys_get_temp_dir(), 'required' => _('Escrita')),
            array('label' => _('Session Save Path'), 'file' => session_save_path(), 'required' => _('Escrita')),
        );
        foreach ($requiredsPermission as &$req) {
            if (is_writable($req['file'])) {
                $req['result'] = _('Escrita');
                $req['class'] = ''; //'success'
            } else {
                $fatal = true;
                $req['result'] = _('Somente leitura');
                $req['class'] = 'error';
            }
        }
        $data['requiredsPermission'] = $requiredsPermission;
        $data['fatal'] = $fatal;
        $data['php_uname'] = php_uname();
        $data['php_sapi_name'] = php_sapi_name();
        $data['timezone'] = date_default_timezone_get();
    }
    
    /**
     * Step 2
     * 
     * Licença do software
     * 
     * @param \novosga\SGAContext $context
     * @param array $data
     */
    public function step2(SGAContext $context, &$data) {
        $default = 'en';
        $lang = SGA::defaultClientLanguage();
        $filePrefix = 'COPYING_';
        if (!file_exists($filePrefix . $lang)) {
            $lang = $default;
        }
        $data['license'] = file_get_contents($filePrefix . $lang);
    }
    
    /**
     * Step 3
     * 
     * Informações de conexão ao banco de dados
     * 
     * @param \novosga\SGAContext $context
     * @param array $data
     */
    public function step3(SGAContext $context, &$data) {
        $data['data'] = $context->session()->get(InstallData::SESSION_KEY);
        if (!$data['data']) {
            $data['data'] = new InstallData();
        }
        $data['data']->database['db_type'] = $context->session()->get('adapter');
        $data['driver'] = $context->session()->get('adapter_driver');
        // setting default port
        if (!$data['data']->database['db_port']) {
            $data['data']->database['db_port'] = InstallData::$dbTypes[$data['driver']]['port'];
        }
        $data['rbmsName'] = InstallData::$dbTypes[$data['driver']]['rdms'];
        $context->session()->set(InstallData::SESSION_KEY, $data['data']);
    }
    
    /**
     * Step 4
     * 
     * Informações do usuário administrador
     * 
     * @param \novosga\SGAContext $context
     * @param array $data
     */
    public function step4(SGAContext $context, &$data) {
        $session = $context->session();
        $data['data'] = $session->get(InstallData::SESSION_KEY);
        if (!$data['data']) {
            $data['data'] = new InstallData();
            $session->set(InstallData::SESSION_KEY, $data['data']);
        }
        $data['currVersion'] = $context->getParameter('currVersion');
    }
    
    /**
     * Step 5
     * 
     * Licença do software
     * 
     * @param \novosga\SGAContext $context
     * @param array $data
     */
    public function step5(SGAContext $context, &$data) {
    }
    
    // post actions
    
    public function set_adapter(SGAContext $context) {
        $context->session()->del('adapter');
        $context->session()->del('adapter_driver');
        $response = new AjaxResponse();
        if ($context->request()->isPost()) {
            $adapter = Arrays::value($_POST, 'adapter');
            $driver = $adapter;
            if ($driver === 'mssql') {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $driver .= '_win';
                } else {
                    $driver .= '_linux';
                }
            }
            if (array_key_exists($driver, InstallData::$dbTypes)) {
                $response->success = true;
                $context->session()->set('adapter', $adapter);
                $context->session()->set('adapter_driver', $driver);
            } else {
                $response->message = sprintf(_('Opção inválida: %s'), $adapter);
            }
        } else {
            $response = $this->postErrorResponse();
        }
        $context->response()->jsonResponse($response);
    }
    
    public function info(SGAContext $context) {
        if (!Config::SGA_INSTALLED) {
            echo SGA::info();
        } else {
            echo _('Por questões de segurança as informações sobre o ambiente são desabilitadas após a instalação.');
        }
        exit();
    }
    
    private function script_create($type) {
         return dirname(__FILE__). DS . 'sql' . DS . 'create' . DS . $type . '.sql';
    }
    
    private function script_data() {
         return dirname(__FILE__). DS . 'sql' . DS . 'data' . DS . 'default.sql';
    }
    
    private function script_migration($from) {
        $path = dirname(__FILE__). DS . 'sql' . DS . 'migrate' . DS;
        // sql format "from:to.sql"
        return $path . $from . ':' . SGA::VERSION . '.sql';
    }
    
    public function test_db(SGAContext $context) {
        if ($context->request()->isPost()) {
            $response = new AjaxResponse(true, _('Banco de Dados testado com sucesso!'));
            $session = $context->session();
            $data = $session->get(InstallData::SESSION_KEY);
            try {
                foreach (InstallData::$dbFields as $field => $message) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        throw new Exception($message);
                    }
                }
                $db = array();
                foreach (InstallData::$dbFields as $field => $message) {
                    $db[$field] = $_POST[$field];
                }
                $db_type = Arrays::value($db, 'db_type');
                $sqlFile = $this->script_create($db_type);
                if (!file_exists($sqlFile)) {
                    throw new Exception(_('Não foi encontrado arquivo SQL para o tipo de banco escolhido'));
                }
                $data->database = $db;
                // testing connection
                DB::createConn($db['db_user'], $db['db_pass'], $db['db_host'], $db['db_port'], $db['db_name'], $db['db_type']);
                $em = DB::getEntityManager();
                $em->beginTransaction();
                $em->rollback();
            } catch (Exception $e) {
                $response->success = false;
                $response->message = $e->getMessage();
            }
            $session->set(InstallData::SESSION_KEY, $data);
        } else {
            $response = $this->postErrorResponse();
        }
        $context->response()->jsonResponse($response);
    }
    
    private function checkMigration(SGAContext $context) {
        $version = $this->getCurrentVersion($context);
        if ($version) {
            //$script = 
        }
        $context->setParameter('currVersion', "$version");
    }
    
    private function getCurrentVersion(SGAContext $context) {
        $data = $context->session()->get(InstallData::SESSION_KEY);
        $db = $data->database;
        DB::createConn($db['db_user'], $db['db_pass'], $db['db_host'], $db['db_port'], $db['db_name'], $db['db_type']);
        $conn = DB::getEntityManager()->getConnection();
        // TODO
        return null;
    }
    
    public function set_admin(SGAContext $context) {
        if ($context->request()->isPost()) {
            $response = new AjaxResponse(true, _('Dados do usuário informados com sucesso'));
            $session = $context->session();
            $data = $session->get(InstallData::SESSION_KEY);
            if (!$data) {
                $data = new InstallData();
            }
            try {
                foreach (InstallData::$adminFields as $field => $message) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        throw new Exception($message);
                    }
                }
                $_POST['senha_usu_2'] = Arrays::value($_POST, 'senha_usu_2');

                $adm = array();
                $adm['login_usu'] = Arrays::value($_POST, 'login_usu');
                $adm['nm_usu'] = Arrays::value($_POST, 'nm_usu');
                $adm['ult_nm_usu'] = Arrays::value($_POST, 'ult_nm_usu');
                $adm['senha_usu'] = Arrays::value($_POST, 'senha_usu');

                if (!ctype_alnum($adm['login_usu'])) {
                    throw new Exception(_('O login deve conter somente letras e números.'));
                }
                if (strlen($adm['login_usu']) < 5 || strlen($adm['login_usu']) > 20) {
                    throw new Exception(_('O login deve possuir entre 5 e 20 caracteres (letras ou números).'));
                }
                if (strlen($adm['senha_usu']) < 6) {
                    throw new Exception(_('A senha deve possuir 6 ou mais letras/números.'));
                }
                if ($_POST['senha_usu'] != $_POST['senha_usu_2']) {
                    throw new Exception(_('A senha não confere com a confirmação de senha.'));
                }
                $adm['senha_usu_2'] = '';
                $data->admin = $adm;

            } catch (Exception $e) {
                $response->success = false;
                $response->message = $e->getMessage();
            }
            $session->set(InstallData::SESSION_KEY, $data);
        } else {
            $response = $this->postErrorResponse();
        }
        $context->response()->jsonResponse($response);
    }
    
    public function do_install(SGAContext $context) {
        if ($context->request()->isPost()) {
            $response = new AjaxResponse(true, _('Instalação concluída com sucesso'));
            $conn = null;
            $session = $context->session();
            try {
                if (Config::SGA_INSTALLED) {
                    throw new Exception(_('O SGA já está instalado'));
                }
                $data = $session->get(InstallData::SESSION_KEY);
                if (!$data) {
                    throw new Exception(_('Os dados da instalação não foram encontrados. Favor iniciar novamente'));
                }
                $db = $data->database;
                $db_type = $db['db_type'];
                
                $configFile = ConfigWriter::filename();
                // verifica se será possível escrever a configuração no arquivo Config.php
                if (!is_writable($configFile)) {
                    $msg = _('Arquivo de configuação (%s) somente leitura');
                    throw new Exception(sprintf($msg, $configFile));
                }
                
                DB::createConn($db['db_user'], $db['db_pass'], $db['db_host'], $db['db_port'], $db['db_name'], $db['db_type']);
                $em = DB::getEntityManager();
                $conn = $em->getConnection();
                //$conn->beginTransaction();
                
                $version = $this->getCurrentVersion($context);
                // atualizando/migrando
                if ($version) {
                    $sql = $this->script_migration($version);
                    if (!is_readable($sql)) {
                        $msg = _('Script SQL de instalação não encontrado (%s)');
                        throw new Exception(sprintf($msg, $sql));
                    }
                    // executando arquivo sql de migracao
                    $conn->exec(file_get_contents($sql));
                } 
                // nova instalacao
                else {
                    $sqlInitFile = $this->script_create($db_type);
                    $sqlDataFile = $this->script_data();
                    // verifica se consegue ler o arquivo de criacao do banco
                    if (!is_readable($sqlInitFile)) {
                        $msg = _('Script SQL de instalação não encontrado (%s)');
                        throw new Exception(sprintf($msg, $sqlInitFile));
                    }
                    // verifica se consegue ler o arquivo dos dados iniciais
                    if (!is_readable($sqlDataFile)) {
                        $msg = _('Script SQL de instalação não encontrado (%s)');
                        throw new Exception(sprintf($msg, $sqlDataFile));
                    }
                    
                    // executando arquivo sql de criacao
                    $conn->exec(file_get_contents($sqlInitFile));
                    // executando arquivo sql de dados iniciais
                    $adm = $data->admin;
                    $adm['senha_usu'] = Security::passEncode($adm['senha_usu']);
                    $sql = Strings::format(file_get_contents($sqlDataFile), $adm);
                    $conn->exec($sql);
                }
                
                //$conn->commit();
                
                // atualizando arquivo de configuracao
                ConfigWriter::write($db);
                // se sucesso limpa a sessao
                SGA::getContext()->session()->clear();
            } catch (Exception $e) {
                if ($conn && $conn->isTransactionActive()) {
                    $conn->rollBack();
                }
                $response->success = false;
                $response->message = $e->getMessage();
            }
        } else {
            $response = $this->postErrorResponse();
        }
        $context->response()->jsonResponse($response);
    }
    
    private function postErrorResponse() {
        return new AjaxResponse(false, _('Requisição inválida'));
    }

}
