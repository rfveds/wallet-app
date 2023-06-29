<?php
/**
 * Operation fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Operation;
use App\Entity\Tag;
use App\Entity\Wallet;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class TaskFixtures.
 */
class OperationFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $this->createMany(10, 'operations', function ($i) {
            $operation = new Operation();
            $operation->setTitle($this->faker->sentence(2, true));
            $operation->setAmount($this->faker->randomFloat(2, 10, 1000));
            $date = \DateTimeImmutable::createFromMutable(
                $this->faker->dateTimeInInterval('-1 years', '+5 days', 'Europe/Warsaw')
            );
            $operation->setCreatedAt($date);
            $operation->setUpdatedAt($date);

            /** @var Category $category */
            $category = $this->getRandomReference('categories');
            $operation->setCategory($category);

            /** @var Wallet $wallet */
            $wallet = $this->getRandomReference('wallets');
            $operation->setWallet($wallet);
            $operation->setCurrentBalance($wallet->getBalance() + $operation->getAmount());
            $wallet->setBalance($wallet->getBalance() + $operation->getAmount());

            $operation->setAuthor($wallet->getUser());

            /** @var Tag $tag */
            $tags = $this->getRandomReferences('tags', $this->faker->numberBetween(0, 5));
            foreach ($tags as $tag) {
                $operation->addTag($tag);
            }

            return $operation;
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
        return [WalletFixtures::class, CategoryFixtures::class, TagFixtures::class, UserFixtures::class];
    }
}
