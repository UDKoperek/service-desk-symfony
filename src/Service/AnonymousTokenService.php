<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Cookie;

final class AnonymousTokenService
{
    private const COOKIE_NAME = 'anon_ticket_id';
    // 7 dni w sekundach
    private const COOKIE_LIFETIME = 604800; 
    
    public function __construct(
        private readonly RequestStack $requestStack
    ) {}
    
    public function getOrCreateToken(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        
        // 1. Sprawdź, czy ciasteczko już istnieje
        $token = $request?->cookies->get(self::COOKIE_NAME);
        
        if ($token === null) {
            // 2. Jeśli nie, generuj nowy token (UUID lub unikalny string)
            $token = bin2hex(random_bytes(16)); 
            
            // 3. Ustaw ciasteczko w odpowiedzi (będzie ustawione przy następnym renderowaniu/przekierowaniu)
            $cookie = Cookie::create(
                self::COOKIE_NAME,
                $token,
                time() + self::COOKIE_LIFETIME,
                '/', // Ścieżka (cała domena)
                null,
                true, // Secure
                true, // HttpOnly
            );
            
            // Dodaj ciasteczko do obiektu Response (to wymaga przechwycenia odpowiedzi w Event Listenerze,
            // ale prostszym sposobem jest użycie Response w Kontrolerze. Na razie użyjemy
            // tymczasowego miejsca w sesji, które będzie używane w Kontrolerze.)
            $this->requestStack->getSession()->set(self::COOKIE_NAME, $token);
            $this->requestStack->getSession()->set('new_cookie', $cookie);
        }
        
        return $token;
    }
}