<?php

namespace App\Entity;

use App\Repository\MaterialPhotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MaterialPhotoRepository::class)]
#[ORM\Table(name: 'material_photo')]
#[ORM\HasLifecycleCallbacks]
class MaterialPhoto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['material:read', 'photo:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['material:read', 'photo:read'])]
    private ?string $filename = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['material:read', 'photo:read'])]
    private ?string $originalName = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['material:read', 'photo:read'])]
    private ?int $fileSize = null;

    #[ORM\Column]
    #[Groups(['material:read', 'photo:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Material $material = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(string $filename): static { $this->filename = $filename; return $this; }

    public function getOriginalName(): ?string { return $this->originalName; }
    public function setOriginalName(?string $originalName): static { $this->originalName = $originalName; return $this; }

    public function getFileSize(): ?int { return $this->fileSize; }
    public function setFileSize(?int $fileSize): static { $this->fileSize = $fileSize; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getMaterial(): ?Material { return $this->material; }
    public function setMaterial(?Material $material): static { $this->material = $material; return $this; }

    #[Groups(['material:read', 'photo:read'])]
    public function getUrl(): string
    {
        return '/uploads/materials/' . $this->filename;
    }
}
