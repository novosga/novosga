<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use Novosga\Http\Envelope;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApiController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route('/admin/api', name: 'admin_api_')]
class ApiController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/api/index.html.twig', [
            'tab' => 'api',
        ]);
    }

    #[Route('/oauth-clients', name: 'clients', methods: ['GET'])]
    public function oauthClients(EntityManagerInterface $em, ClientManagerInterface $clientManager): Response
    {
        $envelope = new Envelope();

        $clients = $clientManager->list(null);

        $envelope->setData($clients);

        return $this->json($envelope);
    }

    #[Route('/oauth-clients', name: 'newclient', methods: ['POST'])]
    public function newOauthClient(Request $request, ClientManagerInterface $clientManager): Response
    {
        $envelope = new Envelope();

        $json = json_decode($request->getContent());
        $description = isset($json->description) ? trim($json->description) : '';

        if (strlen($description) > 30) {
            $description = substr($description, 0, 30);
        }

        $client = new Client(
            name: $description,
            identifier: hash('md5', random_bytes(16)),
            secret: hash('sha512', random_bytes(32)),
        );
        $client->setGrants(new Grant('token'), new Grant('password'), new Grant('refresh_token'));

        $clientManager->save($client);

        $envelope->setData($client);

        return $this->json($envelope);
    }

    #[Route('/oauth-clients/{identifier}', name: 'removeclient', methods: ['DELETE'])]
    public function removeOauthClient(ClientManagerInterface $clientManager, string $identifier): Response
    {
        $envelope = new Envelope();

        $client = $clientManager->find($identifier);
        if ($client !== null) {
            $clientManager->remove($client);
            $envelope->setData($client);
        }

        return $this->json($envelope);
    }
}
