<?php

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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;

/**
 * ApiController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/api")
 */
class ApiController extends AbstractController
{
    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="admin_api_index")
     */
    public function index(Request $request)
    {
        return $this->render('admin/api/index.html.twig', [
            'tab' => 'api',
        ]);
    }

    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/oauth-clients", name="admin_api_clients", methods={"GET"})
     */
    public function oauthClients(Request $request)
    {
        $envelope = new Envelope();
        
        $clients = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(OAuthClient::class)
            ->findAll();
            
        $envelope->setData($clients);

        return $this->json($envelope);
    }

    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/oauth-clients", name="admin_api_newclient", methods={"POST"})
     */
    public function newOauthClient(Request $request, ClientManagerInterface $clientManager)
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

    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/oauth-clients/{id}", name="admin_api_removeclient", methods={"DELETE"})
     */
    public function removeOauthClient(Request $request, OAuthClient $client)
    {
        $envelope = new Envelope();
        
        $em = $this->getDoctrine()->getManager();
        $em->transactional(function ($em) use ($client) {
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
            $em->flush();
        });

        $envelope->setData($client);

        return $this->json($envelope);
    }
}
