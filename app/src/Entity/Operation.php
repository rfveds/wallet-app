<?php
/**
 * Operation entity.
 */

namespace App\Entity;

use App\Repository\OperationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Operation.
 */
#[ORM\Entity(repositoryClass: OperationRepository::class)]
#[ORM\Table(
    name: 'operations',
)]
class Operation
{
    /**
     * Primary key.
     *
     * @var int|null Id
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Title.
     *
     * @var string|null Title
     */
    #[ORM\Column(type: 'string', length: 64)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $title = null;

    /**
     * Amount.
     *
     * @var string Amount
     */
    #[ORM\Column(type: 'decimal', precision: 16, scale: 2)]
    #[Assert\Type(type: 'numeric')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 16)]
    private string $amount;

    /**
     * Created at.
     *
     * @var \DateTimeImmutable|null Created at
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Updated at.
     *
     * @var \DateTimeImmutable|null Updated at
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Category.
     *
     * @var Category|null Category
     */
    #[Assert\Type(type: 'App\Entity\Category')]
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * Wallet.
     *
     * @var Wallet|null Wallet
     */
    #[Assert\Type(type: 'App\Entity\Wallet')]
    #[ORM\ManyToOne(targetEntity: Wallet::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Wallet $wallet = null;

    /**
     * Tags.
     *
     * @var ArrayCollection<int, Tag>|Tag[] Tags
     */
    #[Assert\Valid]
    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'operations_tags')]
    private Collection|array $tags;

    /**
     * Author.
     *
     * @var User|null Author
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type(type: User::class)]
    private ?User $author;

    /**
     * Operation constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }// end __construct()

    /**
     * Getter for id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }// end getId()

    /**
     * Getter for title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }// end getTitle()

    /**
     * Setter for title.
     *
     * @param string $title Title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }// end setTitle()

    /**
     * Getter for amount.
     *
     * @return string|null Amount
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }// end getAmount()

    /**
     * Setter for amount.
     *
     * @param string $amount Amount
     */
    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }// end setAmount()

    /**
     * Getter for createdAt.
     *
     * @return \DateTimeInterface|null Created at
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }// end getCreatedAt()

    /**
     * Setter for createdAt.
     *
     * @param \DateTimeImmutable $createdAt Created at
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }// end setCreatedAt()

    /**
     * Getter for updatedAt.
     *
     * @return \DateTimeImmutable|null Updated at
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }// end getUpdatedAt()

    /**
     * Setter for updatedAt.
     *
     * @param \DateTimeImmutable|null $updatedAt Updated at
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }// end setUpdatedAt()

    /**
     * Getter for category.
     *
     * @return Category|null Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }// end getCategory()

    /**
     * Setter for category.
     *
     * @param Category|null $category Category
     */
    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }// end setCategory()

    /**
     * Getter for wallet.
     *
     * @return Wallet|null Wallet
     */
    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }// end getWallet()

    /**
     * Setter for wallet.
     *
     * @param Wallet|null $wallet Wallet
     */
    public function setWallet(?Wallet $wallet): void
    {
        $this->wallet = $wallet;
    }// end setWallet()

    /**
     * Getter for tags.
     *
     * @return Collection<int, Tag> Tags
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }// end getTags()

    /**
     * Add tag.
     *
     * @param Tag $tag Tag
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }// end addTag()

    /**
     * Remove tag.
     *
     * @param Tag $tag Tag
     */
    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }// end removeTag()

    /**
     * Getter for author.
     *
     * @return User|null Author
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for author.
     *
     * @param User|null $author Author
     */
    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }
}// end class
