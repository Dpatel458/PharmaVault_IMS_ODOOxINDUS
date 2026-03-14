<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Transfer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Warehouse $sourceLocation = null;

    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Warehouse $destinationLocation = null;

    #[ORM\Column(length: 50)]
    private string $status = 'draft';

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'transfer', targetEntity: TransferItem::class, cascade: ['persist', 'remove'])]
    private Collection $items;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceLocation(): ?Warehouse
    {
        return $this->sourceLocation;
    }

    public function setSourceLocation(?Warehouse $sourceLocation): static
    {
        $this->sourceLocation = $sourceLocation;
        return $this;
    }

    public function getDestinationLocation(): ?Warehouse
    {
        return $this->destinationLocation;
    }

    public function setDestinationLocation(?Warehouse $destinationLocation): static
    {
        $this->destinationLocation = $destinationLocation;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(TransferItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setTransfer($this);
        }
        return $this;
    }

    public function removeItem(TransferItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getTransfer() === $this) {
                $item->setTransfer(null);
            }
        }
        return $this;
    }
}
