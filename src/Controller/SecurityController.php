<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // todo: remove the commentes
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('appli_index');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: "/log", name: 'appli_log', methods: ['GET'])]
    public function log(): Response
    {
        // todo: remove the commentes
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('appli_index');
        // }
        return $this->render('security/log.html.twig', [
            'title' => 'Connexion / Inscription'
        ]);
    }
}
