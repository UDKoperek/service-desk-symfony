<?php

namespace App\Entity;

use App\Enum\TicketStatus;
use App\Enum\TicketPriority;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Proszę wprowadzić tytuł biletu.')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'Tytuł musi mieć co najmniej {{ limit }} symboli. Tytuł jest za krótki.',
        maxMessage: 'Tytuł nie może zawierać więcej niż {{ limit }} symboli. Tytuł jeset za długi.'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Описание проблемы не может быть пустым.')]
    #[Assert\Length(
        min: 5,
        max: 3000,
        minMessage: 'Treść musi mieć co najmniej {{ limit }} symboli. Treść jeset za krótka.',
        maxMessage: 'Treść mnie może zawierać więcej niż {{ limit }} symboli. Treść jeset za długa.'
    )]
    private ?string $content = null;

    #[ORM\Column(type: 'string', enumType: TicketPriority::class)]
    private TicketPriority $priority = TicketPriority::ABSENCE;


    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)] // Cant be NULL
    private ?Category $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sessionToken = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'ticket', orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column(type: 'string', enumType: TicketStatus::class)]
    private TicketStatus $status = TicketStatus::NEW;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
            }
    }
    
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getPriority(): TicketPriority
    {
        return $this->priority;
    }

    public function setPriority(TicketPriority $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTicket($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTicket() === $this) {
                $comment->setTicket(null);
            }
        }

        return $this;
    }

    public function getStatus(): TicketStatus
    {
        return $this->status;
    }

    public function setStatus(TicketStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getSessionToken(): ?string
    {
        return $this->sessionToken;
    }

    public function setSessionToken(?string $sessionToken): static
    {
        $this->sessionToken = $sessionToken;

        return $this;
    }
}
