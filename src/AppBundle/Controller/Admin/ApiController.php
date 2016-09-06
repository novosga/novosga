<?php

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
        return $this->render('admin/api.html.twig', [
            'tab' => 'api',
        ]);
    }

    public function add_oauth_client(Context $context)
    {
        $envelope = new Envelope();
        try {
            if (!$context->request()->isPost()) {
                throw new Exception(_('Somente via POST'));
            }
            $client_id = $context->request()->post('client_id');
            $client_secret = $context->request()->post('client_secret');
            $redirect_uri = $context->request()->post('redirect_uri');
            // apaga se ja existir
            $this->delete_auth_client_by_id($client_id);
            // insere novo cliente
            $client = new \Novosga\Entity\OAuthClient();
            $client->setId($client_id);
            $client->setSecret($client_secret);
            $client->setRedirectUri($redirect_uri);

            $em->persist($client);
            $em->flush();

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
