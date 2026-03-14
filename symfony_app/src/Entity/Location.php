<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null; // e.g. Ahmedabad

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $shortCode = null; // e.g. AMD

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getShortCode(): ?string { return $this->shortCode; }
    public function setShortCode(?string $shortCode): static { $this->shortCode = $shortCode; return $this; }
}