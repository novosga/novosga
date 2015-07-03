<?php

namespace Novosga\Auth;

use Novosga\Util\Arrays;

/**
 * LDAP authentication provider.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LdapProvider extends DatabaseProvider
{
    const DEFAULT_PORT = 389;

    private $host;
    private $port = self::DEFAULT_PORT;
    private $baseDn;
    private $loginAttribute;
    private $username;
    private $password;
    private $filter;

    public function init(array $config)
    {
        if (!empty($config)) {
            parent::init($config);
            $this->host = Arrays::value($config, 'host');
            $this->baseDn = Arrays::value($config, 'baseDn');
            $this->loginAttribute = Arrays::value($config, 'loginAttribute');
            $this->username = Arrays::value($config, 'username');
            $this->password = Arrays::value($config, 'password');
            $this->filter = Arrays::value($config, 'filter');
        }
    }

    /**
     * Conecta ao servidor LDAP com o usuário e senha configurado e depois verifica
     * se existe o usuário e senha informados por parâmetro.
     *
     * @param type $username
     * @param type $password
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function auth($username, $password)
    {
        if ($this->host) {
            $message = _('Não foi possível conectar ao servidor LDAP. Favor verificar se as configurações estão corretas.');
            list($conn, $bind) = $this->connect($this->username, $this->password);
            if ($conn && $bind) {
                if (!empty($this->filter)) {
                    $filter = ($this->filter[0] != '(') ? '('.$this->filter.')' : $this->filter;
                    $filter = sprintf('(&%s(%s=%s))', $filter, $this->loginAttribute, $username);
                } else {
                    $filter = sprintf('(%s=%s)', $this->loginAttribute, $username);
                }
                $search = @ldap_search($conn, $this->baseDn, $filter);
                if ($search) {
                    $result = @ldap_get_entries($conn, $search);
                    if ($result && $result['count'] == 1) {
                        $user = $result[0];
                        $bind = @ldap_bind($conn, $user['dn'], $password);
                        if ($bind) {
                            return $this->createUser($username, $user);
                        }
                    }
                } else {
                    throw new \Exception($message);
                }
            } else {
                throw new \Exception($message);
            }
        }

        return parent::auth($username, $password);
    }

    public function test()
    {
        $this->connect($this->username, $this->password);
    }

    private function connect($user, $pwd)
    {
        if (!function_exists('ldap_connect')) {
            throw new \Exception(_('Extensão LDAP não disponível no servidor.'));
        }
        $conn = @ldap_connect($this->host, $this->port);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bind = @ldap_bind($conn, $user, $pwd);
        if (!$bind) {
            throw new \Exception('Não foi possível conectar ao servidor LDAP. Verifique se os dados estão corretos.');
        }

        return array($conn, $bind);
    }

    private function createUser($username, $ldapUser)
    {
        $query = $this->em->createQuery("SELECT u FROM Novosga\Model\Usuario u WHERE u.login = :login");
        $query->setParameter('login', $username);
        $user = $query->getOneOrNullResult();
        if (!$user) {
            $user = new \Novosga\Model\Usuario();
            $user->setLogin($username);
            $nome = (isset($ldapUser['givenname'])) ? $ldapUser['givenname'][0] : $username;
            $user->setNome($nome);
            $sobrenome = (isset($ldapUser['sn'])) ? $ldapUser['sn'][0] : '';
            $user->setSobrenome($sobrenome);
            $user->setSenha('');
            $user->setStatus(1);
            $user->setSessionId(session_id());
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }
}
