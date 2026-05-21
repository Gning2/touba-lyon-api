<?php

namespace App\Entity;

use App\Repository\LoanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
#[ORM\Table(name: 'loan')]
#[ORM\HasLifecycleCallbacks]
class Loan
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_OVERDUE = 'overdue';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['loan:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: "Le nom de l'emprunteur est obligatoire")]
    #[Groups(['loan:read', 'loan:write'])]
    private ?string $borrowerName = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['loan:read', 'loan:write'])]
    private ?string $borrowerOrganization = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['loan:read', 'loan:write'])]
    private ?string $borrowerPhone = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['loan:read', 'loan:write'])]
    private ?string $borrowerEmail = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull(message: "La date de sortie est obligatoire")]
    #[Groups(['loan:read', 'loan:write'])]
    private ?\DateTimeInterface $loanDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['loan:read', 'loan:write'])]
    private ?\DateTimeInterface $expectedReturnDate = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['loan:read'])]
    private ?\DateTimeInterface $actualReturnDate = null;

    #[ORM\Column(length: 20)]
    #[Groups(['loan:read'])]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['loan:read', 'loan:write'])]
    private ?string $notes = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['loan:read', 'loan:write'])]
    private ?string $purpose = null;

    #[ORM\Column]
    #[Groups(['loan:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['loan:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'loan', targetEntity: LoanItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['loan:read'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getBorrowerName(): ?string { return $this->borrowerName; }
    public function setBorrowerName(string $borrowerName): static { $this->borrowerName = $borrowerName; return $this; }

    public function getBorrowerOrganization(): ?string { return $this->borrowerOrganization; }
    public function setBorrowerOrganization(?string $borrowerOrganization): static { $this->borrowerOrganization = $borrowerOrganization; return $this; }

    public function getBorrowerPhone(): ?string { return $this->borrowerPhone; }
    public function setBorrowerPhone(?string $borrowerPhone): static { $this->borrowerPhone = $borrowerPhone; return $this; }

    public function getBorrowerEmail(): ?string { return $this->borrowerEmail; }
    public function setBorrowerEmail(?string $borrowerEmail): static { $this->borrowerEmail = $borrowerEmail; return $this; }

    public function getLoanDate(): ?\DateTimeInterface { return $this->loanDate; }
    public function setLoanDate(\DateTimeInterface $loanDate): static { $this->loanDate = $loanDate; return $this; }

    public function getExpectedReturnDate(): ?\DateTimeInterface { return $this->expectedReturnDate; }
    public function setExpectedReturnDate(?\DateTimeInterface $expectedReturnDate): static { $this->expectedReturnDate = $expectedReturnDate; return $this; }

    public function getActualReturnDate(): ?\DateTimeInterface { return $this->actualReturnDate; }
    public function setActualReturnDate(?\DateTimeInterface $actualReturnDate): static { $this->actualReturnDate = $actualReturnDate; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }

    public function getPurpose(): ?string { return $this->purpose; }
    public function setPurpose(?string $purpose): static { $this->purpose = $purpose; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function getItems(): Collection { return $this->items; }

    public function addItem(LoanItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setLoan($this);
        }
        return $this;
    }

    public function removeItem(LoanItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getLoan() === $this) {
                $item->setLoan(null);
            }
        }
        return $this;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->expectedReturnDate !== null
            && $this->expectedReturnDate < new \DateTime();
    }
}
