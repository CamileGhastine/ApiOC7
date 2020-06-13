<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 * @UniqueEntity("email")
 */
class Customer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"detail", "list"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"detail", "list"})
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"detail", "list"})
     * @SerializedName("firstName")
     * @Assert\NotBlank(message= "Le champs prénom ne peut pas être vide.")
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le prénom doit comporter au moins {{ limit }} charactères",
     *      maxMessage = "Le prénom doit comporter au plus {{ limit }} charactères",
     *      allowEmptyString = false
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"detail", "list"})
     * @SerializedName("lastName")
     * @Assert\NotBlank(message= "Le champs nom ne peut pas être vide.")
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le nom doit comporter au moins {{ limit }} charactères",
     *      maxMessage = "Le nom doit comporter au plus {{ limit }} charactères",
     *      allowEmptyString = false
     * )
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"detail", "list"})
     * @Assert\NotBlank(message= "Le champs adresse ne peut pas être vide.")
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "L\'adresse doit comporter au moins {{ limit }} charactères",
     *      maxMessage = "L\'adresse doit comporter au plus {{ limit }} charactères",
     *      allowEmptyString = false
     * )
     */
    private $address;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"detail", "list"})
     * @SerializedName("postCode")
     * @Assert\NotBlank(message= "Le champs code postal ne peut pas être vide.")
     * @Assert\Length(
     *      min = 5,
     *      max = 5,
     *      exactMessage = "Le code postal doit comporter {{ limit }} chiffres",
     *      allowEmptyString = false
     * )
     */
    private $postCode;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"detail", "list"})
     * @Assert\NotBlank(message= "Le champs ville ne peut pas être vide.")
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "La ville doit comporter au moins {{ limit }} charactères",
     *      maxMessage = "La ville doit comporter au plus {{ limit }} charactères",
     *      allowEmptyString = false
     * )
     */
    private $city;

    /**
     * @ORM\ManyToMany(targetEntity=Phone::class, inversedBy="customers")
     * @Serializer\Groups({"detail"})
     */
    private $phones;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="customers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->phones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostCode(): ?int
    {
        return $this->postCode;
    }

    public function setPostCode(int $postCode): self
    {
        $this->postCode = $postCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection|Phone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->contains($phone)) {
            $this->phones->removeElement($phone);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
