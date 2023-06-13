<?php
/**
 * User controller tests.
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
 * Class UserControllerTest.
 */
class UserControllerTest extends WebTestCase
{
    /**
     * Test client.
     */
    private const TEST_ROUTE = '/user';

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
     * Test index action as anonymous user.
     */
    public function testIndexActionAsAnonymousUser(): void
    {
        $this->httpClient->request('GET', '/user/');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Test index action as logged as user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexActionAsLoggedInUser(): void
    {
        // given
        // access denied
        $expectedStatusCode = 403;
        $user = $this->createUser([UserRole::ROLE_USER->value], 'user_index_user@example.com');
        $this->httpClient->loginUser($user);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test index action as logged in admin.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testIndexActionAsLoggedInAdmin(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'user_index_admin@example.com');
        $this->httpClient->loginUser($adminUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/');
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show action.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowAction(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_user_show_admin@example.com');
        $this->httpClient->loginUser($adminUser);
        $adminUserId = $adminUser->getId();
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$adminUserId);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test delete user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteUser(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_delete_user@example.com');
        $this->httpClient->loginUser($adminUser);

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $this->createUser([UserRole::ROLE_USER->value], 'user_to_delete@example.com');
        $testUserId = $testUser->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testUserId.'/delete');

        // then
        $this->httpClient->submitForm('action.delete');

        // then
        $savedUser = $userRepository->findOneBy(['id' => $testUserId]);
        $this->assertNull($savedUser);
    }

    /**
     * Test delete user with wallet.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteUserWithWallet(): void
    {
        // given
        $userRepository = static::getContainer()->get(UserRepository::class);
        $walletRepository = static::getContainer()->get(WalletRepository::class);
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_delete_user_wallet@example.com');
        $this->httpClient->loginUser($adminUser);
        $testWallet = $this->createWallet('test_wallet', $adminUser);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$adminUser->getId().'/delete');

        // when
        $this->httpClient->submitForm('action.delete');

        // then
        $savedWallet = $walletRepository->findOneBy(['id' => $testWallet->getId()]);
        $savedUser = $userRepository->findOneBy(['id' => $adminUser->getId()]);
        $this->assertNull($savedWallet);
        $this->assertNull($savedUser);
    }

//        /**
//         * Test delete user with operation.
//         *
//         * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
//         */
//        public function testDeleteUserWithOperation(): void
//        {
//            // given
//            $userRepository = static::getContainer()->get(UserRepository::class);
//            $operationRepository = static::getContainer()->get(OperationRepository::class);
//            $walletRepository = static::getContainer()->get(WalletRepository::class);
//            $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_delete_user_operation@example.com');
//            $this->httpClient->loginUser($adminUser);
//            $testWallet = $this->createWallet('test_wallet', $adminUser);
//            $testOperation = $this->createOperation('test_operation', $adminUser, $testWallet);
//
//            $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$adminUser->getId().'/delete');
//
//            // when
//            $this->httpClient->submitForm('action.delete');
//
//            // then
//            $savedOperation = $operationRepository->findOneBy(['id' => $testOperation->getId()]);
//            $savedUser = $userRepository->findOneBy(['id' => $adminUser->getId()]);
//            $this->assertNull($savedOperation);
//            $this->assertNull($savedUser);
//        }

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
     * Create wallet.
     *
     * @param string $title Wallet name
     * @param User   $user  User entity
     *
     * @return Wallet Wallet entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createWallet(string $title, User $user): Wallet
    {
        $wallet = new Wallet();
        $wallet->setTitle($title);
        $wallet->setBalance(0);
        $wallet->setUser($user);
        $wallet->setType('cash');
        $walletRepository = static::getContainer()->get(WalletRepository::class);
        $walletRepository->save($wallet, true);

        return $wallet;
    }

    /**
     * Create operation.
     *
     * @param string $title  Operation name
     * @param User   $user   User entity
     * @param Wallet $wallet Wallet entity
     *
     * @return Operation Operation entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createOperation(string $title, User $user, Wallet $wallet): Operation
    {
        $operation = new Operation();
        $operation->setTitle($title);
        $operation->setAmount(0);
        $operation->setAuthor($user);
        $operation->setWallet($wallet);
        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $operationRepository->save($operation, true);

        return $operation;
    }
}
