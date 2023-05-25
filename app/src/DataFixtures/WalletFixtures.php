<?php
/**
 * Wallet fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Wallet;

/**
 * Class WalletFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class WalletFixtures extends AbstractBaseFixtures
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

            return $wallet;
        });

        $this->manager->flush();
    }
}
