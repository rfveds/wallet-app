<?php
/**
* Operation service tests.
 */

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Operation;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\OperationService;
use App\Service\OperationServiceInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class OperationServiceTest.
 */
class OperationServiceTest extends KernelTestCase
{
    /**
     * Operation repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Operation service.
     */
    private ?OperationServiceInterface $operationService;

    /**
     * Set up test.
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->operationService = $container->get(OperationService::class);
    }

    /**
     * Test save.
     *
     * @throws ORMException
     */
    public function testSave(): void
    {
        // given
        $expectedOperation = new Operation();
        $expectedOperation->setTitle('Test Operation');
        $expectedOperation->setAmount('');

        // when
        $this->operationService->save($expectedOperation);

        // then
        $expectedOperationId = $expectedOperation->getId();
        $resultOperation = $this->entityManager->createQueryBuilder()
            ->select('operation')
            ->from(Operation::class, 'operation')
            ->where('operation.id = :id')
            ->setParameter(':id', $expectedOperationId, Types::INTEGER)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedOperation, $resultOperation);
    }

    /**
     * Create Wallet.
     * @return Wallet
     */
    private function createWallet(string $user = 'userr@example.com'): Wallet
    {
        $wallet = new Wallet();
        $wallet->setTitle('Test Wallet');
        $wallet->setType('cash');
        $wallet->setBalance('999');
        $wallet->setUser($this->createUser([UserRole::ROLE_USER->value], $user));
        $walletRepository = self::getContainer()->get(WalletRepository::class);
        $walletRepository->save($wallet);

        return $wallet;
    }

    /**
     * Create Category.
     */
    private function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('Test Category');
        $category->setCreatedAt(new \DateTimeImmutable('now'));
        $category->setCreatedAt(new \DateTimeImmutable('now'));
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save();

        return $category;
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|\Doctrine\ORM\ORMException|OptimisticLockException
     */
    private function createUser(array $roles): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }
}
