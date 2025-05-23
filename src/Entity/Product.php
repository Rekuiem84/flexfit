<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $name = null;

  #[ORM\Column(type: 'text', nullable: true)]
  private ?string $description = null;

  #[ORM\Column]
  private ?float $price = null;

  #[ORM\Column]
  private ?int $isAvailable = null;

  #[ORM\ManyToOne(inversedBy: 'products')]
  private ?Category $category = null;

  /**
   * @var Collection<int, Media>
   */
  #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'product')]
  private Collection $media;

  /**
   * @var Collection<int, SavedProduct>
   */
  #[ORM\OneToMany(targetEntity: SavedProduct::class, mappedBy: 'product', orphanRemoval: true)]
  private Collection $savedProducts;

  /**
   * @var Collection<int, OrderItem>
   */
  #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'product')]
  private Collection $orderItems;

  /**
   * @var Collection<int, User>
   */
  #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'products')]
  private Collection $users;


  public function __construct()
  {
    $this->media = new ArrayCollection();
    $this->savedProducts = new ArrayCollection();
    $this->orderItems = new ArrayCollection();
    $this->users = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
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

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(?string $description): static
  {
    $this->description = $description;

    return $this;
  }

  public function getPrice(): ?float
  {
    return $this->price;
  }

  public function setPrice(float $price): static
  {
    $this->price = $price;

    return $this;
  }

  public function getIsAvailable(): ?int
  {
    return $this->isAvailable;
  }

  public function setIsAvailable(int $isAvailable): static
  {
    $this->isAvailable = $isAvailable;

    return $this;
  }

  public function getCategory(): ?Category
  {
    return $this->category;
  }

  public function setCategory(?Category $category): static
  {
    $this->category = $category;

    return $this;
  }

  /**
   * @return Collection<int, Media>
   */
  public function getMedia(): Collection
  {
    return $this->media;
  }

  public function addMedium(Media $medium): static
  {
    if (!$this->media->contains($medium)) {
      $this->media->add($medium);
      $medium->setProduct($this);
    }

    return $this;
  }

  public function removeMedium(Media $medium): static
  {
    if ($this->media->removeElement($medium)) {
      // set the owning side to null (unless already changed)
      if ($medium->getProduct() === $this) {
        $medium->setProduct(null);
      }
    }

    return $this;
  }
  public function getMainImage(): ?Media
  {
    foreach ($this->media as $media) {
      if ($media->isMainImage()) {
        return $media;
      }
    }
    return null;
  }
  public function getDetailsImages(): array
  {
    $detailsImages = [];
    foreach ($this->media as $media) {
      if (!$media->isMainImage()) {
        $detailsImages[] = $media;
      }
    }
    return $detailsImages;
  }

  /**
   * @return Collection<int, SavedProduct>
   */
  public function getSavedProducts(): Collection
  {
    return $this->savedProducts;
  }

  public function addSavedProduct(SavedProduct $savedProduct): static
  {
    if (!$this->savedProducts->contains($savedProduct)) {
      $this->savedProducts->add($savedProduct);
      $savedProduct->setProduct($this);
    }

    return $this;
  }

  public function removeSavedProduct(SavedProduct $savedProduct): static
  {
    if ($this->savedProducts->removeElement($savedProduct)) {
      // set the owning side to null (unless already changed)
      if ($savedProduct->getProduct() === $this) {
        $savedProduct->setProduct(null);
      }
    }

    return $this;
  }

  /**
   * @return Collection<int, OrderItem>
   */
  public function getOrderItems(): Collection
  {
      return $this->orderItems;
  }

  public function addOrderItem(OrderItem $orderItem): static
  {
      if (!$this->orderItems->contains($orderItem)) {
          $this->orderItems->add($orderItem);
          $orderItem->setProduct($this);
      }

      return $this;
  }

  public function removeOrderItem(OrderItem $orderItem): static
  {
      if ($this->orderItems->removeElement($orderItem)) {
          // set the owning side to null (unless already changed)
          if ($orderItem->getProduct() === $this) {
              $orderItem->setProduct(null);
          }
      }

      return $this;
  }

  /**
   * @return Collection<int, User>
   */
  public function getUsers(): Collection
  {
      return $this->users;
  }

  public function addUser(User $user): static
  {
      if (!$this->users->contains($user)) {
          $this->users->add($user);
          $user->addProduct($this);
      }

      return $this;
  }

  public function removeUser(User $user): static
  {
      if ($this->users->removeElement($user)) {
          $user->removeProduct($this);
      }

      return $this;
  }
}
