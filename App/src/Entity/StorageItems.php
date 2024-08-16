<?php

namespace App\Entity;

use App\Repository\StorageItemsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StorageItemsRepository::class)]
class StorageItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'storageItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Storage $storageId = null;

    /**
     * @var Collection<int, Products>
     */
    #[ORM\OneToMany(targetEntity: Products::class, mappedBy: 'storageItems')]
    private Collection $productId;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(nullable: true)]
    private ?int $minQuantity = null;

    public function __construct()
    {
        $this->productId = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStorageId(): ?Storage
    {
        return $this->storageId;
    }

    public function setStorageId(?Storage $storageId): static
    {
        $this->storageId = $storageId;

        return $this;
    }

    /**
     * @return Collection<int, Products>
     */
    public function getProductId(): Collection
    {
        return $this->productId;
    }

    public function addProductId(Products $productId): static
    {
        if (!$this->productId->contains($productId)) {
            $this->productId->add($productId);
            $productId->setStorageItems($this);
        }

        return $this;
    }

    public function removeProductId(Products $productId): static
    {
        if ($this->productId->removeElement($productId)) {
            // set the owning side to null (unless already changed)
            if ($productId->getStorageItems() === $this) {
                $productId->setStorageItems(null);
            }
        }

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getMinQuantity(): ?int
    {
        return $this->minQuantity;
    }

    public function setMinQuantity(?int $minQuantity): static
    {
        $this->minQuantity = $minQuantity;

        return $this;
    }
    /**
     * Get an array of product details (including quantity and minQuantity).
     *
     * @return array
     */
    public function getProductDetails(): array
    {
        $productDetails = [];

        foreach ($this->productId as $product) {
            $productDetails[] = [
                'product' => $product,
                'quantity' => $product->getQuantity(),
                'minQuantity' => $product->getMinQuantity(),
            ];
        }

        return $productDetails;
    }
}
