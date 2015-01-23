<?php
namespace Novosga;

use Exception;
use Novosga\Service\AcessoService;

/**
 * Novo SGA App
 * 
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class App extends \Slim\Slim {
    
    const VERSION = "1.4.0-dev";
    const CHARSET = "utf-8";
    
    // SESSION KEYS
    const K_CURRENT_USER    = "SGA_CURRENT_USER";
    
    private $context;
    private $acessoService;
    
    private static $instance;
    
    public function __construct(array $userSettings = array()) {
        $twig = new \Slim\Views\Twig();
        $userSettings = array_merge($userSettings, array(
            'debug' => NOVOSGA_DEV,
            'cache' => NOVOSGA_CACHE,
            'templates.path' => NOVOSGA_TEMPLATES,
            'view' => $twig
        ));
        if (!$userSettings['debug']) {
            $twig->parserOptions = array(
                'cache' => $userSettings['cache']
            );
        }
        parent::__construct($userSettings);
        
        $this->view()->set('version', App::VERSION);
        $this->view()->set('lang', \Novosga\Util\I18n::lang());
        
        $this->view()->parserExtensions = array(
            new \Slim\Views\TwigExtension(),
            new \Twig_Extensions_Extension_I18n(),
            new Twig\Extensions()
        );
        
        if ($userSettings['debug']) {
            $this->view()->parserExtensions[] = new \Twig_Extension_Debug();
        }
    
        $app = $this;
        $app->notFound(function() use ($app) {
            $app->render(NOVOSGA_TEMPLATES . '/error/404.html.twig');
        });

        $app->error(function(\Exception $e) use ($app) {
            $app->view()->set('exception', $e);
            $app->render(NOVOSGA_TEMPLATES . '/error/500.html.twig');
        });
    }
    
    /**
     * 
     * @return App
     */
    public static function create(array $settings = array()) {
        if (!self::$instance) {
            self::$instance = new App($settings);
        }
        return self::$instance;
    }

    public function prepare(Config\DatabaseConfig $db) {
        $this->context = new Context($this, $db);
        $this->acessoService = new AcessoService();
        $this->add(new \Novosga\Slim\InstallMiddleware($this->context));
        $this->add(new \Novosga\Slim\AuthMiddleware($this->context));
    }
    
    /**
     * @return Context
     */
    public function getContext() {
        return $this->context;
    }
    
    /**
     * @return AcessoService
     */
    public function getAcessoService() {
        return $this->acessoService;
    }
        
    public function gotoLogin() {
        $this->redirect($this->request()->getRootUri() . '/login');
    }
    
    public function gotoHome() {
        $this->redirect($this->request()->getRootUri() . '/home');
    }
    
    public function gotoModule() {
        $this->redirect($this->request()->getRootUri() . '/modules/' . $this->getContext()->getModulo()->getChave());
    }
    
    public function moduleResource($moduleKey, $resource) {
        $filename = join(DS, array(
            MODULES_PATH, join(DS, explode(".", $moduleKey)), 'public', $resource)
        );
        if (file_exists($filename)) {
            $mime = \Novosga\Util\FileUtils::mime($filename);
            header("Content-type: $mime");
            echo file_get_contents($filename);
            exit();
        } else {
            throw new Exception(sprintf("Resource not found: %s", $filename));
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function render($template, $data = array(), $status = null) {
        if (substr($template, 0, 1) === '/' || substr($template, 0, 2) === '..') {
            // defined a template outside the default twigTemplateDirs
            $dir = dirname($template);
            $this->view()->twigTemplateDirs[] = $dir;
            $template = basename($template);
        }
        // for security purposes allow only .html.twig files
        $ext = ".html.twig";
        if (substr($template, -strlen($ext)) !== $ext) {
            throw new Exception('Você está tentando exibir um arquivo de template inválido.');
        }
        return parent::render($template, $data, $status);
    }
    
    /**
     * Autentica o usuario do sistema
     * @param type $user
     * @param type $pass
     * @return Usuario|null
     */
    public function auth($login, $pass) {
        $em = $this->getContext()->database()->createEntityManager();
        $config = \Novosga\Model\Configuracao::get($em, \Novosga\Auth\Authentication::KEY);
        $auth = ($config) ? $config->getValor() : array();
        $authMethods = \Novosga\Auth\AuthFactory::createList($this->getContext(), $auth);
        foreach ($authMethods as $auth) {
            try {
                $user = $auth->auth($login, $pass);
                if ($user) {
                    return $user;
                }
            } catch (\Exception $e) {
            }
        }
        return false;
    }
    
    public static function defaultClientLanguage() {
        $lang = \Novosga\Util\I18n::locale();
        return current(explode('_', $lang));
    }
    
    /**
     * Retorna informacoes gerais sobre o sistema
     */
    public static function info() {
        ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_end_clean();
        return $info;
    }    

}
