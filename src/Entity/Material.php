<?php

namespace App\Entity;

use App\Repository\MaterialRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaterialRepository::class)]
#[ORM\Table(name: 'material')]
#[ORM\HasLifecycleCallbacks]
class Material
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['material:read', 'loan:read', 'movement:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le nom du matériel est obligatoire')]
    #[Assert\Length(max: 150)]
    #[Groups(['material:read', 'material:write', 'loan:read', 'movement:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['material:read', 'material:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['material:read', 'material:write'])]
    private ?string $reference = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero(message: 'Le stock total doit être positif ou nul')]
    #[Groups(['material:read', 'material:write'])]
    private ?int $totalStock = 0;

    #[ORM\Column]
    #[Groups(['material:read'])]
    private ?int $availableStock = 0;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['material:read', 'material:write'])]
    private ?string $unit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['material:read', 'material:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['material:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['material:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'materials')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La catégorie est obligatoire')]
    #[Groups(['material:read', 'material:write'])]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'material', targetEntity: LoanItem::class)]
    private Collection $loanItems;

    #[ORM\OneToMany(mappedBy: 'material', targetEntity: StockMovement::class)]
    private Collection $stockMovements;

    #[ORM\OneToMany(mappedBy: 'material', targetEntity: MaterialPhoto::class, cascade: ['remove'], orphanRemoval: true)]
    #[Groups(['material:read'])]
    private Collection $photos;

    public function __construct()
    {
        $this->loanItems = new ArrayCollection();
        $this->stockMovements = new ArrayCollection();
        $this->photos = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->availableStock = $this->totalStock;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getReference(): ?string { return $this->reference; }
    public function setReference(?string $reference): static { $this->reference = $reference; return $this; }

    public function getTotalStock(): ?int { return $this->totalStock; }
    public function setTotalStock(int $totalStock): static { $this->totalStock = $totalStock; return $this; }

    public function getAvailableStock(): ?int { return $this->availableStock; }
    public function setAvailableStock(int $availableStock): static { $this->availableStock = $availableStock; return $this; }

    public function getUnit(): ?string { return $this->unit; }
    public function setUnit(?string $unit): static { $this->unit = $unit; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): static { $this->notes = $notes; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): static { $this->category = $category; return $this; }

    public function getLoanItems(): Collection { return $this->loanItems; }
    public function getStockMovements(): Collection { return $this->stockMovements; }

    public function getLoanedQuantity(): int
    {
        $loaned = 0;
        foreach ($this->loanItems as $item) {
            if ($item->getLoan()->getStatus() === 'active') {
                $loaned += $item->getQuantity();
            }
        }
        return $loaned;
    }
    
    public function getPhotos(): Collection { 
        return $this->photos; 
    }

    public function addPhoto(MaterialPhoto $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setMaterial($this);
        }
        return $this;
    }

    public function removePhoto(MaterialPhoto $photo): static
    {
        if ($this->photos->removeElement($photo)) {
            if ($photo->getMaterial() === $this) {
                $photo->setMaterial(null);
            }
        }
        return $this;
    }

    public function getPhotoCount(): int { 
        return $this->photos->count(); 
    }
}
