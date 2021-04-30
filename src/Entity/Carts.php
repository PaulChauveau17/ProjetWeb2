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
     * @var Users
     * @ORM\ManyToOne(targetEntity=Users::class)
     * @ORM\JoinColumn(name="utilisateur", nullable=false, referencedColumnName="pk")
     */
    private $user;

    /**
     * @var Items
     * @ORM\ManyToOne(targetEntity=Items::class)
     * @ORM\JoinColumn(name="produit", nullable=false, referencedColumnName="pk")
     */
    private $item;

    /**
     * @ORM\Column(name="quantite", type="integer", nullable=false, options={"default"=1})
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

    /*public function getUserID(): int
    { // this getter returns the ID
        return $this->user;
    }*/

    public function setUser(Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getItem(): ?Items
    {
        return $this->item;
    }

    /*public function getItemID(): int
    {  // this getter returns the ID
        return $this->item;
    }*/

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
