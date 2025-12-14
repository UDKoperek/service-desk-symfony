<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Ticket;
use App\Form\TicketType;
use App\Form\CommentType; 
use App\Service\TicketService;
use App\Service\CommentService;
use App\Service\AnonymousTokenService;
use App\Security\Voter\AbstractTicketVoter;
use App\Security\Voter\CommentVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ticket')]
final class TicketController extends AbstractController
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly CommentService $commentService, 
        private readonly AnonymousTokenService $anonymousTokenService,
        private readonly Security $security,
    ) {
    }

    #[Route(name: 'app_ticket_index', methods: ['GET'])]
    public function index(): Response
    {
        $tickets = $this->ticketService->getTicketsForCurrentUser();

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/new', name: 'app_ticket_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $ticket = new Ticket();

        $isAgentorAdmin = $this->security->isGranted('ROLE_AGENT') || $this->security->isGranted('ROLE_ADMIN');
        $formOptions = [
            'status_disabled' => $isAgent,
            'priority_disabled' => $isAgent,
        ];
        $form = $this->createForm(TicketType::class, $ticket, $formOptions);

        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->ticketService->processNewTicket($ticket); 
            
            // Zapisz token anonimowego użytkownika do ciasteczka, jeśli został wygenerowany
            $response = $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
            
            $newCookie = $this->anonymousTokenService->getNewCookie();
            if ($newCookie) {
                $response->headers->setCookie($newCookie);
            }

            return $response;
        }

        return $this->render('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ticket_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Ticket $ticket): Response
    {
        $this->denyAccessUnlessGranted(AbstractTicketVoter::SHOW, $ticket);
        
        $comment = new Comment();

        $canComment = $this->isGranted(CommentVoter::COMMENT_ON_TICKET, $ticket);

        $commentForm = $this->createForm(CommentType::class, $comment, [
            'data_class' => Comment::class,
            'disabled' => !$canComment, 
        ]);
        
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            
            $this->denyAccessUnlessGranted(CommentVoter::COMMENT_ON_TICKET, $ticket);

            $content = $comment->getContent(); 

            $user = $this->getUser();

            $anonymousToken = null;

            if (!($user instanceof User)) {
                // Użytkownik jest anonimowy, pobieramy token z ciasteczka
                $anonymousToken = $this->anonymousTokenService->getOrCreateToken();
                // Ustawiamy $user na null
                $user = null; 
            }

            $this->commentService->createAndAddComment($comment, $ticket, $user, $anonymousToken);

            $this->addFlash('success', 'Komentarz został dodany pomyślnie!');

            return $this->redirectToRoute('app_ticket_show', ['id' => $ticket->getId()]);
        }

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
            'commentForm' => $commentForm->createView(),
            'canComment' => $canComment, 
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ticket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket): Response
    {
        $this->denyAccessUnlessGranted(AbstractTicketVoter::EDIT, $ticket);

        $isAgentorAdmin = $this->security->isGranted('ROLE_AGENT') || $this->security->isGranted('ROLE_ADMIN');
        $formOptions = [
            'status_disabled' => $isAgentorAdmin,
            'priority_disabled' => $isAgentorAdmin,
        ];

        $form = $this->createForm(TicketType::class, $ticket, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->ticketService->saveChanges();

            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket): Response
    {
        $this->denyAccessUnlessGranted(AbstractTicketVoter::DELETE, $ticket);
        
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->getPayload()->getString('_token'))) {
            $this->ticketService->deleteTicket($ticket);
        }

        return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
    }
}