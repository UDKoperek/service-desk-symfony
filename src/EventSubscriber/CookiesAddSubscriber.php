<?php

namespace App\EventSubscriber;

use App\Service\AnonymousTokenService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CookiesAddSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AnonymousTokenService $tokenService
    ) {}

    public function onKernelResponse(ResponseEvent $event): void
    {
        $cookie = $this->tokenService->getNewCookie();

        if ($cookie) {
            $event->getResponse()->headers->setCookie($cookie);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}