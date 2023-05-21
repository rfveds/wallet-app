<?php
/**
 * Task fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Operation;

/**
 * Class TaskFixtures.
 */
class OperationFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     */
    public function loadData(): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $task = new Operation();
            $task->setTitle($this->faker->word);
            $task->setAmount($this->faker->randomFloat(2, 1, 1000));
            $task->setCreatedAt(
                \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );
            $task->setUpdatedAt(
                \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );
            $this->manager->persist($task);
        }

        $this->manager->flush();
    }
}
