<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    private ?Product $product = null;


    #[ORM\Column]
    private ?bool $isMainImage = null;

    public function __construct($url, $isMainImage)
    {
        $this->url = $url;
        $this->isMainImage = $isMainImage;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
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

    public function isMainImage(): ?bool
    {
        return $this->isMainImage;
    }

    public function setIsMainImage(bool $isMainImage): static
    {
        $this->isMainImage = $isMainImage;

        return $this;
    }
}
