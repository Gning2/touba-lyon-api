<?php

namespace App\Entity;

use App\Repository\MaterialConditionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaterialConditionRepository::class)]
#[ORM\Table(name: 'material_condition')]
#[ORM\HasLifecycleCallbacks]
class MaterialCondition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['condition:read', 'material:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le libellé est obligatoire')]
    #[Groups(['condition:read', 'condition:write', 'material:read'])]
    private ?string $label = null;

    #[ORM\Column(length: 7)]
    #[Groups(['condition:read', 'condition:write', 'material:read'])]
    private string $color = '#10B981';

    #[ORM\Column]
    #[Groups(['condition:read', 'condition:write', 'material:read'])]
    private bool $isAvailable = true;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['condition:read', 'condition:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['condition:read', 'condition:write'])]
    private int $position = 0;

    #[ORM\Column]
    #[Groups(['condition:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getLabel(): ?string { return $this->label; }
    public function setLabel(string $label): static { $this->label = $label; return $this; }

    public function getColor(): string { return $this->color; }
    public function setColor(string $color): static { $this->color = $color; return $this; }

    public function isAvailable(): bool { return $this->isAvailable; }
    public function setIsAvailable(bool $isAvailable): static { $this->isAvailable = $isAvailable; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}
