<?php
namespace core\auth;

/**
 * Database Authentication
 *
 * @author rogeriolino
 */
class DatabaseAuthentication extends Authentication {

    public function init(array $config) {
    }

    /**
     * Verifica o usuário e senha na tabela de usuarios
     * @param type $username
     * @param type $password
     * @return boolean
     */
    public function auth($username, $password) {
        $em = \core\db\DB::getEntityManager();
        $usuarioRepository = $em->getRepository('\core\model\Usuario');
        try {
            $user = $usuarioRepository->findOneByLogin($username);
            if ($user) {
                if (\core\Security::passCheck($password, $user->getSenha())) {
                    return $user;
                }
            }
        } catch (\Doctrine\ORM\NoResultException $e) {
        }

        return false;
    }

    public function test() {
        return true;
    }

}
