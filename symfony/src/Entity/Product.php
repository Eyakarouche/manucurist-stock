<?php

namespace App\Entity;

use App\Exception\InsufficientStockException;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null {
        get {
            return $this->id;
        }
    }

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $shade = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $priceHt = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column]
    private ?int $alertThreshold = null;

    #[ORM\Version, ORM\Column]
    private int $version = 1;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getShade(): ?string
    {
        return $this->shade;
    }

    public function setShade(string $shade): static
    {
        $this->shade = $shade;

        return $this;
    }

    public function getPriceHt(): ?string
    {
        return $this->priceHt;
    }

    public function setPriceHt(string $priceHt): static
    {
        $this->priceHt = $priceHt;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getAlertThreshold(): ?int
    {
        return $this->alertThreshold;
    }

    public function setAlertThreshold(int $alertThreshold): static
    {
        $this->alertThreshold = $alertThreshold;

        return $this;
    }

    /**
     * @throws \App\Exception\InsufficientStockException
     */
    public function sell(int $qty): void
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException(
                'Quantity must be a positive integer.'
            );
        }

        if ($qty > $this->stock) {
            throw new InsufficientStockException(
                $this->id,
                $qty,
                $this->stock
            );
        }

        $this->stock -= $qty;
    }

    public function isInAlert(): bool
    {
        return $this->stock < $this->alertThreshold;
    }
}

