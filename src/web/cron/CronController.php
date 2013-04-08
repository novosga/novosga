<?php
namespace cron;

use \core\SGA;
use \core\SGAContext;
use \core\http\AjaxResponse;
use \core\controller\SGAController;
use \core\business\AtendimentoBusiness;
use \core\model\util\UsuarioSessao;
use \core\db\DB;

/**
 * CronController
 *
 * @author rogeriolino
 *
 */
class CronController extends SGAController {

    protected function createView() {
        require_once(__DIR__ . '/CronView.php');
        return new CronView();
    }

    public function index(SGAContext $context) {
        exit();
    }

        public function reiniciar_senhas(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            // verificando usuario e token de seguranca
            $em = DB::getEntityManager();
            $login = $context->getParameter('login');
            $query = $em->createQuery("SELECT e FROM \core\model\Usuario e WHERE e.login = :login");
            $query->setParameter('login', $login);
            $query->setMaxResults(1);
            $usuario = $query->getOneOrNullResult();
            if (!$usuario) {
                throw new \Exception(_('Usuário inválido'));
            }
            $token = $context->getParameter('token');
            if (self::checkToken($token, $usuario->getLogin(), $usuario->getSenha())) {
                throw new \Exception(_('Token de segurança inválido'));
            }
            // acumulando atendimentos
            AtendimentoBusiness::acumularAtendimentos();
            $response->success = true;
            $response->data['usuario'] = $usuario->getLogin();
            $response->data['timestamp'] = \core\util\DateUtil::milis();
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
    }

    /**
     * Gera um token baseado em um hash seguro e codificado
     * usando base64 a partir do login e senha do usuário.
     *
     * @param  string $login
     * @param  string $senha
     * @return string
     */
    private static function token($login, $senha) {
        return base64_encode(\core\Security::hash($login . ':' . $senha));
    }

    /**
     * Verifica se $token pode ser gerado com os mesmos dados
     * passados inicialmente ($login e $senha)
     *
     * @param  string $token Token anteriormente gerado, codificado em base64
     * @param  string $login Login do usuário
     * @param  string $senha Hash da senha do usuário
     * @return boolean
     */
    private static function checkToken($token, $login, $senha) {
        $token = base64_decode($token);
        return ($token == \core\Security::hash($login . ':' . $senha, $token));
    }

    public static function cronUrl($page, UsuarioSessao $usuario) {
        $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $url = current(explode('?', $url));
        $url = "$protocol://$url?" . SGA::K_CRON . '&' . SGA::K_PAGE . '=' . $page;
        $url .= '&login=' . $usuario->getLogin() . '&token=' . self::token($usuario->getLogin(), $usuario->getSenha());
        return $url;
    }
}
