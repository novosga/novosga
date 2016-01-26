<?php

namespace Novosga\Entity;

/**
 * OAuthClient
 */
class OAuthClient implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
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
        return [
            'id'          => $this->id,
            'secret'      => $this->secret,
            'redirectUri' => $this->redirectUri,
        ];
    }
}
