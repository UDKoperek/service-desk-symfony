<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Kontroler obsługujący logikę związaną z bezpieczeństwem (logowanie/wylogowanie).
 */
class SecurityController extends AbstractController
{
    /**
     * Wyświetla formularz logowania i obsługuje błędy uwierzytelniania.
     *
     * @param AuthenticationUtils $authenticationUtils Serwis dostarczający ostatni błąd uwierzytelniania.
     * @return Response
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Jeśli użytkownik jest już zalogowany, można go przekierować, 
        // aby uniknąć ponownego wyświetlania formularza logowania.
        if ($this->getUser()) {
            // Zmień 'app_ticket_index' na docelową stronę po zalogowaniu
            return $this->redirectToRoute('app_ticket_index'); 
        }

        // Pobiera błąd logowania (jeśli istnieje) z sesji.
        $error = $authenticationUtils->getLastAuthenticationError();

        // Pobiera ostatnią nazwę użytkownika (np. email), którą wprowadzono w formularzu.
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Ta metoda nigdy nie zostanie wykonana.
     * Jest wymagana wyłącznie do definicji ścieżki (Route) w Symfony, 
     * która zostanie przechwycona przez firewall w konfiguracji security.yaml.
     * * @throws \LogicException
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Ten wyjątek jest celowy i oznacza, że logika wylogowania 
        // jest delegowana do mechanizmu bezpieczeństwa Symfony.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}