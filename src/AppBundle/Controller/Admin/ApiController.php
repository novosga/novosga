<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller\Admin;

use Novosga\Context;
use Novosga\Http\Envelope;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
    public function indexAction(Request $request)
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
     * @Route("/new-oauth-client")
     */
    public function newOauthClientAction(Request $request)
    {
        $envelope = new Envelope();
        try {
            $clientManager = $this->get('fos_oauth_server.client_manager.default');
            $client = $clientManager->createClient();
            $client->setRedirectUris(['http://www.example.com']);
            $client->setAllowedGrantTypes(['token', 'password', 'refresh_token']);
            $clientManager->updateClient($client);
            
            $envelope->setData($client);

        } catch (\Exception $e) {
            $envelope
                    ->setSuccess(false)
                    ->setMessage($e->getMessage());
        }

        return $this->json($envelope);
    }

    public function get_oauth_client(Context $context)
    {
        $envelope = new Envelope();
        $client_id = $context->request()->get('client_id');
        $query = $em->createQuery('SELECT e FROM Novosga\Entity\OAuthClient e WHERE e.id = :client_id');
        $query->setParameter('client_id', $client_id);
        $client = $query->getOneOrNullResult();
        if ($client) {
            $data = $client->jsonSerialize();
            $envelope->setData($data);
        }

        return $this->json($envelope);
    }

    public function get_all_oauth_client(Context $context)
    {
        $envelope = new Envelope();
        $rs = $em->getRepository('Novosga\Entity\OAuthClient')->findBy([], ['id' => 'ASC']);
        $data = [];
        foreach ($rs as $client) {
            $data[] = $client->jsonSerialize();
        }
        $envelope->setData($data);

        return $this->json($envelope);
    }

    public function delete_oauth_client(Context $context)
    {
        $envelope = new Envelope();
        $client_id = $context->request()->post('client_id');
        $this->delete_auth_client_by_id($client_id);

        return $this->json($envelope);
    }

    private function delete_auth_client_by_id($client_id)
    {
        $query = $em->createQuery('DELETE Novosga\Entity\OAuthClient e WHERE e.id = :client_id');
        $query->setParameter('client_id', $client_id);
        $query->execute();
    }
}
