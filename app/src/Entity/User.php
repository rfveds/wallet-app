<?php
/**
 * User entity.
 */

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(
    name: 'users',
)]
#[ORM\UniqueConstraint(
    name: 'email_idx',
    columns: ['email'],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Primary key.
     *
     * @var int|null $id Id
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Email.
     *
     * @var string|null $email Email
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * First name.
     *
     * @var string|null $firstName First name
     */
    #[ORM\Column(type: 'string', length: 64)]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    /**
     * Last name.
     *
     * @var string|null $lastName Last name
     */
    #[ORM\Column(type: 'string', length: 64)]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    /**
     * Blocked.
     *
     * @var bool $blocked Blocked
     */
    #[ORM\Column(type: 'boolean')]
    private bool $blocked = false;

    /**
     * Roles.
     *
     * @var array<int, string> $roles Roles
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * Password.
     *
     * @var string|null The hashed password
     */
    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    private ?string $password = null;

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
     * Getter for password.
     *
     * @return string|null Password
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }// end getEmail()

    /**
     * Setter for password.
     *
     * @param string $email Email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }// end setEmail()

    /**
     * A visual identifier that represents this user.
     *
     * @return string User identifier
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }// end getUserIdentifier()

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     *
     * @return string Username
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }// end getUsername()

    /**
     * Getter for roles.
     *
     * @return array<int, string> Roles
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }// end getRoles()

    /**
     * Setter for roles.
     *
     * @param array<int, string> $roles Roles
     *
     * @return $this Self object
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }// end setRoles()

    /**
     * Getter for password.
     *
     * @return string Password
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }// end getPassword()

    /**
     * Setter for password.
     *
     * @param string $password Password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }// end setPassword()

    /**
     * Getter for first name.
     *
     * @return string|null First name
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }// end getFirstName()

    /**
     * Setter for first name.
     *
     * @param string $firstName First name
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }// end setFirstName()

    /**
     * Getter for last name.
     *
     * @return string|null Last name
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }// end getLastName()

    /**
     * Setter for last name.
     *
     * @param string $lastName Last name
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }// end setLastName()

    /**
     * Getter for blocked.
     *
     * @return bool|null Blocked
     */
    public function getBlocked(): ?bool
    {
        return $this->blocked;
    }// end getBlocked()

    /**
     * Setter for blocked.
     *
     * @param bool $blocked Blocked
     */
    public function setBlocked(bool $blocked): void
    {
        $this->blocked = $blocked;
    }// end setBlocked()

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @return string|null Salt
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }// end getSalt()

    /**
     * Removes sensitive information from the token.
     *
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }// end eraseCredentials()
}// end class
