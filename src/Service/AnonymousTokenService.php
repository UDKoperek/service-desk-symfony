<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Cookie;

final class AnonymousTokenService
{
    private const COOKIE_NAME = 'anon_ticket_id';
    // 7 dni w sekundach
    private const COOKIE_LIFETIME = 604800; 
    private ?Cookie $newCookie = null;

    public function __construct(
        private readonly RequestStack $requestStack
    ) {}
    
    public function getOrCreateToken(): string
    {
        $request = $this->requestStack->getCurrentRequest(); 
        
        if (!$request) {
            // Kontynuacja błędu, jeśli Request jest niedostępny
            throw new \Exception('Brak obiektu Request w AnonymousTokenService.');
        }

        $token = $request->cookies->get(self::COOKIE_NAME);
        
        if ($token === null) {
            $token = bin2hex(random_bytes(16));
            
            $this->newCookie = Cookie::create(
                self::COOKIE_NAME,
                $token,
                time() + self::COOKIE_LIFETIME,
                '/',   // 1. Ścieżka na root — bezwzględnie
                null,  // 2. Domena — bezwzględnie na null
                true,
                true,  // 3. Secure — bezwzględnie na false (dla HTTP)
                
                Cookie::SAMESITE_LAX
            );
        }
        return $token;
    }
    public function getNewCookie(): ?Cookie
    {
        return $this->newCookie;
    }
}