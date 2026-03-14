<?php

namespace App\Entity;

use App\Repository\StockMoveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockMoveRepository::class)]
#[ORM\Table(name: 'stock_moves')]
class StockMove
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $from_location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $to_location = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'draft';

    /**
     * @var Collection<int, StockMoveItem>
     */
    #[ORM\OneToMany(targetEntity: StockMoveItem::class, mappedBy: 'move', cascade: ['persist', 'remove'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    public function getFromLocation(): ?string
    {
        return $this->from_location;
    }

    public function setFromLocation(?string $from_location): static
    {
        $this->from_location = $from_location;

        return $this;
    }

    public function getToLocation(): ?string
    {
        return $this->to_location;
    }

    public function setToLocation(?string $to_location): static
    {
        $this->to_location = $to_location;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, StockMoveItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(StockMoveItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setMove($this);
        }

        return $this;
    }

    public function removeItem(StockMoveItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getMove() === $this) {
                $item->setMove(null);
            }
        }

        return $this;
    }
}
