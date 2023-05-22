<?php
/**
 * wallet-app.
 *
 * (c) Karol Kijowski , 2023
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Operation;

/**
 * Class TaskFixtures.
 */
class OperationFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $this->createMany(100, 'operations', function () {
            $operation = new Operation();
            $operation->setTitle($this->faker->word);
            $operation->setAmount($this->faker->randomFloat(2, 10, 1000));
            $operation->setCreatedAt(
                $this->faker->dateTimeBetween('-100 days', '-1 days')
            );
            $operation->setUpdatedAt(
                $this->faker->dateTimeBetween('-100 days', '-1 days')
            );
            /** @var Category $category */
            $category = $this->getRandomReference('categories');
            $operation->setCategory($category);

            return $operation;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class, 1:}
     */
    public function getDependencies(): array
    {
        return [CategoryFixtures::class];
    }
}
