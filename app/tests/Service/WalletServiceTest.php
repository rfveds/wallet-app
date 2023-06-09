<?php
/**
* Wallet service tests.
 */

namespace App\Tests\Service;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\UserRepository;
use App\Service\WalletService;
use App\Service\WalletServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class WalletServiceTest.
 */
class WalletServiceTest extends KernelTestCase
{
    /**
     * Wallet repository.
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Wallet service.
     *
     * @var WalletService|null
     */
    private ?WalletServiceInterface $walletService;

    /**
     * Set up test.
     *
     * @throws ContainerExceptionInterface
     */
    public function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->walletService = $container->get(WalletService::class);
    }

    /**
     * Test save.
     *
     * @throws OptimisticLockException|NotFoundExceptionInterface|ContainerExceptionInterface|ORMException
     */
    public function testSave(): void
    {
        // given
        $expectedWallet = new Wallet();
        $expectedWallet->setTitle('Test Wallet');
        $expectedWallet->setBalance(0);
        $expectedWallet->setType('cash');
        $expectedWallet->setUser($this->createUser([UserRole::ROLE_USER->value], 'wallet_save@example.com'));

        // when
        $this->walletService->save($expectedWallet);

        // then
        $expectedWalletId = $expectedWallet->getId();
        $resultWallet = $this->entityManager->createQueryBuilder()
            ->select('wallet')
            ->from(Wallet::class, 'wallet')
            ->where('wallet.id = :id')
            ->setParameter('id', $expectedWalletId)
            ->getQuery()
            ->getSingleResult();

        $this->assertEquals($expectedWallet, $resultWallet);
    }

    /**
     * Test delete.
     *
     * @throws OptimisticLockException|NotFoundExceptionInterface|ContainerExceptionInterface|ORMException
     */
    public function testDelete(): void
    {
        // given
        $walletToDelete = new Wallet();
        $walletToDelete->setTitle('Test Wallet');
        $walletToDelete->setBalance(0);
        $walletToDelete->setType('cash');
        $walletToDelete->setUser($this->createUser([UserRole::ROLE_USER->value], 'wallet_delete@example.com'));

        $this->entityManager->persist($walletToDelete);
        $this->entityManager->flush();
        $deletedWalletId = $walletToDelete->getId();

        // when
        $this->walletService->delete($walletToDelete);

        // then
        $resultWallet = $this->entityManager->createQueryBuilder()
            ->select('wallet')
            ->from(Wallet::class, 'wallet')
            ->where('wallet.id = :id')
            ->setParameter('id', $deletedWalletId)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNull($resultWallet);
    }

    /**
     * Test pagination.
     *
     * @throws OptimisticLockException|NotFoundExceptionInterface|ContainerExceptionInterface|ORMException
     */
    public function testCreatePaginatedList(): void
    {
        // given
        $page = 1;
        $dataSetSize = 15;
        $expectedResultSize = 10;
        $user = $this->createUser([UserRole::ROLE_USER->value], 'wallet_list@example.com');

        $counter = 0;
        while ($counter < $dataSetSize) {
            $wallet = new Wallet();
            $wallet->setTitle('Test Category #'.$counter);
            $wallet->setBalance(0);
            $wallet->setType('cash');
            $wallet->setUser($user);

            $this->walletService->save($wallet);

            ++$counter;
        }

        // when
        $result = $this->walletService->createPaginatedList($page, $user);

        // then
        $this->assertEquals($expectedResultSize, $result->count());
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    protected function createUser(array $roles, string $email): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail($email);
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
