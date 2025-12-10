<?php

namespace App\EventSubscriber;

use App\Service\AnonymousTokenService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseCookieSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AnonymousTokenService $tokenService
    ) {}

    // 🔑 Metoda, która zostanie uruchomiona na zdarzeniu Response
    public function onKernelResponse(ResponseEvent $event): void
    {
        // 1. Sprawdź, czy serwis wygenerował nowe ciasteczko w tym żądaniu
        $cookie = $this->tokenService->getNewCookie();

        if ($cookie) {
            // 2. Dodaj ciasteczko do nagłówków odpowiedzi
            $event->getResponse()->headers->setCookie($cookie);
        }
    }

    // 🔑 Rejestracja subskrybenta
    public static function getSubscribedEvents(): array
    {
        // Uruchomienie onKernelResponse tuż przed wysłaniem odpowiedzi
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}