<?php
namespace App\NovosgaGestorUnidadeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/", name: "novosga_gestorunidade_")]
class DefaultController extends AbstractController
{
    #[Route("/", name: "index", methods: ["GET", "POST"])]
    public function index(): Response
    {
        ob_start();
        $isWrapped = true;
        $currentUser = $this->getUser();
        include '/opt/novosga/public/gestor-unidade/index.php';
        $content = ob_get_clean();
        return new Response($content);
    }
}
