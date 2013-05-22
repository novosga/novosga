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
        $query = $em->createQuery("SELECT u FROM core\model\Usuario u WHERE u.login = :login");
        $query->setParameter('login', $username);
        try {
            $user = $query->getSingleResult();
            if ($user) {
                if ($user->getSenha() == \core\Security::passEncode($password)) {
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
