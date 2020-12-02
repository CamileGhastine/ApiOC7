<?php

namespace App\Entity;

use App\Repository\PhoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;
use OpenApi\Annotations as OA;

/**
 * @ORM\Entity(repositoryClass=PhoneRepository::class)
 *
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route("show_phone",
 *     parameters = { "id" = "expr(object.getId())" }),
 *     exclusion = @Hateoas\Exclusion(groups = "detail"),
 * )
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route("show_phone",
 *     parameters = { "id" = "expr(object.getId())" }),
 *     exclusion = @Hateoas\Exclusion(groups = "list"),
 * )
 *
 * @OA\Schema(
 *     schema="PhonesList",
 *     @OA\Property(type="integer", property="id"),
 *     @OA\Property(type="string", property="brand"),
 *     @OA\Property(type="string", property="model"),
 *     @OA\Property(type="integer", property="price"),
 *     @OA\Property(
 *          type="array",
 *          @OA\Items(
 *               @OA\Property(type="string", property="self"),
 *          ),
 *          property="links"),
 * )
 * @OA\Schema(
 *     schema="Phone",
 *     @OA\Property(type="integer", property="id"),
 *     @OA\Property(type="string", property="brand"),
 *     @OA\Property(type="string", property="model"),
 *     @OA\Property(type="integer", property="price"),
 *     @OA\Property(
 *          type="array",
 *          @OA\Items(
 *               @OA\Property(type="string", property="self"),
 *          ),
 *          property="links"),
 *     @OA\Property(type="string", property="description")
 * )

 */
class Phone
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Groups({"detail", "list"})
     *
     *
     * @var int
     */

    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Groups({"detail", "list"})
     *
     *
     * @var string
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Groups({"detail", "list"})
     *
     *
     * @var string
     */
    private $model;

    /**
     * @ORM\Column(type="integer")
     *
     * @Serializer\Groups({"detail", "list"})
     *
     *
     * @var int
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     *
     * @Serializer\Groups({"detail"})
     *
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=Customer::class, mappedBy="phones")
     *
     * @var ArrayCollection
     */
    private $customers;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
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

    /**
     * @return Collection|Customer[]
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): self
    {
        if (!$this->customers->contains($customer)) {
            $this->customers[] = $customer;
            $customer->addPhone($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): self
    {
        if ($this->customers->contains($customer)) {
            $this->customers->removeElement($customer);
            $customer->removePhone($this);
        }

        return $this;
    }
}
