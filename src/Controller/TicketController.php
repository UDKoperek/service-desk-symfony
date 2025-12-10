<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TicketRepository; 
use App\Service\TicketService;
use App\Service\AnonymousTokenService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/ticket')]
final class TicketController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly TicketService $TicketService,
        private readonly AnonymousTokenService $tokenService,
        private readonly TicketRepository $ticketRepository,
    ) {
    }

    #[Route(name: 'app_ticket_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->security->getUser();
        $authorId = null;
        $sessionToken = null;

        if ($user) {
            $authorId = $user->getId();
        } 
        // 2. Sprawdzenie, czy użytkownik jest ANONIMOWY (i czy ma aktywną sesję)
        elseif (!$user) {
            $sessionToken = $this->tokenService->getOrCreateToken();
        }

        // Przekazanie kryteriów filtrowania do Repository
        $tickets = $this->ticketRepository->findTicketsForUser($authorId, $sessionToken);

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/new', name: 'app_ticket_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $ticket = new Ticket();
        $user = $this->security->getUser();

        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {
            
            // 2. Cała logika jest w serwisie!
            $this->TicketService->processNewTicket($ticket); 
            
            // ... powiadomienie i przekierowanie
            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ticket_show', methods: ['GET'])]
    public function show(Ticket $ticket): Response
    {
        $this->denyAccessUnlessGranted('SHOW', $ticket);
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ticket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $ticket);
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $ticket);
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
    }
}
