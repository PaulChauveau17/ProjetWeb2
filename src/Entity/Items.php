<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ItemsRepository;


/**
 * @ORM\Table(name="im2021_produits")
 * @ORM\Entity(repositoryClass=ItemsRepository::class)
 */
class Items
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="pk", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="libelle", type="string", length=86)
     */
    private $description;

    /**
     * @ORM\Column(name="prix", type="float")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $price;

    /**
     * @ORM\Column(name="stock", type="integer")
     *@Assert\GreaterThanOrEqual(0)
     */
    private $stock;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }
}
