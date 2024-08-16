<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\StorageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
#[ApiResource()]
#[ORM\Entity(repositoryClass: StorageRepository::class)]
class Storage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'storages')]
    private Collection $userId;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, StorageItems>
     */
    #[ORM\OneToMany(targetEntity: StorageItems::class, mappedBy: 'storageId')]
    private Collection $storageItems;

    public function __construct()
    {
        $this->userId = new ArrayCollection();
        $this->storageItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUserId(): Collection
    {
        return $this->userId;
    }

    public function addUserId(User $userId): static
    {
        if (!$this->userId->contains($userId)) {
            $this->userId->add($userId);
        }

        return $this;
    }

    public function removeUserId(User $userId): static
    {
        $this->userId->removeElement($userId);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, StorageItems>
     */
    public function getStorageItems(): Collection
    {
        return $this->storageItems;
    }

    public function addStorageItem(StorageItems $storageItem): static
    {
        if (!$this->storageItems->contains($storageItem)) {
            $this->storageItems->add($storageItem);
            $storageItem->setStorageId($this);
        }

        return $this;
    }

    public function removeStorageItem(StorageItems $storageItem): static
    {
        if ($this->storageItems->removeElement($storageItem)) {
            // set the owning side to null (unless already changed)
            if ($storageItem->getStorageId() === $this) {
                $storageItem->setStorageId(null);
            }
        }
        return $this;
    }

}
