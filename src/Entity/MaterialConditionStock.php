<?php

namespace App\Entity;

use App\Repository\MaterialConditionStockRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MaterialConditionStockRepository::class)]
#[ORM\Table(name: 'material_condition_stock')]
#[ORM\UniqueConstraint(columns: ['material_id', 'condition_id'])]
class MaterialConditionStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['material:read', 'condition_stock:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['material:read', 'condition_stock:read', 'condition_stock:write'])]
    private int $quantity = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['material:read', 'condition_stock:read'])]
    private ?Material $material = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['material:read', 'condition_stock:read'])]
    private ?MaterialCondition $condition = null;

    public function getId(): ?int { return $this->id; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }

    public function getMaterial(): ?Material { return $this->material; }
    public function setMaterial(?Material $material): static { $this->material = $material; return $this; }

    public function getCondition(): ?MaterialCondition { return $this->condition; }
    public function setCondition(?MaterialCondition $condition): static { $this->condition = $condition; return $this; }
}