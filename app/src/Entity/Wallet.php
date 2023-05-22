<?php
/**
 * wallet-app.
 *
 * (c) Karol Kijowski , 2023
 */

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Wallet.
 */
#[ORM\Entity(repositoryClass: WalletRepository::class)]
class Wallet
{
    /**
     * Primary key.
     *
     * @var int|null $id Id
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Type.
     *
     * @var string|null $type Type
     */
    #[ORM\Column(length: 32)]
    private ?string $type = null;

    /**
     * Balance.
     *
     * @var string|null $balance Balance
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 16, scale: 2)]
    private ?string $balance = null;

    /**
     * Title.
     *
     * @var string|null $title Title
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

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
     * Getter for type.
     *
     * @return string|null Type
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Setter for type.
     *
     * @param string $type Type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Getter for balance.
     *
     * @return string|null Balance
     */
    public function getBalance(): ?string
    {
        return $this->balance;
    }

    /**
     * Setter for balance.
     *
     * @param string $balance Balance
     */
    public function setBalance(string $balance): void
    {
        $this->balance = $balance;
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
}
