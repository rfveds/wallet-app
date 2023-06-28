<?php
/**
 * Category fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class CategoryFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class CategoryFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
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
        $admin->setEmail('admin_for_categories@example.com');
        $admin->setFirstName($this->faker->firstName);
        $admin->setLastName($this->faker->lastName);
        $admin->setRoles([UserRole::ROLE_SUPER_ADMIN->value]);
        $admin->setPassword(
            $this->passwordHasher->hashPassword(
                $admin,
                'admin1234'
            )
        );
        $this->manager->persist($admin);
        $this->manager->flush();

        /* Categories created by admin */
        $this->createMany(5, 'categories', function () {
            $category = new Category();
            $category->setTitle($this->faker->unique()->word);
            $category->setUserOrAdmin('admin');
            $category->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $category->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );

            $admin = $this->manager->getRepository(User::class)->findOneBy(['email' => 'admin_for_categories@example.com']);
            $category->setAuthor($admin);

            return $category;
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
