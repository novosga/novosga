<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;

/**
 * OAuthClient
 */
class OAuthClient extends BaseClient implements \JsonSerializable
{
    /**
     * @var string
     */
    private $description;
    
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
        
    public function jsonSerialize()
    {
        return [
            'id'           => $this->getId(),
            'description'  => $this->getDescription(),
            'publicId'     => $this->getPublicId(),
            'randomId'     => $this->getRandomId(),
            'redirectUris' => $this->getRedirectUris(),
            'secret'       => $this->getSecret(),
        ];
    }
}
