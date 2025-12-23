<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\State\UserPasswordHasher;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Encja reprezentująca użytkownika systemu.
 * Implementuje interfejsy wymagane przez system bezpieczeństwa Symfony.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'Ten użytkownik już istnieje.')]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_AGENT') or is_granted('ROLE_ADMIN')"),
        new Get(security: "object == user or is_granted('ROLE_AGENT') or is_granted('ROLE_ADMIN')"),
        new Patch(
            processor: UserPasswordHasher::class,
            security: "object == user or is_granted('ROLE_AGENT') or is_granted('ROLE_ADMIN')",
            denormalizationContext: [
                'groups' => "is_granted('ROLE_ADMIN') ? ['user:write', 'admin:write'] : ['user:write']"
            ]
        )
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Główny klucz encji.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    /**
     * Nazwa użytkownika, używana jako identyfikator do logowania (musi być unikalna).
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Nazwa użytkownika jest wymagana.')]
    #[Assert\Length(
        min: 3, 
        max: 180, 
        minMessage: 'Nazwa musi mieć co najmniej {{ limit }} znaki.',
        maxMessage: 'Nazwa nie może przekroczyć {{ limit }} znaków.'
    )]
    #[Groups(['user:read'])]
    private ?string $username = null;

    /**
     * Adres email użytkownika.
     */
    
    #[ORM\Column(length: 180)]
    #[Assert\Email(
        mode: 'strict',
        message: '{{ value }} jest błędny.',
    )]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank(message: 'Musi być email')]
    private string $email;

    /**
     * @var list<string> The user roles
     * Tablica ról użytkownika (np. ROLE_ADMIN, ROLE_AGENT).
     */
    #[ORM\Column]
    #[Groups(['user:read', 'admin:write'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     * Hashed hasło użytkownika. Nigdy nie przechowujemy hasła w formie czystego tekstu!
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Hasło jest wymagane.', groups: ['registration'])] 
    #[Assert\Length(
        min: 8, 
        minMessage: 'Hasło musi mieć co najmniej {{ limit }} znaków.'
    )]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => false])]
    private bool $isVerified = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return (string) $this->email; 
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Zwraca identyfikator używany do logowania (zazwyczaj nazwa użytkownika lub email).
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        // Używamy nazwy użytkownika jako identyfikatora logowania.
        return (string) $this->username; 
    }

    /**
     * Zwraca listę ról użytkownika. Zapewnia, że każdy użytkownik ma rolę podstawową 'ROLE_USER'.
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Gwarantuje, że każdy użytkownik ma przynajmniej domyślną rolę
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Zwraca zakodowane (hashed) hasło użytkownika.
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function hashPassword(): void
    {
    }
    /**
     * Bezpieczna serializacja: Zapewnia, że sesja nie zawiera faktycznych skrótów haseł, 
     * ale jedynie ich bezpieczny hasz (CRC32C). Jest to standard bezpieczeństwa Symfony.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        // Zastępuje pełne hasło w sesji jego haszem CRC32C.
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    /**
     * Metoda przestarzała w Symfony 7.0+ i powinna być usunięta przy przejściu na Symfony 8.
     * Wcześniej służyła do usuwania poufnych danych po uwierzytelnieniu (np. hasła).
     * * @deprecated
     */
    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }
}