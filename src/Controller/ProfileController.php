<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Form\ProfileType;
use Exception;
use Novosga\Http\Envelope;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    /**
     * @Route("/", name="profile_index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user, [
            'action' => $this->generateUrl('profile_update'),
        ]);
        
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/", name="profile_update", methods={"POST"})
     */
    public function update(Request $request, TranslatorInterface $translator)
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($user);
            $em->flush();
            
            $this->addFlash('success', $translator->trans('Perfil atualizado com sucesso!'));
        }
        
        return $this->redirectToRoute('profile_index');
    }

    /**
     * @Route("/password", methods={"POST"})
     */
    public function password(Request $request, EncoderFactoryInterface $factory)
    {
        $envelope = new Envelope();
        
        $data         = json_decode($request->getContent());
        $current      = $data->atual;
        $password     = $data->senha;
        $confirmation = $data->confirmacao;
        $user         = $this->getUser();
        $salt         = $user->getSalt();
        $encoder      = $factory->getEncoder($user);
        
        if (!$encoder->isPasswordValid($user->getPassword(), $current, $salt)) {
            throw new Exception('A senha atual informada não confere.');
        }
        
        if (strlen($password) < 6) {
            throw new Exception(sprintf('A nova senha precisa ter no mínimo %s caraceteres.', 6));
        }

        if ($password !== $confirmation) {
            throw new Exception('A nova senha e a confirmação da senha não conferem.');
        }

        $encoded = $encoder->encodePassword($password, $salt);
        $user->setSenha($encoded);

        $em = $this->getDoctrine()->getManager();
        $em->merge($user);
        $em->flush();

        return $this->json($envelope);
    }
}
