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
use Hateoas\Configuration\Annotation as Hateoas;
use OpenApi\Annotations as OA;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 *
 * @UniqueEntity(fields={"email", "user"}, message="ce courriel est déjà utilisé !")
 *
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route("show_customer",
 *         parameters = { "id" = "expr(object.getId())" }
 *     ),
 *     embedded = "expr(object.getPhones())",
 *     exclusion = @Hateoas\Exclusion(groups = "detail")
 * )
 * @Hateoas\Relation(
 *     "create",
 *     href = @Hateoas\Route("add_customer"),
 *     exclusion = @Hateoas\Exclusion(groups = "detail")
 * )
 * @Hateoas\Relation(
 *     "update",
 *     href = @Hateoas\Route("update_customer",
 *     parameters = { "id" = "expr(object.getId())" }),
 *     exclusion = @Hateoas\Exclusion(groups = "detail")
 * )
 * @Hateoas\Relation(
 *     "delete",
 *     href = @Hateoas\Route("delete_customer",
 *     parameters = { "id" = "expr(object.getId())" }),
 *     exclusion = @Hateoas\Exclusion(groups = "detail")
 * )
 *
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route("show_customer",
 *         parameters = { "id" = "expr(object.getId())" }
 *     ),
 *     embedded = "expr(object.getPhones())",
 *     exclusion = @Hateoas\Exclusion(groups = "list")
 * )
 * @Hateoas\Relation(
 *     "create",
 *     href = @Hateoas\Route("add_customer"),
 *     exclusion = @Hateoas\Exclusion(groups = "list")
 * )
 * @Hateoas\Relation(
 *     "update",
 *     href = @Hateoas\Route("update_customer",
 *     parameters = { "id" = "expr(object.getId())" }),
 *     exclusion = @Hateoas\Exclusion(groups = "list")
 * )
 * @Hateoas\Relation(
 *     "delete",
 *     href = @Hateoas\Route("delete_customer",
 *     parameters = { "id" = "expr(object.getId())" }),
 *     exclusion = @Hateoas\Exclusion(groups = "list")
 * )
 *
 * @OA\Schema(
 *     schema="CustomersList",
 *     @OA\Property(type="integer", property="id"),
 *     @OA\Property(type="string", property="email"),
 *     @OA\Property(type="string", property="firstName"),
 *     @OA\Property(type="string", property="lastName"),
 *     @OA\Property(
 *          type="array",
 *          @OA\Items(
 *               @OA\Property(type="string", property="self"),
 *               @OA\Property(type="string", property="create"),
 *               @OA\Property(type="string", property="update"),
 *               @OA\Property(type="string", property="delete"),
 *          ),
 *          property="links"),
 *     @OA\Property(
 *          type="array",
 *          @OA\Items(
 *               @OA\Property(type="string", property="phones"),
 *          ),
 *          property="embedded"),
 * )
 * @OA\Schema(
 *     schema="Customer",
 *     @OA\Property(type="integer", property="id"),
 *     @OA\Property(type="string", property="email"),
 *     @OA\Property(type="string", property="firstName"),
 *     @OA\Property(type="string", property="lastName"),
 *     @OA\Property(type="string", property="address"),
 *     @OA\Property(type="integer", property="postCode"),
 *     @OA\Property(type="string", property="city"),
 *     @OA\Property(type="array", @OA\Items(ref="#/components/schemas/Phone"),  property="phones"),
 *     @OA\Property(
 *          type="array",
 *          @OA\Items(
 *               @OA\Property(type="string", property="self"),
 *               @OA\Property(type="string", property="create"),
 *               @OA\Property(type="string", property="update"),
 *               @OA\Property(type="string", property="delete"),
 *          ),
 *          property="links"),
 *     @OA\Property(
 *          type="array",
 *          @OA\Items(
 *               @OA\Property(type="string", property="phones"),
 *          ),
 *          property="embedded"),
 * )
 * @OA\Schema(
 *     schema="CustomerEdit",
 *     @OA\Property(type="string", property="email"),
 *     @OA\Property(type="string", property="firstName"),
 *     @OA\Property(type="string", property="lastName"),
 *     @OA\Property(type="string", property="address"),
 *     @OA\Property(type="integer", property="postCode"),
 *     @OA\Property(type="string", property="city"),
 * )
 */
class Customer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Groups({"detail", "list"})
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Groups({"detail", "list"})
     *
     * @Assert\NotBlank(message= "Le champs email ne peut pas être vide.")
     * @Assert\Email
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"detail", "list"})
     *
     * @SerializedName("firstName")
     *
     * @Assert\NotBlank(message= "Le champs prénom ne peut pas être vide.")
     * @Assert\Regex("/^[A-Za-zÀ-ÖØ-öø-ÿ](['A-Za-zÀ-ÖØ-öø-ÿ-](\s)?)+[A-Za-zÀ-ÖØ-öø-ÿ]+$/", message="Le champs prénom n'a pas le bon format ")
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le prénom doit comporter au moins {{ limit }} charactères",
     *      maxMessage = "Le prénom doit comporter au plus {{ limit }} charactères",
     *      allowEmptyString = false
     * )
     *
     * @var string
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Groups({"detail", "list"})
     * @SerializedName("lastName")
     *
     * @Assert\NotBlank(message= "Le champs nom ne peut pas être vide.")
     * @Assert\Regex("/^[A-Za-zÀ-ÖØ-öø-ÿ-]([A-Za-zÀ-ÖØ-öø-ÿ-](\s|-)?)+[A-Za-zÀ-ÖØ-öø-ÿ-]+$/", message="Le champs nom n'a pas le bon format ")
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "Le nom doit comporter au moins {{ limit }} charactères",
     *      maxMessage = "Le nom doit comporter au plus {{ limit }} charactères",
     *      allowEmptyString = false
     * )
     *
     * @var string
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Groups({"detail"})
     *
     * @Assert\NotBlank(message= "Le champs adresse ne peut pas être vide.")
     * @Assert\Regex("/\w+/", message="Le champs adresse n'a pas le bon format ")
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "L\'adresse doit comporter au moins {{ limit }} charactères",
     *      maxMessage = "L\'adresse doit comporter au plus {{ limit }} charactères",
     *      allowEmptyString = false
     * )
     *
     * @var string
     */
    private $address;

    /**
     * @ORM\Column(type="integer")
     *
     * @Serializer\Groups({"detail"})
     *
     * @SerializedName("postCode")
     * @Assert\NotBlank(message= "Le champs code postal ne peut pas être vide.")
     * @Assert\Length(
     *      min = 5,
     *      max = 5,
     *      exactMessage = "Le code postal doit comporter {{ limit }} chiffres",
     *      allowEmptyString = false
     * )
     *
     * @var int
     */
    private $postCode;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Groups({"detail"})
     *
     * @Assert\NotBlank(message= "Le champs ville ne peut pas être vide.")
     * @Assert\Regex("/^[a-zA-Z_]([a-zA-Z_](\s|-)?)+[a-zA-Z_]+$/", message="Le champs ville n'a pas le bon format ")
     * @Assert\Length(
     *      min = 2,
     *      max = 50,
     *      minMessage = "La ville doit comporter au moins {{ limit }} charactères",
     *      maxMessage = "La ville doit comporter au plus {{ limit }} charactères",
     *      allowEmptyString = false
     * )
     *
     * @var string
     */
    private $city;

    /**
     * @ORM\ManyToMany(targetEntity=Phone::class, inversedBy="customers")
     *
     * @var ArrayCollection
     */
    private $phones;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="customers")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var User
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
