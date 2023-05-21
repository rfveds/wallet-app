<?php
/**
 * Category entity.
 */

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Category.
 *
 * @psalm-supress MissingConstructor
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'categories')]
#[ORM\UniqueConstraint(name: 'uq_categories_title', columns: ['title'])]
#[UniqueEntity(fields: ['title'])]
class Category
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * Created at.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt;

    /**
     * Updated at.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $updatedAt;

    /**
     * Title.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $title;

    /**
     * Admin only.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $admin_only;

    /**
     * Getter for id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for createdAt.
     *
     * @return \DateTimeImmutable|null Created at
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for createdAt.
     *
     * @param \DateTimeImmutable $createdAt Created at
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Getter for updatedAt.
     *
     * @return \DateTimeImmutable|null Updated at
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter for updatedAt.
     *
     * @param \DateTimeImmutable $updatedAt Updated at
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Getter for title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string $title Title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Getter for admin_only.
     *
     * @return bool|null Admin only
     */
    public function isAdminOnly(): ?bool
    {
        return $this->admin_only;
    }

    /**
     * Setter for admin_only.
     *
     * @param bool $admin_only Admin only
     */
    public function setAdminOnly(bool $admin_only): void
    {
        $this->admin_only = $admin_only;
    }
}
