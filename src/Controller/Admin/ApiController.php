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

use Novosga\Http\Envelope;
use App\Entity\OAuthClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * ApiController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Route("/admin/api")
 */
class ApiController extends Controller
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
     * @Route("/oauth-clients", name="admin_api_clients")
     * @Method("GET")
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
     * @Route("/oauth-clients", name="admin_api_newclient")
     * @Method("POST")
     */
    public function newOauthClient(Request $request)
    {
        $envelope = new Envelope();
        
        $json = json_decode($request->getContent());
        $uri = isset($json->redirectUri) ? trim($json->redirectUri) : '';

        $clientManager = $this->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();
        if (!empty($uri)) {
            $client->setRedirectUris([
                $uri
            ]);
        }
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
     * @Route("/oauth-clients/{id}", name="admin_api_removeclient")
     * @Method("DELETE")
     */
    public function removeOauthClient(Request $request, OAuthClient $client)
    {
        $envelope = new Envelope();
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($client);
        $em->flush();

        $envelope->setData($client);

        return $this->json($envelope);
    }
}
