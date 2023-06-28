<?php
/**
 * Operation fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Operation;
use App\Entity\Tag;
use App\Entity\User;
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

        $this->createMany(50, 'operations', function ($i) {
            $operation = new Operation();
            $operation->setTitle($this->faker->word);
            $operation->setAmount($this->faker->randomFloat(2, 10, 1000));
            $date = \DateTimeImmutable::createFromMutable(
                $this->faker->dateTimeBetween('-50 days', '-'.(50) - $i.'days')
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

            /** @var Tag $tag */
            $tags = $this->getRandomReferences('tags', $this->faker->numberBetween(0, 5));
            foreach ($tags as $tag) {
                $operation->addTag($tag);
            }

            /** @var User $author */
            $author = $wallet->getUser();
            $operation->setAuthor($author);

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
