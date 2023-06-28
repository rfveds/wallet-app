<?php
/**
 * Report entity.
 */

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Report.
 */
#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ORM\Table(
    name: 'reports',
)]
#[UniqueEntity(fields: ['title'])]
class Report
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
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Date from.
     *
     * @var \DateTimeInterface|null Date from
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateFrom = null;

    /**
     * Date to.
     *
     * @var \DateTimeInterface|null Date to
     */
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateTo = null;

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
     * Author.
     *
     * @var User|null Author
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $author;

    /**
     * Category.
     *
     * @var Category|null $category Category
     */
    #[Assert\Type(type: 'App\Entity\Category')]
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    /**
     * Wallet.
     *
     * @var Wallet|null $wallet Wallet
     */
    #[Assert\Type(type: 'App\Entity\Wallet')]
    #[ORM\ManyToOne(targetEntity: Wallet::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Wallet $wallet = null;

    /**
     * Tag.
     *
     * @var Tag|null $tag Tag
     */
    #[Assert\Type(type: 'App\Entity\Tag')]
    #[ORM\ManyToOne(targetEntity: Tag::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Tag $tag = null;

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }// end getId()

    /**
     * Getter for createdAt.
     *
     * @return \DateTimeImmutable|null Created at date
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }// end getCreatedAt()

    /**
     * Setter for createdAt.
     *
     * @param \DateTimeImmutable $createdAt Created at date
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }// end setCreatedAt()

    /**
     * Getter for updatedAt.
     *
     * @return \DateTimeImmutable|null Updated at date
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }// end getUpdatedAt()

    /**
     * Setter for updatedAt.
     *
     * @param \DateTimeImmutable $updatedAt Updated at date
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }// end setUpdatedAt()

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
     * Getter for author.
     *
     * @return User|null Author
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }// end getAuthor()

    /**
     * Setter for author.
     *
     * @param User|null $author Author
     */
    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }// end setAuthor()

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

    // end setCategory()

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
     * Getter for tag.
     *
     * @return Tag|null Tag
     */
    public function getTag(): ?Tag
    {
        return $this->tag;
    }// end getTag()

    /**
     * Setter for tag.
     *
     * @param Tag|null $tag Tag
     */
    public function setTag(?Tag $tag): void
    {
        $this->tag = $tag;
    }// end setTag()

    /**
     * Getter for dateFrom.
     *
     * @return \DateTimeInterface|null Date from
     */
    public function getDateFrom(): ?\DateTimeInterface
    {
        return $this->dateFrom;
    }// end getDateFrom()

    /**
     * Setter for dateFrom.
     *
     * @param \DateTimeInterface|null $dateFrom Date from
     */
    public function setDateFrom(?\DateTimeInterface $dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }// end setDateFrom()

    /**
     * Getter for dateTo.
     *
     * @return \DateTimeInterface|null Date to
     */
    public function getDateTo(): ?\DateTimeInterface
    {
        return $this->dateTo;
    }// end getDateTo()

    /**
     * Setter for dateTo.
     *
     * @param \DateTimeInterface|null $dateTo Date to
     */
    public function setDateTo(?\DateTimeInterface $dateTo): void
    {
        $this->dateTo = $dateTo;
    }// end setDateTo()
}// end class
