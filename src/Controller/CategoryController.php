<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/category')]
final class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly Security $security,
    ) {
    }

    /**
     * @return Response
     */
    #[Route(name: 'app_category_index', methods: ['GET'])]
    public function index(): Response
    {

        $isAgentOrAdmin = $this->security->isGranted('ROLE_AGENT') || $this->security->isGranted('ROLE_ADMIN');
        if (!$isAgentOrAdmin) {
            throw new AccessDeniedException('Only Agents or Admins can view this page.');
        }

        return $this->render('category/index.html.twig', [
            'categories' => $this->categoryService->getAllCategories(),
        ]);
    }

    /**

     * @param Request
     * @return Response
     */
    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {

        $this->denyAccessUnlessGranted('CREATE_CATEGORY');

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->categoryService->createCategory($category);

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    /**
     * @param Category 
     * @return Response
     */
    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        
        $this->denyAccessUnlessGranted('SHOW_CATEGORY', $category);

        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @param Request
     * @param Category
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category): Response
    {

        $this->denyAccessUnlessGranted('EDIT_CATEGORY', $category);

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->categoryService->saveChanges();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    /**
     * Usuwa kategorię. Wymaga metody POST i walidacji CSRF.
     * Wymaga uprawnienia 'DELETE_CATEGORY' (ROLE_ADMIN).
     *
     * @param Request $request Aktualne żądanie HTTP.
     * @param Category $category Obiekt do usunięcia.
     * @return Response
     * * UWAGA STYL: Usunięto EntityManagerInterface z argumentów metody.
     */
    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Category $category): Response
    {


        $this->denyAccessUnlessGranted('DELETE_CATEGORY', $category); // POPRAWKA: Dodano $category jako subject


        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            // Delegowanie logiki usuwania do serwisu
            $this->categoryService->deleteCategory($category);
        }

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}