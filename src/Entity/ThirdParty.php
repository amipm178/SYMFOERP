<?php

namespace App\Entity;

use App\Repository\ThirdPartyRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: ThirdPartyRepository::class)]
#[ORM\Table(name: 'app_third_party')]
#[ORM\Index(name: 'idx_third_party_category_name', columns: ['category', 'name'])]
class ThirdParty
{
    public const CATEGORY_CUSTOMER = 'customer';
    public const CATEGORY_PROSPECT = 'prospect';
    public const CATEGORY_SUPPLIER = 'supplier';

    private const CATEGORIES = [
        self::CATEGORY_CUSTOMER,
        self::CATEGORY_PROSPECT,
        self::CATEGORY_SUPPLIER,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $name;

    #[ORM\Column(length: 20)]
    private string $category;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    public function __construct(string $name, string $category = self::CATEGORY_CUSTOMER)
    {
        $this->setName($name);
        $this->setCategory($category);
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('A third party name cannot be empty.');
        }

        $this->name = $name;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        if (!in_array($category, self::CATEGORIES, true)) {
            throw new InvalidArgumentException('Unsupported third party category.');
        }

        $this->category = $category;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email !== null && trim($email) !== '' ? trim($email) : null;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone !== null && trim($phone) !== '' ? trim($phone) : null;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
