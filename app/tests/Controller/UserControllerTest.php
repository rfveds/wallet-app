<?php
/**
 * User controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Operation;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\CategoryRepository;
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
     * Test show action for user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowAction(): void
    {
        // given
        $expectedStatusCode = 200;
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_user_show_user@example.com');
        $this->httpClient->loginUser($user);
        $userId = $user->getId();
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$userId);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();
        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show action.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowActionAdmin(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_USER->value, UserRole::ROLE_ADMIN->value], 'test_user_show_admin@example.com');
        $user = $this->createUser([UserRole::ROLE_USER->value], 'user_to_test_show@example.com');
        $userId = $user->getId();
        $this->httpClient->loginUser($adminUser);
        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$userId);
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
        $this->httpClient->submitForm('usuń');

        // then
        $savedUser = $userRepository->findOneBy(['id' => $testUserId]);
        $this->assertNull($savedUser);
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
        $this->assertResponseRedirects('/');
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
        $this->httpClient->submitForm('usuń');

        // then
        $savedWallet = $walletRepository->findOneBy(['id' => $testWallet->getId()]);
        $savedUser = $userRepository->findOneBy(['id' => $adminUser->getId()]);
        $this->assertNull($savedWallet);
        $this->assertNull($savedUser);
    }

    /**
     * Test edit password.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditPassword(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_edit_password@example.com');
        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$user->getId().'/edit-password');

        // when
        $this->httpClient->submitForm('edytuj',
            [
                'user_password' => [
                    'password' => [
                        'first' => 'new_password',
                        'second' => 'new_password',
                    ],
                ],
            ]);

        // then
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
    }

    /**
     * Test edit user data.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditUserData(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'user_edit_data@example.com');
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userId = $user->getId();
        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$user->getId().'/edit');
        $editedEmail = 'user_edited_data@example.com';
        $editedFirstName = 'edited_first_name';
        $editedLastName = 'edited_last_name';

        // when
        $this->httpClient->submitForm('action.edit',
            [
                'user_data' => [
                    'email' => $editedEmail,
                    'firstName' => $editedFirstName,
                    'lastName' => $editedLastName,
                ],
            ]);

        // then
        $editedUser = $userRepository->findOneBy(['id' => $userId]);
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
        $this->assertEquals($editedEmail, $editedUser->getEmail());
        $this->assertEquals($editedFirstName, $editedUser->getFirstName());
        $this->assertEquals($editedLastName, $editedUser->getLastName());
    }

    /**
     * Test changing user role.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testChangeUserRole(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'admin_role_admin@example.com');
        $user = $this->createUser([UserRole::ROLE_USER->value], 'user_role_user@example.com');
        $this->httpClient->loginUser($adminUser);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$user->getId().'/edit-role');

        // when
        $this->httpClient->submitForm('edytuj',
            [
                'user_role' => [
                    'roles' => [
                        UserRole::ROLE_ADMIN->value,
                    ],
                ],
            ]);

        // then
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
        $userRepository = static::getContainer()->get(UserRepository::class);
        $editedUser = $userRepository->findOneBy(['email' => 'user_role_user@example.com']);
        $this->assertContains(UserRole::ROLE_ADMIN->value, $editedUser->getRoles());
    }

    /**
     * Test delete user with operation.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteUserWithOperation(): void
    {
        // given
        $userRepository = static::getContainer()->get(UserRepository::class);
        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $walletRepository = static::getContainer()->get(WalletRepository::class);

        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_delete_user_operation@example.com');
        $this->httpClient->loginUser($adminUser);

        $testCategory = $this->createCategory('test_category', $adminUser);
        $testWallet = $this->createWallet('test_wallet', $adminUser);
        $testOperation = $this->createOperation('test_operation', $adminUser, $testWallet, $testCategory);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$adminUser->getId().'/delete');

        // when
        $this->httpClient->submitForm('usuń');

        // then
        $savedOperation = $operationRepository->findOneBy(['id' => $testOperation->getId()]);
        $savedUser = $userRepository->findOneBy(['id' => $adminUser->getId()]);
        $this->assertNull($savedOperation);
        $this->assertNull($savedUser);
    }

    /**
     * Test block user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testBlockUser(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'admin_block_user@example.com');
        $this->httpClient->loginUser($adminUser);
        $user = $this->createUser([UserRole::ROLE_USER->value], 'user_to_block@example.com');
        $userId = $user->getId();

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$userId.'/block');

        // then
        $userRepository = static::getContainer()->get(UserRepository::class);
        $blockedUser = $userRepository->findOneBy(['id' => $userId]);
        $this->assertTrue($blockedUser->getBlocked());
    }

    /**
     * Test unblock user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testUnblockUser(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'admin_unblock_user@example.com');
        $this->httpClient->loginUser($adminUser);
        $user = $this->createUser([UserRole::ROLE_USER->value], 'user_to_unblock@example.com');
        $user->setBlocked(true);
        $userId = $user->getId();

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$userId.'/unblock');

        // then
        $userRepository = static::getContainer()->get(UserRepository::class);
        $blockedUser = $userRepository->findOneBy(['id' => $userId]);
        $this->assertFalse($blockedUser->getBlocked());
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
        $user->setFirstName('Test');
        $user->setLastName('User');
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
     * @param string   $title    Operation name
     * @param User     $user     User entity
     * @param Wallet   $wallet   Wallet entity
     * @param Category $category Category entity
     *
     * @return Operation Operation entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    private function createOperation(string $title, User $user, Wallet $wallet, Category $category): Operation
    {
        $operation = new Operation();
        $operation->setTitle($title);
        $operation->setAmount(0);
        $operation->setAuthor($user);
        $operation->setCategory($category);
        $operation->setWallet($wallet);
        $operation->setCurrentBalance($wallet->getBalance());
        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $operationRepository->save($operation, true);

        return $operation;
    }

    /**
     * Create category.
     *
     * @throws ContainerExceptionInterface
     */
    private function createCategory($title, User $user): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $category->setAuthor($user);
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category, true);

        return $category;
    }
}
