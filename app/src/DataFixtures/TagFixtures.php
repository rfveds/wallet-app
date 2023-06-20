<?php
/**
 * Tag fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Enum\UserRole;
use App\Entity\Tag;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class TagFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class TagFixtures extends AbstractBaseFixtures
{
    /**
     * Password hasher.
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @param UserPasswordHasherInterface $passwordHasher Password hasher
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $admin = new User();
        $admin->setEmail('admin_for_tags@example.com');
        $admin->setFirstName($this->faker->firstName);
        $admin->setLastName($this->faker->lastName);
        $admin->setRoles([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value]);
        $admin->setPassword(
            $this->passwordHasher->hashPassword(
                $admin,
                'admin1234'
            )
        );
        $this->manager->persist($admin);
        $this->manager->flush();

        $this->createMany(5, 'tags', function () {
            $tag = new Tag();
            $tag->setTitle($this->faker->word);

            $admin = $this->manager->getRepository(User::class)->findOneBy(['email' => 'admin_for_tags@example.com']);
            $tag->setAuthor($admin);

            return $tag;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
