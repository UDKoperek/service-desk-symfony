<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Service\TicketManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const ANONYMOUS_USERNAME = 'anonymous_submitter';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.com');
    
        $admin->setUsername('admin'); 
    
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $admin->setRoles(['ROLE_ADMIN']); 
        $manager->persist($admin);

        $agent = new User();
        $agent->setEmail('agent@example.com');

        $agent->setUsername('agent.com'); 

        $agent->setPassword($this->passwordHasher->hashPassword($agent, 'password'));
        $agent->setRoles(['ROLE_AGENT']); 
        $manager->persist($agent);

        $manager->flush();

        $anonymousUser = new User();

        $anonymousUser->setUsername(self::ANONYMOUS_USERNAME);
        $anonymousUser->setEmail(self::ANONYMOUS_USERNAME . '@example.com');

        $anonymousUser->setPassword($this->passwordHasher->hashPassword($anonymousUser, ''));

        $anonymousUser->setRoles(['ROLE_ANONYMOUS_USERNAME']);

        $existingUser = $manager->getRepository(User::class)->findOneBy(['username' => self::ANONYMOUS_USERNAME]);
        if (!$existingUser) {
            $manager->persist($anonymousUser);
            $manager->flush();
        }
    }
}