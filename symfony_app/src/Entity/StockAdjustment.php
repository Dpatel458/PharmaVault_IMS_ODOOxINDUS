<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class StockAdjustment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Warehouse $warehouse = null;

    #[ORM\Column(type: 'integer')]
    private int $oldQuantity = 0;

    #[ORM\Column(type: 'integer')]
    private int $newQuantity = 0;

    #[ORM\Column(type: 'integer')]
    private int $difference = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getWarehouse(): ?Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(?Warehouse $warehouse): static
    {
        $this->warehouse = $warehouse;
        return $this;
    }

    public function getOldQuantity(): int
    {
        return $this->oldQuantity;
    }

    public function setOldQuantity(int $oldQuantity): static
    {
        $this->oldQuantity = $oldQuantity;
        return $this;
    }

    public function getNewQuantity(): int
    {
        return $this->newQuantity;
    }

    public function setNewQuantity(int $newQuantity): static
    {
        $this->newQuantity = $newQuantity;
        return $this;
    }

    public function getDifference(): int
    {
        return $this->difference;
    }

    public function setDifference(int $difference): static
    {
        $this->difference = $difference;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
