<?php
namespace core\auth;

/**
 * LDAP Authentication
 *
 * @author rogeriolino
 */
class LdapAuthentication extends Authentication {
    
    const DEFAULT_PORT = 389;
    
    private $host;
    private $port = self::DEFAULT_PORT;
    private $baseDn;
    private $loginAttribute;
    private $username;
    private $password;
    
    public function init(array $auth = null) {
        if ($auth && isset($auth['ldap'])) {
            $ldap = $auth['ldap'];
            $this->host = $ldap['host'];
            $this->baseDn = $ldap['baseDn'];
            $this->loginAttribute = $ldap['loginAttribute'];
            $this->username = $ldap['username'];
            $this->password = $ldap['password'];
        }
    }
    
    /**
     * Conecta ao servidor LDAP com o usuário e senha configurado e depois verifica
     * se existe o usuário e senha informados por parâmetro.
     * @param type $username
     * @param type $password
     * @return boolean
     * @throws \Exception
     */
    public function auth($username, $password) {
        if ($this->host) {
            list($conn, $bind) = $this->connect($this->username, $this->password);
            if ($conn && $bind) {
                $filter = sprintf('(&(objectClass=user)(%s=%s))', $this->loginAttribute, $username);
                $search = ldap_search($conn, $this->baseDn, $filter);
                $result = ldap_get_entries($conn, $search);
                if ($result['count'] == 1) {
                    $user = $result[0];
                    $bind = @ldap_bind($conn, $user['dn'], $password);
                    if ($bind) {
                        return $this->createUser($username, $user);
                    }
                }
            } else {
                throw new \Exception(_('Não foi possível conectar ao servidor LDAP. Favor verificar se as configurações estão corretas.'));
            }
        }
        return false;
    }
    
    private function connect($user, $pwd) {
        $bind = null;
        $conn = @ldap_connect($this->host, $this->port);
        if ($conn) {
            $bind = @ldap_bind($conn, $user, $pwd);
        }
        return array($conn, $bind);
    }
    
    private function createUser($username, $ldapUser) {
        $em = \core\db\DB::getEntityManager();
        $query = $em->createQuery("SELECT u FROM core\model\Usuario u WHERE u.login = :login");
        $query->setParameter('login', $username);
        $user = $query->getOneOrNullResult();
        if (!$user) {
            $user = new \core\model\Usuario();
            $user->setLogin($username);
            $user->setNome($ldapUser['givenname'][0]);
            $user->setSobrenome($ldapUser['sn'][0]);
            $user->setSenha('');
            $user->setStatus(1);
            $user->setSessionId(session_id());
            $em->persist($user);
            $em->flush();
        }
        return $user;
    }
    
}
