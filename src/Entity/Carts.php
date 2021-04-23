<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CartsRepository;


/**
 * @ORM\Table(name="im2021_paniers")
 * @ORM\Entity(repositoryClass=CartsRepository::class)
 */
class Carts
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="pk", type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Users::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(referencedColumnName="pk", nullable=false)
     * @ORM\Column(name="utilisateur")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Items::class)
     * @ORM\JoinColumn(nullable=false)
     * @ORM\Column(name="produit")
     */
    private $item;

    /**
     * @ORM\Column(name="quantite", type="integer", nullable=false, options={"default"=0})
     * @Assert\GreaterThanOrEqual(0)
     */
    private $quantity;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getItem(): ?Items
    {
        return $this->item;
    }

    public function setItem(?Items $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

}
