<?php
/**
 * Operation Controller test.
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
 * Class OperationControllerTest.
 */
class OperationControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/operation';

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
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_operation_admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/'
        );
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(301, $result->getStatusCode());
    }

    /**
     * Test show single operation.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowOperation(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_show_operation@example.com');
        $this->httpClient->loginUser($adminUser);

        $expectedOperation = new Operation();
        $expectedOperation->setTitle('Test 1 operation');
        $expectedOperation->setAmount(100);
        $expectedOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $expectedOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $expectedOperation->setCategory($this->createCategory('testCategoryShowOperation'));
        $expectedOperation->setWallet($this->createWallet($adminUser));
        $expectedOperation->setAuthor($adminUser);

        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $operationRepository->save($expectedOperation);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedOperation->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('html h1', $expectedOperation->getTitle());
    }
//
//    /**
//     * Test create operation.
//     *
//     * @throws OptimisticLockException|ORMException|NotFoundExceptionInterface|ContainerExceptionInterface
//     */
//    public function testCreateOperation(): void
//    {
//        // given
//        $user = $this->createUser(
//            [UserRole::ROLE_USER->value],
//            'test_operation_create@example.com'
//        );
//        $this->httpClient->loginUser($user);
//        $operationTitle = 'createdOperation';
//        $operationRepository = static::getContainer()->get(CategoryRepository::class);
//        $this->httpClient->request(
//            'GET',
//            self::TEST_ROUTE.'/create'
//        );
//
//        // when
//        $this->httpClient->submitForm(
//            'action.save',
//            ['operation' => [
//                    'title' => $operationTitle,
//                    'amount' => 100,
//                ],
//            ]
//        );
//
//        // then
//        $savedOperation = $operationRepository->findOneByTitle($operationTitle);
//        $this->assertEquals($operationTitle, $savedOperation->getTitle());
//
//        $result = $this->httpClient->getResponse();
//        $this->assertEquals(302, $result->getStatusCode());
//    }

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
     * Create Wallet.
     *
     * @param User $user User entity
     *
     * @throws ContainerExceptionInterface
     */
    private function createWallet(User $user): Wallet
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

    /**
     * Create category.
     *
     * @throws ContainerExceptionInterface
     */
    private function createCategory($title): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }
}
