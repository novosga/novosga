<?php

namespace Novosga\Model;

/**
 * @Entity
 * @Table(name="oauth_clients")
 */
class OAuthClient implements \JsonSerializable
{
    /**
     * @Id
     * @Column(type="string", name="client_id", length=80, nullable=false, unique=true)
     */
    protected $id;

    /**
     * @Column(type="string", name="client_secret", length=80, nullable=false)
     */
    protected $secret;

    /**
     * @Column(type="string", name="redirect_uri", length=2000, nullable=false)
     */
    protected $redirectUri;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'secret' => $this->secret,
            'redirectUri' => $this->redirectUri,
        );
    }
}
