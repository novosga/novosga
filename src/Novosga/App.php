<?php

namespace Novosga;

use Exception;
use Novosga\Util\I18n;
use Novosga\Config\AppConfig;
use Novosga\Config\DatabaseConfig;
use Novosga\Service\AcessoService;

/**
 * Novo SGA App.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class App extends \Slim\Slim
{
    const VERSION = '1.5.0';
    const CHARSET = 'utf-8';

    private $context;
    private $acessoService;

    private static $instance;

    public function __construct(array $userSettings = array())
    {
        $twig = new \Slim\Views\Twig();
        $userSettings = array_merge($userSettings, array(
            'debug' => NOVOSGA_DEV,
            'cache' => NOVOSGA_CACHE,
            'templates.path' => NOVOSGA_TEMPLATES,
            'view' => $twig,
        ));
        if (!$userSettings['debug']) {
            $twig->parserOptions = array(
                'cache' => $userSettings['cache'],
            );
        }
        parent::__construct($userSettings);

        $this->view()->set('version', self::VERSION);

        $this->view()->parserExtensions = array(
            new \Slim\Views\TwigExtension(),
            new \Twig_Extensions_Extension_I18n(),
            new Twig\Extensions(),
        );

        if ($userSettings['debug']) {
            $this->view()->parserExtensions[] = new \Twig_Extension_Debug();
        }

        $app = $this;
        $app->notFound(function () use ($app) {
            $app->render(NOVOSGA_TEMPLATES.'/error/404.html.twig');
        });

        $app->error(function (\Exception $e) use ($app) {
            $app->view()->set('exception', $e);
            $app->render(NOVOSGA_TEMPLATES.'/error/500.html.twig');
        });
    }

    /**
     * @return App
     */
    public static function create(array $settings = array())
    {
        if (!self::$instance) {
            self::$instance = new self($settings);
        }

        return self::$instance;
    }

    public static function isInstalled()
    {
        return DatabaseConfig::getInstance()->isIntalled();
    }

    public function prepare()
    {
        // i18n
        I18n::bind();
        $this->view()->set('lang', I18n::lang());

        $db = DatabaseConfig::getInstance();
        $db->setDev(NOVOSGA_DEV);

        $this->context = new Context($this, $db);
        $this->acessoService = new AcessoService();

        $this->add(new \Novosga\Slim\InstallMiddleware($this->context));
        $this->add(new \Novosga\Slim\AuthMiddleware($this->context));
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return AcessoService
     */
    public function getAcessoService()
    {
        return $this->acessoService;
    }

    public function gotoLogin()
    {
        $this->redirect($this->request()->getRootUri().'/login');
    }

    public function gotoHome()
    {
        $this->redirect($this->request()->getRootUri().'/home');
    }

    public function gotoModule()
    {
        $this->redirect($this->request()->getRootUri().'/modules/'.$this->getContext()->getModulo()->getChave());
    }

    public function moduleResource($moduleKey, $resource)
    {
        $filename = implode(DS, array(
            MODULES_PATH, implode(DS, explode('.', $moduleKey)), 'public', $resource, )
        );
        if (file_exists($filename)) {
            $mime = \Novosga\Util\FileUtils::mime($filename);
            header("Content-type: $mime");
            echo file_get_contents($filename);
            exit();
        } else {
            throw new Exception(sprintf('Resource not found: %s', $filename));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, $data = array(), $status = null)
    {
        $customTemplateDir = AppConfig::getInstance()->get('template.dir');
        if (!empty($customTemplateDir)) {
            array_unshift($this->view()->twigTemplateDirs, $customTemplateDir);
        }

        if (substr($template, 0, 1) === '/' || substr($template, 0, 2) === '..') {
            // defined a template outside the default twigTemplateDirs
            $dir = dirname($template);
            $this->view()->twigTemplateDirs[] = $dir;
            $template = basename($template);
        }
        // for security purposes allow only .html.twig files
        $ext = '.html.twig';
        if (substr($template, -strlen($ext)) !== $ext) {
            throw new Exception('Você está tentando exibir um arquivo de template inválido.');
        }

        return parent::render($template, $data, $status);
    }

    /**
     * @return \Novosga\Auth\AuthProviderFactory
     */
    public static function authenticationFactory()
    {
        $factory = null;
        $factoryClass = AppConfig::getInstance()->get('auth.factory');
        if (!empty($factoryClass) && class_exists($factoryClass)) {
            $factory = new $factoryClass();
        }
        if (!$factory || !($factory instanceof \Novosga\Auth\AuthProviderFactory)) {
            $factory = new \Novosga\Auth\DefaultAuthProviderFactory();
        }

        return $factory;
    }

    public static function defaultClientLanguage()
    {
        $lang = \Novosga\Util\I18n::locale();

        return current(explode('_', $lang));
    }

    /**
     * Retorna informacoes gerais sobre o sistema.
     */
    public static function info()
    {
        ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_end_clean();

        return $info;
    }
}
