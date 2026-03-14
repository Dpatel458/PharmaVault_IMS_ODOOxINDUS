<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Warehouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $shortCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    // Location (City) sathe nu connection
    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Location $location = null;

    // --- ID ---
    public function getId(): ?int
    {
        return $this->id;
    }

    // --- Name ---
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    // --- ShortCode ---
    public function getShortCode(): ?string 
    { 
        return $this->shortCode; 
    }

    public function setShortCode(?string $shortCode): static 
    { 
        $this->shortCode = $shortCode; 
        return $this; 
    }

    // --- Address ---
    public function getAddress(): ?string 
    { 
        return $this->address; 
    }

    public function setAddress(?string $address): static 
    { 
        $this->address = $address; 
        return $this; 
    }

    // --- Location Relationship ---
    public function getLocation(): ?Location 
    { 
        return $this->location; 
    }

    public function setLocation(?Location $location): static 
    { 
        $this->location = $location; 
        return $this; 
    }
}