<?php

namespace App\Entity;

use App\Repository\MaterialConditionHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MaterialConditionHistoryRepository::class)]
#[ORM\Table(name: 'material_condition_history')]
#[ORM\HasLifecycleCallbacks]
class MaterialConditionHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['history:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['history:read'])]
    private int $quantity = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['history:read'])]
    private ?string $comment = null;

    #[ORM\Column]
    #[Groups(['history:read'])]
    private ?\DateTimeImmutable $changedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['history:read'])]
    private ?Material $material = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['history:read'])]
    private ?MaterialCondition $condition = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->changedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }

    public function getComment(): ?string { return $this->comment; }
    public function setComment(?string $comment): static { $this->comment = $comment; return $this; }

    public function getChangedAt(): ?\DateTimeImmutable { return $this->changedAt; }
    public function setChangedAt(\DateTimeImmutable $changedAt): static { $this->changedAt = $changedAt; return $this; }

    public function getMaterial(): ?Material { return $this->material; }
    public function setMaterial(?Material $material): static { $this->material = $material; return $this; }

    public function getCondition(): ?MaterialCondition { return $this->condition; }
    public function setCondition(?MaterialCondition $condition): static { $this->condition = $condition; return $this; }
}
