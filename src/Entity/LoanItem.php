<?php
// src/Entity/LoanItem.php

namespace App\Entity;

use App\Repository\LoanItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoanItemRepository::class)]
#[ORM\Table(name: 'loan_item')]
class LoanItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['loan:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Positive(message: 'La quantité doit être positive')]
    #[Groups(['loan:read', 'loan:write'])]
    private ?int $quantity = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['loan:read'])]
    private ?int $returnedQuantity = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['loan:read', 'loan:write'])]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'loanItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['loan:read', 'loan:write'])]
    private ?Material $material = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Loan $loan = null;

    public function getId(): ?int { return $this->id; }

    public function getQuantity(): ?int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }

    public function getReturnedQuantity(): ?int { return $this->returnedQuantity; }
    public function setReturnedQuantity(int $returnedQuantity): static { $this->returnedQuantity = $returnedQuantity; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }

    public function getMaterial(): ?Material { return $this->material; }
    public function setMaterial(?Material $material): static { $this->material = $material; return $this; }

    public function getLoan(): ?Loan { return $this->loan; }
    public function setLoan(?Loan $loan): static { $this->loan = $loan; return $this; }

    public function getRemainingQuantity(): int
    {
        return ($this->quantity ?? 0) - ($this->returnedQuantity ?? 0);
    }
}
