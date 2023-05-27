<?php
/**
 * Wallet fixtures.
 */

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Wallet;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class WalletFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class WalletFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
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

        $this->createMany(10, 'wallets', function () {
            $wallet = new Wallet();
            $wallet->setTitle($this->faker->word);
            $wallet->setBalance($this->faker->randomFloat(2, 10, 1000));
            $wallet->setType($this->faker->randomElement(['cash', 'bank', 'credit_card', 'other']));

            /** @var User $user */
            $user = $this->getRandomReference('users');
            $wallet->setUser($user);

            return $wallet;
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
