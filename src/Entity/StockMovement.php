<?php
// src/Entity/StockMovement.php

namespace App\Entity;

use App\Repository\StockMovementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StockMovementRepository::class)]
#[ORM\Table(name: 'stock_movement')]
#[ORM\HasLifecycleCallbacks]
class StockMovement
{
    public const TYPE_OUT = 'out';      // Sortie (emprunt)
    public const TYPE_IN  = 'in';       // Entrée (retour ou ajout stock)
    public const TYPE_ADJUSTMENT = 'adjustment'; // Ajustement manuel

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['movement:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Groups(['movement:read'])]
    private string $type = self::TYPE_OUT;

    #[ORM\Column]
    #[Groups(['movement:read'])]
    private ?int $quantity = null;

    #[ORM\Column]
    #[Groups(['movement:read'])]
    private ?int $stockBefore = null;

    #[ORM\Column]
    #[Groups(['movement:read'])]
    private ?int $stockAfter = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['movement:read'])]
    private ?string $reason = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['movement:read'])]
    private ?string $borrowerName = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['movement:read'])]
    private ?string $borrowerOrganization = null;

    #[ORM\Column]
    #[Groups(['movement:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'stockMovements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['movement:read'])]
    private ?Material $material = null;

    #[ORM\ManyToOne]
    #[Groups(['movement:read'])]
    private ?Loan $loan = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getQuantity(): ?int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }

    public function getStockBefore(): ?int { return $this->stockBefore; }
    public function setStockBefore(int $stockBefore): static { $this->stockBefore = $stockBefore; return $this; }

    public function getStockAfter(): ?int { return $this->stockAfter; }
    public function setStockAfter(int $stockAfter): static { $this->stockAfter = $stockAfter; return $this; }

    public function getReason(): ?string { return $this->reason; }
    public function setReason(?string $reason): static { $this->reason = $reason; return $this; }

    public function getBorrowerName(): ?string { return $this->borrowerName; }
    public function setBorrowerName(?string $borrowerName): static { $this->borrowerName = $borrowerName; return $this; }

    public function getBorrowerOrganization(): ?string { return $this->borrowerOrganization; }
    public function setBorrowerOrganization(?string $borrowerOrganization): static { $this->borrowerOrganization = $borrowerOrganization; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getMaterial(): ?Material { return $this->material; }
    public function setMaterial(?Material $material): static { $this->material = $material; return $this; }

    public function getLoan(): ?Loan { return $this->loan; }
    public function setLoan(?Loan $loan): static { $this->loan = $loan; return $this; }
}
