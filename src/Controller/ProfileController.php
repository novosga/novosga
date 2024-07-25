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

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\ProfileType;
use Exception;
use Novosga\Http\Envelope;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
    ): Response {
        $user = $this->getUser();
        $form = $this
            ->createForm(ProfileType::class, $user)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('Perfil atualizado com sucesso!'));

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/password', methods: ['POST'])]
    public function password(
        Request $request,
        EntityManagerInterface $em,
        PasswordHasherFactoryInterface $factory,
    ): Response {
        $envelope = new Envelope();

        $data = json_decode($request->getContent());
        $current = $data->atual;
        $password = $data->senha;
        $confirmation = $data->confirmacao;
        /** @var Usuario */
        $user = $this->getUser();
        $encoder = $factory->getPasswordHasher($user);

        if (strlen($password) < 6) {
            throw new Exception(sprintf('A nova senha precisa ter no mínimo %s caraceteres.', 6));
        }

        if ($password !== $confirmation) {
            throw new Exception('A nova senha e a confirmação da senha não conferem.');
        }

        if (!$encoder->verify($user->getPassword(), $current)) {
            throw new Exception('A senha atual informada não confere.');
        }

        $encoded = $encoder->hash($password);
        $user->setSenha($encoded);

        $em->persist($user);
        $em->flush();

        return $this->json($envelope);
    }
}
