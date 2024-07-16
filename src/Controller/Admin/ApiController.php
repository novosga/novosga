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

use App\Entity\OAuthAccessToken;
use App\Entity\OAuthClient;
use App\Entity\OAuthRefreshToken;
use Novosga\Http\Envelope;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApiController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[Route("/admin/api", name: "admin_api_")]
class ApiController extends AbstractController
{
    #[Route("/", name: "index")]
    public function index(): Response
    {
        return $this->render('admin/api/index.html.twig', [
            'tab' => 'api',
        ]);
    }

    #[Route("/oauth-clients", name: "oauth-clients", methods: ["GET"])]
    public function oauthClients(EntityManagerInterface $em): Response
    {
        $envelope = new Envelope();

        $clients = $em
            ->getRepository(OAuthClient::class)
            ->findAll();

        $envelope->setData($clients);

        return $this->json($envelope);
    }

    #[Route("/oauth-clients", name: "newclient", methods: ["POST"])]
    public function newOauthClient(Request $request, /*ClientManagerInterface $clientManager*/): Response
    {
        $envelope = new Envelope();

        $json = json_decode($request->getContent());
        $description = isset($json->description) ? trim($json->description) : '';
        
        if (strlen($description) > 30) {
            $description = substr($description, 0, 30);
        }
        
        $client = $clientManager->createClient();
        $client->setDescription($description);
        $client->setAllowedGrantTypes(['token', 'password', 'refresh_token']);
        $clientManager->updateClient($client);

        $envelope->setData($client);

        return $this->json($envelope);
    }

    #[Route("/oauth-clients/{id}", name: "removeclient", methods: ["DELETE"])]
    public function removeOauthClient(Request $request, EntityManagerInterface $em, /*OAuthClient $client*/): Response
    {
        $envelope = new Envelope();

        $em->beginTransaction();
        $em
            ->createQueryBuilder()
            ->delete(OAuthRefreshToken::class, 'e')
            ->where('e.client = :client')
            ->getQuery()
            ->execute([ 'client' => $client ]);

        $em
            ->createQueryBuilder()
            ->delete(OAuthAccessToken::class, 'e')
            ->where('e.client = :client')
            ->getQuery()
            ->execute([ 'client' => $client ]);

        $em->remove($client);
        $em->commit();
        $em->flush();

        $envelope->setData($client);

        return $this->json($envelope);
    }
}
