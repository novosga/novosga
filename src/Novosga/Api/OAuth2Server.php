<?php

namespace Novosga\Api;

use Exception;
use Doctrine\ORM\EntityManager;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Request;
use OAuth2\Server;

/**
 * OAuth2Server.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class OAuth2Server extends Server
{
    private $storage;
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->storage = new OAuth2Storage($em);
        parent::__construct($this->storage, array(
            'www_realm' => 'NovoSGA',
        ));
        $this->addGrantType(new ClientCredentials($this->storage));
        $this->addGrantType(new AuthorizationCode($this->storage));
        $this->addGrantType(new RefreshToken($this->storage, array(
            'always_issue_new_refresh_token' => true,
        )));
        $this->addGrantType(new UserCredentials($this->storage));
        $this->em = $em;
    }

    public function checkAccess()
    {
        if (!$this->verifyResourceRequest(Request::createFromGlobals())) {
            throw new Exception('Permission denied', 403);
        }
    }

    /**
     * @return Novosga\Model\Usuario
     */
    public function user()
    {
        $token = $this->getAccessTokenData(Request::createFromGlobals());
        if (isset($token['user_id'])) {
            $rs = $this->em->getRepository('Novosga\Model\Usuario')->findBy(array('login' => $token['user_id']));
            if (sizeof($rs)) {
                return $rs[0];
            }
        }

        return;
    }
}
