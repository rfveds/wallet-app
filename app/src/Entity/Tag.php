<?php
/**
 * wallet-app.
 *
 * (c) Karol Kijowski , 2023
 */

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Tag.
 */
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(
    name: 'tags',
)]
class Tag
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * Title.
     */
    #[ORM\Column(type: Types::STRING, length: 45)]
    private ?string $title = null;

    /**
     * Admin only.
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $adminOnly = null;

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
     * Getter for admin only.
     *
     * @return bool|null Admin only
     */
    public function isAdminOnly(): ?bool
    {
        return $this->adminOnly;
    }

    /**
     * Setter for admin only.
     *
     * @param bool $adminOnly Admin only
     */
    public function setAdminOnly(bool $adminOnly): void
    {
        $this->adminOnly = $adminOnly;
    }
}
