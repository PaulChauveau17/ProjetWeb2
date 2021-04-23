<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;
use App\Repository\UsersRepository;

/**
 * @ORM\Table(name="im2021_utilisateurs")
 * @ORM\Entity(repositoryClass=UsersRepository::class)
 */
class Users
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="pk", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="identifiant", type="string", length=20, unique=true)
     */
    private $login;

    /**
     * @ORM\Column(name="motdepasse", type="string", length=40, options={"comment"="has to be prefixed then hashed with sha1"})
     */
    private $encPwd;

    /**
     * @ORM\Column(name="nom", type="string", length=30)
     */
    private $name;

    /**
     * @ORM\Column(name="prenom", type="string", length=30)
     */
    private $surname;

    /**
     * @ORM\Column(name="anniversaire", type="date", nullable=true)
     */
    private $birthDate;

    /**
     * @ORM\Column(name="estadmin", type="boolean", options={"default"=false, "comment"="true if the user is an admin"})
     */
    private $isAdmin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getEncPwd(): ?string
    {
        return $this->encPwd;
    }

    // this setter uses a method to transform an not hashed password into an hashed password
    public function setEncPwd(string $pwd): self
    {
        //$this->encPwd = saltAndHash($pwd); // to check !
        $this->encPwd = $pwd;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getBirthDate(): ?DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?DateTimeInterface $BirthDate): self
    {
        $this->birthDate = $BirthDate;

        return $this;
    }

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }
}
