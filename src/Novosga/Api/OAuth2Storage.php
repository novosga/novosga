<?php
namespace Novosga\Api;

use Novosga\Security;
use OAuth2\Storage\Pdo;

/**
 * OAuth2Storage
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class OAuth2Storage extends Pdo {
    
    protected function checkPassword($user, $password)
    {
        return $user['senha'] == Security::passEncode($password);
    }
    
    public function getUser($username)
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT * from usuarios where login=:username'));
        $stmt->execute(array('username' => $username));

        if (!$userInfo = $stmt->fetch()) {
            return false;
        }

        // the default behavior is to use "username" as the user_id
        return array_merge(array('user_id' => $username), $userInfo);
    }

    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
    }
    
}
