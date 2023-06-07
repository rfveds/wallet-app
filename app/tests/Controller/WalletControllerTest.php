<?php
/**
 * Wallet Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\Operation;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\OperationRepository;
use App\Repository\UserRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class WalletControllerTest.
 */
class WalletControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/wallet';

    /**
     * Test client.
     */
    private KernelBrowser $httpClient;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
    }

    /**
     * Test index route for anonymous user.
     */
    public function testIndexRouteAnonymousUser(): void
    {
        // given
        $expectedStatusCode = 302; // redirect to login page

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index route for admin user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_wallet_admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/'
        );
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
    }

    /**
     * Test show single wallet.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     * @throws \Exception
     */
    public function testShowWallet(): void
    {
        // given
        $adminUser = $this->createUser(
            [UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value],
            'test_show_wallet@example.com');
        $this->httpClient->loginUser($adminUser);

        $expectedWallet = new Wallet();
        $expectedWallet->setTitle('Test 1 wallet');
        $expectedWallet->setUser($adminUser);
        $expectedWallet->setType('cash');
        $expectedWallet->setBalance(1000);
        $walletRepository = static::getContainer()->get(WalletRepository::class);
        $walletRepository->save($expectedWallet);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedWallet->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('html h1', $expectedWallet->getTitle());
    }

    /**
     * @throws OptimisticLockException
     * @throws NotFoundExceptionInterface
     * @throws ORMException
     * @throws ContainerExceptionInterface
     */
    public function testCreateWallet(): void
    {
        // given
        $user = $this->createUser(
            [UserRole::ROLE_USER->value],
            'test_wallet_create@example.com'
        );
        $this->httpClient->loginUser($user);
        $walletTitle = 'createdWallet';
        $walletRepository = static::getContainer()->get(WalletRepository::class);
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/create'
        );

        // when
        $this->httpClient->submitForm(
            'action.save',
            ['wallet' => [
                    'title' => $walletTitle,
                    'balance' => 0,
                ],
            ]
        );

        // then
        $savedWallet = $walletRepository->findOneByTitle($walletTitle);
        $this->assertEquals($walletTitle, $savedWallet->getTitle());

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * Test edit wallet.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditWallet(): void
    {
        // given
        $user = $this->createUser(
            [UserRole::ROLE_USER->value],
            'test_wallet_edit@example.com');
        $this->httpClient->loginUser($user);

        $walletRepository = static::getContainer()->get(WalletRepository::class);
        $testWallet = new Wallet();
        $testWallet->setTitle('editedWallet');
        $testWallet->setBalance(0);
        $testWallet->setUser($user);
        $testWallet->setType('cash');
        $walletRepository->save($testWallet);
        $testWalletId = $testWallet->getId();
        $expectedNewWalletTitle = 'TestWalletEdit';

        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/'.$testWalletId.'/edit');

        // when
        $this->httpClient->submitForm(
            'action.edit',
            ['wallet' => ['title' => $expectedNewWalletTitle]]
        );

        // then
        $savedWallet = $walletRepository->findOneById($testWalletId);
        $this->assertEquals($expectedNewWalletTitle,
            $savedWallet->getTitle());
    }

    /**
     * Test delete wallet.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteWallet(): void
    {
        // given
        $user = null;
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_wallet_delete@example.com');
        $this->httpClient->loginUser($user);

        $walletRepository = static::getContainer()->get(WalletRepository::class);
        $testWallet = new Wallet();
        $testWallet->setTitle('TestWalletCreated');
        $testWallet->setBalance(0);
        $testWallet->setUser($user);
        $testWallet->setType('cash');
        $walletRepository->save($testWallet);
        $testWalletId = $testWallet->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testWalletId.'/delete');

        // when
        $this->httpClient->submitForm(
            'action.delete'
        );

        // then
        $this->assertNull($walletRepository->findOneByTitle('TestWalletCreated'));
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
    private function createUser(array $roles, $email): User
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
        $userRepository->save($user, true);

        return $user;
    }

    /**
     * Create operation.
     *
     * @param User   $user       User entity
     * @param Wallet $testWallet Wallet entity
     *
     * @throws ContainerExceptionInterface
     */
    private function createOperation(User $user, Wallet $testWallet): Operation
    {
        $operation = new Operation();
        $operation->setTitle('TestOperation');
        $operation->setAmount('11');
        $operation->setWallet($testWallet);
        $operation->setWallet($this->createWallet($user));
        $operation->setAuthor($user);

        $operationRepository = self::getContainer()->get(OperationRepository::class);
        $operationRepository->save($operation);

        return $operation;
    }

    /**
     * Create Wallet.
     *
     * @param User $user User entity
     *
     * @throws ContainerExceptionInterface
     */
    protected function createWallet(User $user): Wallet
    {
        $wallet = new Wallet();
        $wallet->setTitle('TestWallet');
        $wallet->setType('cash');
        $wallet->setBalance('1000');
        $wallet->setUser($user);
        $walletRepository = self::getContainer()->get(WalletRepository::class);
        $walletRepository->save($wallet);

        return $wallet;
    }
}
