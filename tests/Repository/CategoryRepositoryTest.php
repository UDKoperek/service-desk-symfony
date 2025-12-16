<?php

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class CategoryRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private CategoryRepository $categoryRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);
    }

    public function testCanPersistAndFindCategory(): void
    {
        // ARRANGE
        $category = new Category();
        $category->setName('Wsparcie Techniczne');

        // ACT: Zapisujemy do bazy danych
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $categoryId = $category->getId(); 

        $this->entityManager->clear(); 
        
        // ASSERT: Odczytujemy go z bazy danych
        $foundCategory = $this->categoryRepository->find($categoryId); 

        $this->assertSame('Wsparcie Techniczne', $foundCategory->getName());
        $this->assertNotNull($foundCategory);
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        unset($this->entityManager);
        unset($this->categoryRepository);
    }
}