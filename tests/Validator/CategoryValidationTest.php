<?php

namespace App\Tests\Validator;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use PHPUnit\Framework\Attributes\Test;

class CategoryValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {

        self::bootKernel();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    #[Test]
    public function categoryNameCannotBeBlank(): void
    {
        // ARRANGE: Przygotowanie obiektu Category
        $category = new Category();
        $category->setName(''); 
        
        // ACT: Wywołanie walidatora na obiekcie
        /** @var ConstraintViolationListInterface $violations */
        $violations = $this->validator->validate($category);

        // ASSERT: Sprawdzenie, czy jest dokładnie jeden błąd
        // Oczekujemy błędu, ponieważ naruszyliśmy Assert\NotBlank
        $this->assertCount(1, $violations, 'Oczekiwano błędu, ponieważ nazwa jest pusta.');
        
        // ASSERT: Sprawdzenie, czy błąd dotyczy właściwego pola i komunikatu
        $this->assertSame('name', $violations[0]->getPropertyPath());
        $this->assertSame('Nazwa kategorii nie może być pusta.', $violations[0]->getMessage());
    }

    #[Test]
    public function validCategoryPassesValidation(): void
    {
        // ARRANGE: Przygotowanie obiektu Category z poprawną nazwą
        $category = new Category();
        $category->setName('Wsparcie IT');
        
        // ACT: Wywołanie walidatora
        $violations = $this->validator->validate($category);

        // ASSERT: Sprawdzenie, czy NIE ma żadnych błędów
        $this->assertCount(0, $violations, 'Kategoria z poprawną nazwą nie powinna generować błędów.');
    }
}