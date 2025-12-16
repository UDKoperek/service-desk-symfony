<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\Attributes\Test;

class CategoryControllerTest extends WebTestCase
{
    private $client;
    private $testCategory;

    private static $users = []; 

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        self::bootKernel(); 
        
        $em = static::getContainer()->get('doctrine')->getManager(); 
        
        $em->getConnection()->executeStatement('DELETE FROM category');
        $em->getConnection()->executeStatement('DELETE FROM user');
        static::$users = [];
        
        $this->testCategory = $this->createTestCategory('Kategoria do Edycji', $em); 
        $em->flush();
        
        self::ensureKernelShutdown();
        $this->client = null;
    }
    
    /**
     * Tworzy i zapisuje testowego użytkownika, jeśli nie istnieje w statycznej tablicy.
     */
    private function createTestUser(string $role, $em): User
    {
        $username = strtolower($role) . '_test';
        $email = strtolower($role) . '@test.com';

        if (isset(self::$users[$role])) {
            return self::$users[$role];
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword('hashed_password_for_tests'); 
        $user->setRoles([$role]); 
        $user->setUsername($username);
        
        $em->persist($user);
        $em->flush();
        
        self::$users[$role] = $user;
        return $user;
    }

    private function logInAs(string $role): void
    {
        self::ensureKernelShutdown();
        
        $this->client = static::createClient(); 
        
        $em = static::getContainer()->get('doctrine')->getManager();
        $em->clear(); 

        $user = $this->createTestUser($role, $em);
        $this->client->loginUser($user);
    }
    
    private function createTestCategory(string $name, $em): Category
    {
        $category = new Category();
        $category->setName($name);
        $em->persist($category);
        return $category;
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->testCategory = null;
        if (static::$kernel) {
            static::$kernel->shutdown();
        }
    }

    #[Test]
    public function testIndexAccessRights(): void
    {
        // 1. Anonim
        $client = static::createClient(); 
        $client->request('GET', '/category');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND, 'Anonim powinien zobaczyć 302.'); 

        // 2. ROLE_USER
        $this->logInAs('ROLE_USER');
        $this->client->request('GET', '/category');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, 'Użytkownik powinien zobaczyć 403.'); 

        // 3. ROLE_AGENT
        $this->logInAs('ROLE_AGENT');
        $this->client->request('GET', '/category');
        $this->assertResponseIsSuccessful('Agent powinien zobaczyć 200 OK.'); 
        $this->assertSelectorTextContains('h1', 'Wszystkie kategorie', 'Agent powinien zobaczyć poprawny nagłówek "Wszystkie kategorie".'); 

        // 4. ROLE_ADMIN
        $this->logInAs('ROLE_ADMIN');
        $this->client->request('GET', '/category');
        $this->assertResponseIsSuccessful('Admin powinien zobaczyć 200 OK.'); 
    }

    #[Test]
    public function testNewCategoryCreationAndAccess(): void
    {
        $this->logInAs('ROLE_ADMIN');
        $this->client->request('GET', '/category/new');
        $this->assertResponseIsSuccessful('Admin powinien zobaczyć formularz 200 OK.');

        $this->client->submitForm('Zapisz', [ 
            'category[name]' => 'Nowa Kategoria Testowa', 
        ]);

        $this->assertResponseRedirects('/category', Response::HTTP_SEE_OTHER);

        self::ensureKernelShutdown();
        self::bootKernel();
        $em = static::getContainer()->get('doctrine')->getManager();
        $em->clear();
        
        $newCategory = $em->getRepository(Category::class)->findOneBy(['name' => 'Nowa Kategoria Testowa']);
        $this->assertNotNull($newCategory, 'Kategoria powinna zostać zapisana w bazie po poprawnym formularzu.');
    }

    #[Test]
    public function testEditCategory(): void
    {
        $initialName = $this->testCategory->getName();
        $this->logInAs('ROLE_ADMIN'); 
        $NEW_NAME = 'Zmieniona Nazwa Finalna';

        $this->client->request('GET', '/category/'.$this->testCategory->getId().'/edit');
        $this->assertResponseIsSuccessful();
        
        $this->client->submitForm('Zmień', [ 
            'category[name]' => $NEW_NAME,
        ]);

        $this->assertResponseRedirects('/category', Response::HTTP_SEE_OTHER);
        
        self::ensureKernelShutdown(); 
        self::bootKernel();
        
        $em = static::getContainer()->get('doctrine')->getManager();
        $em->clear();
        $updatedCategory = $em->getRepository(Category::class)->find($this->testCategory->getId());
        
        $this->assertSame($NEW_NAME, $updatedCategory->getName(), 'Nazwa kategorii powinna zostać zmieniona w bazie.');
        $this->assertNotSame($initialName, $updatedCategory->getName());
    }

    #[Test]
    public function testDeleteCategoryRequiresAdminAndCsrf(): void
    {
        $categoryId = $this->testCategory->getId();

        // 1. ROLE_AGENT próbuje usunąć 
        $this->logInAs('ROLE_AGENT');
        $this->client->request('POST', '/category/'.$categoryId, [
            '_token' => 'dummy_token',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, 'Agent bez uprawnienia powinien zobaczyć 403.');

        // 2. ROLE_ADMIN z niepoprawnym tokenem CSRF
        $this->logInAs('ROLE_ADMIN'); 
        
        $this->client->request('POST', '/category/'.$categoryId, [
            '_token' => 'zly_token', 
        ]);
        $this->assertResponseRedirects('/category', Response::HTTP_SEE_OTHER);

        self::ensureKernelShutdown();
        self::bootKernel();
        $em = static::getContainer()->get('doctrine')->getManager();
        $categoryStillExists = $em->getRepository(Category::class)->find($categoryId); 
        $this->assertNotNull($categoryStillExists, 'Kategoria nie powinna zostać usunięta przy błędnym tokenie.');

        $this->logInAs('ROLE_ADMIN'); 


        $crawler = $this->client->request('GET', '/category/'.$categoryId.'/edit'); 
        $this->assertResponseIsSuccessful();
        
        $expectedActionUrl = '/category/' . $categoryId;
        
        $deleteButton = $crawler->selectButton('delete')
            ->closest('form[method="post"][action="'.$expectedActionUrl.'"]')
            ->children('button:contains("Usuń")')
            ->first();

        if ($deleteButton->count() > 0) {

            $form = $deleteButton->form();
            $this->client->submit($form);

        } else {
             $this->fail('Nie znaleziono przycisku "Usuń" w formularzu o akcji: ' . $expectedActionUrl . '. Sprawdź, czy widok /category renderuje formularz.');
        }

        $this->assertResponseRedirects('/category', Response::HTTP_SEE_OTHER);

        self::ensureKernelShutdown(); 
        self::bootKernel();
        $em = static::getContainer()->get('doctrine')->getManager();
        $categoryDeleted = $em->getRepository(Category::class)->find($categoryId);

        $this->assertNull($categoryDeleted, 'Kategoria powinna zostać usunięta po poprawnym tokenie.');
    } 
 
}