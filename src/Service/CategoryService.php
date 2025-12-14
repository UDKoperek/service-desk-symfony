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

    
    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }


    public function createCategory(Category $category): void
    {

        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }


    public function saveChanges(): void
    {

        $this->entityManager->flush();
    }

    public function deleteCategory(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}