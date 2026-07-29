<?php
namespace App\NovosgaMidiaAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/", name: "novosga_midiaadmin_")]
class DefaultController extends AbstractController
{
    #[Route("/", name: "index", methods: ["GET", "POST"])]
    public function index(): Response
    {
        // Encapsula o arquivo raw index.php legado do midia-admin dentro do contexto do Symfony.
        // $isWrapped = true sinaliza para o arquivo interno que ele esta rodando seguro dentro do modulo,
        // e não por acesso direto malicioso.
        ob_start();
        $isWrapped = true;
        $currentUser = $this->getUser();
        include '/opt/novosga/public/midia-admin/index.php';
        $content = ob_get_clean();
        return new Response($content);
    }
}
