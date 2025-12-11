<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CategoryService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * Zwraca listę wszystkich kategorii.
     */
    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    /**
     * Zapisuje nową kategorię w bazie danych.
     */
    public function createCategory(Category $category): void
    {
        // Używamy persist, bo to może być nowa encja
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    /**
     * Zapisuje zmiany w istniejącej kategorii (flush).
     */
    public function saveChanges(): void
    {
        // Encja jest już śledzona przez Doctrine po wczytaniu, wystarczy flush
        $this->entityManager->flush();
    }

    /**
     * Usuwa kategorię z bazy danych.
     */
    public function deleteCategory(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}