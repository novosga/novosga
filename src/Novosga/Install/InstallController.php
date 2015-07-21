<?php

namespace Novosga\Install;

use Exception;
use Novosga\Controller\InternalController;
use Novosga\Config\DatabaseConfig;
use Novosga\Http\JsonResponse;
use Novosga\Model\Configuracao;
use Novosga\Security;
use Novosga\App;
use Novosga\Context;
use Novosga\Util\Strings;

/**
 * InstallController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class InstallController extends InternalController
{
    private static $steps = array();

    const STEPS = 'steps';
    const TOTAL_STEPS = 'totalSteps';
    const CURR_STEP_IDX = 'currStepIdx';
    const CURR_STEP = 'currStep';

    private $os;

    public function __construct()
    {
        $this->os = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'win' : 'linux';
    }

    private function getSteps()
    {
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

    public function index(Context $context)
    {
        if (App::isInstalled()) {
            $context->app()->gotoHome();
        }
        $steps = $this->getSteps();
        $index = (int) $context->request()->get(App::K_INSTALL);
        // após o step 3 (banco de dados) verifica se já tem uma versão do sga instalada
        if ($index == 4) {
            $this->checkMigration($context);
        }
        $context->setParameter(self::STEPS, $steps);
        $context->setParameter(self::TOTAL_STEPS, sizeof($steps));
        $context->setParameter(self::CURR_STEP_IDX, $index);
        $context->setParameter(self::CURR_STEP, $steps[$index]);
    }

    public function doStep(Context $context, $step)
    {
        $context->session()->del('error');
        $steps = $this->getSteps();
        $data = array(
            'steps' => $steps,
            'totalSteps' => sizeof($steps),
            'index' => $step,
            'currStep' => $steps[$step],
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
        $data['error'] = $context->session()->get('error');
        $data['currAdapter'] = $context->session()->get('adapter');

        return $data;
    }

    /**
     * Step 0.
     *
     * Passo para escolher o banco de dados a ser utilizado
     * na instalação
     *
     * @param Context $context
     * @param array   $data
     */
    public function step0(Context $context, array &$data)
    {
        $currAdapter = $context->session()->get('adapter');
        // desabilita o proximo, para liberar so quando marcar uma opção
        $context->session()->set('error', $currAdapter == null);
        $data['version'] = App::VERSION;
        $scriptHeader = function ($file) {
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
        $files = glob(__DIR__.DS.'sql'.DS.'create'.DS.'*.sql');
        foreach ($files as $file) {
            $header = $scriptHeader($file);
            $header['id'] = current(explode('.', basename($file)));
            $scripts[] = $header;
        }
        $data['scripts'] = $scripts;
    }

    /**
     * Step 1
     * Passo para verificação de requisitos mínimos para rodar o Novo SGA.
     *
     * @param Context $context
     * @param array   $data
     */
    public function step1(Context $context, array &$data)
    {
        $fatal = false;
        $adapter = InstallData::$dbTypes[$context->session()->get('adapter')];
        $driver = $context->session()->get('adapter_driver');
        $data['adapter'] = $adapter;
        /*
         * minimum requirements
         */
        $requiredsSetup = array(
            array('name' => 'PHP', 'key' => 'php', 'version_required' => '5.3.2', 'ext' => false),
            array('name' => 'PDO', 'key' => 'pdo', 'version_required' => '1.0.0', 'ext' => true),
            array('name' => $adapter['label'], 'key' => $driver, 'version_required' => $adapter['version'], 'ext' => true),
            array('name' => 'json', 'key' => 'json', 'ext' => true),
            array('name' => 'gettext', 'key' => 'gettext', 'ext' => true),
        );
        foreach ($requiredsSetup as &$req) {
            $success = true;
            if ($req['ext']) {
                $success = extension_loaded($req['key']);
                if ($success) {
                    // if loaded then check version
                    if (isset($req['version_required'])) {
                        $req['version'] = phpversion($req['key']);
                        if ($req['version']) {
                            $success = version_compare($req['version'], $req['version_required'], '>=');
                            $req['result'] = $req['version'];
                        } else {
                            $success = false;
                            $req['result'] = _('Não instalado');
                        }
                    } else {
                        $req['version_required'] = '*';
                        $req['result'] = _('Instalado');
                    }
                } else {
                    $req['result'] = _('Não instalado');
                }
            } elseif ($req['key'] == 'php') {
                $req['version'] = phpversion();
                $success = version_compare($req['version'], $req['version_required'], '>=');
                $req['result'] = $req['version'];
            }
            if ($success) {
                $req['class'] = '';
            } else {
                $fatal = true;
                $req['class'] = 'danger';
            }
        }
        $data['requiredsSetup'] = $requiredsSetup;
        /*
         * file permissions
         */
        $requiredsPermission = array(
            array('label' => _('Configuração do SGA'), 'file' => NOVOSGA_CONFIG, 'required' => _('Escrita')),
            array('label' => _('Diretório temporário'), 'file' => sys_get_temp_dir(), 'required' => _('Escrita')),
            array('label' => _('Session Save Path'), 'file' => session_save_path(), 'required' => _('Escrita')),
        );
        foreach ($requiredsPermission as &$req) {
            if (is_writable($req['file'])) {
                $req['result'] = _('Escrita');
                $req['class'] = ''; //'success'
            } elseif (!is_dir($req['file'])) {
                $fatal = true;
                $req['result'] = _('Não existe');
                $req['class'] = 'danger';
            } else {
                $fatal = true;
                $req['result'] = _('Somente leitura');
                $req['class'] = 'danger';
            }
        }
        $data['requiredsPermission'] = $requiredsPermission;
        $data['fatal'] = $fatal;
        $data['php_uname'] = php_uname();
        $data['php_sapi_name'] = php_sapi_name();
        $data['timezone'] = date_default_timezone_get();
    }

    /**
     * Step 2.
     *
     * Licença do software
     *
     * @param Context $context
     * @param array   $data
     */
    public function step2(Context $context, &$data)
    {
        $context->session()->set('error', true);
        $data['license'] = file_get_contents(NOVOSGA_ROOT.'/LICENSE');
    }

    /**
     * Step 3.
     *
     * Informações de conexão ao banco de dados
     *
     * @param Context $context
     * @param array   $data
     */
    public function step3(Context $context, &$data)
    {
        $data['data'] = $context->session()->get(InstallData::SESSION_KEY);
        if (!$data['data']) {
            $data['data'] = new InstallData();
            $data['data']->database['charset'] = 'utf8';
        }
        $adapter = $context->session()->get('adapter');
        $driver = $context->session()->get('adapter_driver');
        $data['data']->database['driver'] = $driver;
        // setting default port
        if (!$data['data']->database['port']) {
            $data['data']->database['port'] = InstallData::$dbTypes[$adapter]['port'];
        }
        $data['rdmsName'] = InstallData::$dbTypes[$adapter]['rdms'];
        $context->session()->set(InstallData::SESSION_KEY, $data['data']);
    }

    /**
     * Step 4.
     *
     * Informações do usuário administrador
     *
     * @param Context $context
     * @param array   $data
     */
    public function step4(Context $context, &$data)
    {
        $session = $context->session();
        $data['data'] = $session->get(InstallData::SESSION_KEY);
        if (!$data['data']) {
            $data['data'] = new InstallData();
            $session->set(InstallData::SESSION_KEY, $data['data']);
        }
        $data['currVersion'] = $context->getParameter('currVersion');
    }

    /**
     * Step 5.
     *
     * Licença do software
     *
     * @param Context $context
     * @param array   $data
     */
    public function step5(Context $context, &$data)
    {
    }

    // post actions

    public function set_adapter(Context $context)
    {
        $context->session()->del('adapter');
        $context->session()->del('adapter_driver');
        $response = new JsonResponse();
        if ($context->request()->isPost()) {
            $adapter = $context->request()->post('adapter');
            if (array_key_exists($adapter, InstallData::$dbTypes)) {
                $response->success = true;
                $context->session()->set('adapter', $adapter);
                $context->session()->set('adapter_driver', InstallData::$dbTypes[$adapter]['driver'][$this->os]);
            } else {
                $response->message = sprintf(_('Opção inválida: %s'), $adapter);
            }
        } else {
            $response = $this->postErrorResponse();
        }

        return $response;
    }

    public function info(Context $context)
    {
        if (!App::isInstalled()) {
            echo App::info();
        } else {
            echo _('Por questões de segurança as informações sobre o ambiente são desabilitadas após a instalação.');
        }
        exit();
    }

    private function script_create($type)
    {
        return dirname(__FILE__).DS.'sql'.DS.'create'.DS.$type.'.sql';
    }

    private function script_data()
    {
        return dirname(__FILE__).DS.'sql'.DS.'data'.DS.'default.sql';
    }

    private function modules()
    {
        return glob(MODULES_PATH.DS.'sga'.DS.'*', GLOB_ONLYDIR);
    }

    public function test_db(Context $context)
    {
        if ($context->request()->isPost()) {
            $response = new JsonResponse(true, _('Banco de Dados testado com sucesso!'));
            $session = $context->session();
            $data = $session->get(InstallData::SESSION_KEY);
            try {
                foreach (InstallData::$dbFields as $field => $message) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        throw new Exception($message);
                    }
                }
                $config = array();
                foreach (InstallData::$dbFields as $field => $message) {
                    $config[$field] = $_POST[$field];
                }
                $adapter = $context->session()->get('adapter');
                $sqlFile = $this->script_create($adapter);
                if (!file_exists($sqlFile)) {
                    throw new Exception(_('Não foi encontrado arquivo SQL para o tipo de banco escolhido'));
                }
                $data->database = $config;
                // testing connection

                $db = new DatabaseConfig($data->database);
                $em = $db->createEntityManager();
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

        return $response;
    }

    private function checkMigration(Context $context)
    {
        $data = $context->session()->get(InstallData::SESSION_KEY);
        $db = new DatabaseConfig($data->database);
        $em = $db->createEntityManager();
        $version = Configuracao::get($em, 'version');
        $context->setParameter('currVersion', $version ? $version->getValor() : '');
    }

    public function set_admin(Context $context)
    {
        if ($context->request()->isPost()) {
            $response = new JsonResponse(true, _('Dados do usuário informados com sucesso'));
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

                $adm = array();
                $adm['login'] = $context->request()->post('login');
                $adm['nome'] = $context->request()->post('nome');
                $adm['sobrenome'] = $context->request()->post('sobrenome');
                $adm['senha'] = $context->request()->post('senha');

                if (!ctype_alnum($adm['login'])) {
                    throw new Exception(_('O login deve conter somente letras e números.'));
                }
                if (strlen($adm['login']) < 5 || strlen($adm['login']) > 20) {
                    throw new Exception(_('O login deve possuir entre 5 e 20 caracteres (letras ou números).'));
                }
                if (strlen($adm['senha']) < 6) {
                    throw new Exception(_('A senha deve possuir 6 ou mais letras/números.'));
                }
                if ($adm['senha'] != $context->request()->post('senha_2')) {
                    throw new Exception(_('A senha não confere com a confirmação de senha.'));
                }
                $data->admin = $adm;
            } catch (Exception $e) {
                $response->success = false;
                $response->message = $e->getMessage();
            }
            $session->set(InstallData::SESSION_KEY, $data);
        } else {
            $response = $this->postErrorResponse();
        }

        return $response;
    }

    public function do_install(Context $context)
    {
        if ($context->request()->isPost()) {
            $response = new JsonResponse(true, _('Instalação concluída com sucesso'));
            $conn = null;
            $session = $context->session();
            try {
                if (App::isInstalled()) {
                    throw new Exception(_('O SGA já está instalado'));
                }
                $data = $session->get(InstallData::SESSION_KEY);
                if (!$data) {
                    throw new Exception(_('Os dados da instalação não foram encontrados. Favor iniciar novamente'));
                }

                $db = new DatabaseConfig($data->database);
                $em = $db->createEntityManager();
                $conn = $em->getConnection();
                //$conn->beginTransaction();

                $version = Configuracao::get($em, 'version');
                // atualizando/migrando
                if ($version) {
                    $scripts = self::migrationScripts($version->getValor(), App::VERSION);
                    foreach ($scripts as $sql) {
                        if (!is_readable($sql)) {
                            $msg = _('Script SQL de instalação não encontrado (%s)');
                            throw new Exception(sprintf($msg, $sql));
                        }
                        // executando arquivo sql de migracao
                        $conn->exec(file_get_contents($sql));
                    }
                }
                // nova instalacao
                else {
                    $sqlInitFile = $this->script_create($session->get('adapter'));
                    // verifica se consegue ler o arquivo de criacao do banco
                    if (!is_readable($sqlInitFile)) {
                        $msg = _('Script SQL de instalação não encontrado (%s)');
                        throw new Exception(sprintf($msg, $sqlInitFile));
                    }
                    // executando arquivo sql de criacao
                    $conn->exec(file_get_contents($sqlInitFile));

                    // instalando modulos
                    $service = new \Novosga\Service\ModuloService($em);
                    $modules = $this->modules();
                    foreach ($modules as $dir) {
                        $service->install($dir, 'sga.'.basename($dir), 1);
                    }

                    // finalizando instalacao com SQL auxiliar
                    $sqlDataFile = $this->script_data();
                    // verifica se consegue ler o arquivo dos dados iniciais
                    if (!is_readable($sqlDataFile)) {
                        $msg = _('Script SQL de instalação não encontrado (%s)');
                        throw new Exception(sprintf($msg, $sqlDataFile));
                    }

                    // executando arquivo sql de dados iniciais
                    $adm = $data->admin;
                    $adm['senha'] = Security::passEncode($adm['senha']);
                    $sql = Strings::format(file_get_contents($sqlDataFile), $adm);
                    $conn->exec($sql);
                }

                //$conn->commit();

                // atualiza versao no banco
                Configuracao::set($em, 'version', App::VERSION);

                // atualizando arquivo de configuracao
                $db->save();
                // se sucesso limpa a sessao
                $context->session()->clear();
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

        return $response;
    }

    private function postErrorResponse()
    {
        return new JsonResponse(false, _('Requisição inválida'));
    }

    public static function migrationScripts($from, $to)
    {
        $path = __DIR__.'/sql/migration';
        if (!is_dir($path)) {
            throw new Exception(sprintf('Caminho para nova versão inválido: %s', $path));
        }
        $scripts = array();
        $files = glob("$path/*.sql");
        foreach ($files as $file) {
            $version = str_replace('.sql', '', basename($file));
            if (version_compare($version, $from, '>') && version_compare($version, $to, '<=')) {
                $scripts[] = $file;
            }
        }

        return $scripts;
    }
}
