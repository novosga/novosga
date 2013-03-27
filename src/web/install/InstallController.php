<?php
namespace install;

require_once(__DIR__ . '/InstallStep.php');
require_once(__DIR__ . '/InstallData.php');
require_once(__DIR__ . '/InstallView.php');

use \Exception;
use \core\SGA;
use \core\Config;
use \core\ConfigWriter;
use \core\Security;
use \core\SGAContext;
use \core\db\DB;
use \core\http\AjaxResponse;
use \core\util\Arrays;
use \core\util\Strings;
use \core\controller\InternalController;

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
    const SGALIVRE = 'sgalivre';
    
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

    protected function createView() {
        return new InstallView();
    }
    
    public function index(SGAContext $context) {
        if (Config::SGA_INSTALLED) {
            SGA::redirect('/');
        }
        $steps = $this->getSteps();
        $index = (int) $context->getRequest()->getParameter(SGA::K_INSTALL);
        // após o step 3 (banco de dados) verifica se já tem uma versão do sga instalada
        if ($index == 4) {
            $this->checkMigration($context);
        }
        $context->setParameter(self::STEPS, $steps);
        $context->setParameter(self::TOTAL_STEPS, sizeof($steps));
        $context->setParameter(self::CURR_STEP_IDX, $index);
        $context->setParameter(self::CURR_STEP, $steps[$index]);
    }
    
    public function set_adapter(SGAContext $context) {
        $context->getSession()->del('adapter');
        $response = new AjaxResponse();
        if ($context->getRequest()->isPost()) {
            $adapter = Arrays::value($_POST, 'adapter');
            if (array_key_exists($adapter, InstallData::$dbTypes)) {
                $response->success = true;
                $context->getSession()->set('adapter', $adapter);
            } else {
                $response->message = sprintf(_('Opção inválida: %s'), $adapter);
            }
        } else {
            $response = $this->postErrorResponse();
        }
        $context->getResponse()->jsonResponse($response);
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
        if ($from == self::SGALIVRE) {
            return $path . self::SGALIVRE . ".sql";
        }
        // sql format "from:to.sql"
        return $path . $from . ':' . SGA::VERSION . '.sql';
    }
    
    public function test_db(SGAContext $context) {
        if ($context->getRequest()->isPost()) {
            $response = new AjaxResponse(true, _('Banco de Dados testado com sucesso!'));
            $session = $context->getSession();
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
        $context->getResponse()->jsonResponse($response);
    }
    
    private function checkMigration(SGAContext $context) {
        $version = $this->getCurrentVersion($context);
        if ($version) {
            //$script = 
        }
        $context->setParameter('currVersion', "$version");
    }
    
    private function getCurrentVersion(SGAContext $context) {
        $data = $context->getSession()->get(InstallData::SESSION_KEY);
        $db = $data->database;
        DB::createConn($db['db_user'], $db['db_pass'], $db['db_host'], $db['db_port'], $db['db_name'], $db['db_type']);
        $conn = DB::getEntityManager()->getConnection();
        // is sgalivre
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM menus");
            $stmt->execute();
            return 'sgalivre';
        } catch (\Exception $e) {
        }
        return null;
    }
    
    public function set_admin(SGAContext $context) {
        if ($context->getRequest()->isPost()) {
            $response = new AjaxResponse(true, _('Dados do usuário informados com sucesso'));
            $session = $context->getSession();
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
        $context->getResponse()->jsonResponse($response);
    }
    
    public function do_install(SGAContext $context) {
        if ($context->getRequest()->isPost()) {
            $response = new AjaxResponse(true, _('Instalação concluída com sucesso'));
            $conn = null;
            $session = $context->getSession();
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
                SGA::getContext()->getSession()->clear();
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
        $context->getResponse()->jsonResponse($response);
    }
    
    private function postErrorResponse() {
        return new AjaxResponse(false, _('Requisição inválida'));
    }

}
