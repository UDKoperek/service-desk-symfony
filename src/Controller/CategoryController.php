<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository; // Nadal wymagany, jeśli CategoryType używa Category::class
use App\Service\CategoryService; // Używamy serwisu do logiki biznesowej/repozytoryjnej
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface; // UWAGA: Ten import jest tu zbędny, jeśli używamy Serwisu

/**
 * Kontroler do zarządzania kategoriami (CRUD).
 * Deleguje operacje bazodanowe do CategoryService.
 */
#[Route('/category')]
final class CategoryController extends AbstractController
{
    /**
     * Wstrzyknięcie CategoryService w konstruktorze.
     */
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {
    }

    /**
     * Wyświetla listę wszystkich kategorii. 
     * Dostęp publiczny (nie wymaga specjalnych uprawnień).
     *
     * @return Response
     */
    #[Route(name: 'app_category_index', methods: ['GET'])]
    public function index(): Response
    {
        // Pobranie danych za pomocą serwisu
        return $this->render('category/index.html.twig', [
            'categories' => $this->categoryService->getAllCategories(),
        ]);
    }

    /**
     * Tworzy nową kategorię. Obsługuje wyświetlenie i przetworzenie formularza.
     * Wymaga uprawnienia 'CREATE_CATEGORY' (ROLE_ADMIN).
     *
     * @param Request $request Aktualne żądanie HTTP.
     * @return Response
     */
    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Kontrola dostępu: Wymagamy uprawnienia CREATE_CATEGORY. Subject to null, bo tworzymy nowy obiekt.
        $this->denyAccessUnlessGranted('CREATE_CATEGORY');

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Delegowanie logiki zapisu do serwisu
            $this->categoryService->createCategory($category);

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    /**
     * Wyświetla szczegóły pojedynczej kategorii.
     * Wymaga uprawnienia 'SHOW_CATEGORY'.
     *
     * @param Category $category Obiekt kategorii wstrzyknięty przez ParamConverter.
     * @return Response
     */
    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        // Kontrola dostępu: Wymagamy uprawnienia SHOW_CATEGORY dla danego obiektu
        $this->denyAccessUnlessGranted('SHOW_CATEGORY', $category); // Styl: Brak średnika na końcu linii w oryginalnym kodzie

        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * Edytuje istniejącą kategorię. Obsługuje wyświetlenie i przetworzenie formularza.
     * Wymaga uprawnienia 'EDIT_CATEGORY' (ROLE_ADMIN).
     *
     * @param Request $request Aktualne żądanie HTTP.
     * @param Category $category Obiekt do edycji.
     * @return Response
     * * UWAGA STYL: Usunięto EntityManagerInterface z argumentów metody, ponieważ logika bazodanowa jest w Serwisie.
     */
    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category): Response
    {
        // Kontrola dostępu: Wymagamy uprawnienia EDIT_CATEGORY
        $this->denyAccessUnlessGranted('EDIT_CATEGORY', $category); // POPRAWKA: Dodano $category jako subject

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Delegowanie zapisu zmian do serwisu. Używamy saveChanges (flush)
            $this->categoryService->saveChanges(); // POPRAWKA: Poprawiono literówkę z $this->categorySerivce

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
    public function delete(Request $request, Category $category): Response
    {
        // Kontrola dostępu: Wymagamy uprawnienia DELETE_CATEGORY
        $this->denyAccessUnlessGranted('DELETE_CATEGORY', $category); // POPRAWKA: Dodano $category jako subject

        // Ręczna weryfikacja tokena CSRF dla akcji usuwania (POST)
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->getString('_token'))) {
            // Delegowanie logiki usuwania do serwisu
            $this->categoryService->deleteCategory($category);
        }

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}