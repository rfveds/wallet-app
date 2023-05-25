<?php
/**
 * Abstract base fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

/**
 * Class AbstractBaseFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
abstract class AbstractBaseFixtures extends Fixture
{
    /**
     * Faker.
     */
    protected ?Generator $faker = null;

    /**
     * Persistence object manager.
     */
    protected ?ObjectManager $manager = null;

    /**
     * Object reference index.
     *
     * @var array<string, array<int, array-key>>
     */
    private array $referencesIndex = [];

    /**
     * Load.
     *
     * @param ObjectManager $manager Persistence object manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create();
        $this->loadData();
    }

    /**
     * Load data.
     */
    abstract protected function loadData(): void;

    /**
     * Create many objects at once:.
     *
     *      $this->createMany(10, function(int $i) {
     *          $user = new User();
     *          $user->setFirstName('Ryan');
     *
     *           return $user;
     *      });
     *
     * @param int      $count     Number of object to create
     * @param string   $groupName Tag these created objects with this group name,
     *                            and use this later with getRandomReference(s)
     *                            to fetch only from this specific group
     * @param callable $factory   Defines method of creating objects
     *
     * @psalm-suppress PossiblyNullReference
     */
    protected function createMany(int $count, string $groupName, callable $factory): void
    {
        for ($i = 0; $i < $count; ++$i) {
            /** @var object|null $entity */
            $entity = $factory($i);

            if (null === $entity) {
                throw new \LogicException('Did you forget to return the entity object from your callback to BaseFixture::createMany()?');
            }

            $this->manager->persist($entity);

            // store for usage later than groupName_#COUNT#
            $this->addReference(sprintf('%s_%d', $groupName, $i), $entity);
        }
    }

    /**
     * Set random reference to the object.
     *
     * @param string $groupName Objects group name
     *
     * @return object Random object reference
     *
     * @psalm-suppress MixedAssignment
     * @psalm-suppress UnusedForeachValue
     */
    protected function getRandomReference(string $groupName): object
    {
        if (!isset($this->referencesIndex[$groupName])) {
            $this->referencesIndex[$groupName] = [];

            foreach ($this->referenceRepository->getReferences() as $key => $reference) {
                if (str_starts_with((string) $key, $groupName.'_')) {
                    $this->referencesIndex[$groupName][] = $key;
                }
            }
        }

        if (empty($this->referencesIndex[$groupName])) {
            throw new \InvalidArgumentException(sprintf('Did not find any references saved with the group name "%s"', $groupName));
        }

        $randomReferenceKey = (string) $this->faker->randomElement($this->referencesIndex[$groupName]);

        return $this->getReference($randomReferenceKey);
    }

    /**
     * Get array of objects references based on count.
     *
     * @param string $groupName Object group name
     * @param int    $count     Number of references
     *
     * @return object[] Result
     *
     * @psalm-return list<object>
     */
    protected function getRandomReferences(string $groupName, int $count): array
    {
        $references = [];
        while (count($references) < $count) {
            $references[] = $this->getRandomReference($groupName);
        }

        return $references;
    }
}
