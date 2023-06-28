<?php
/**
 * Category Controller test.
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
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';

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
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException|\Exception
     */
    public function testIndexRouteAdminUser(): void
    {
        // given
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_category__admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $resultStatusCode = $this->httpClient->getResponse()->getStatusCode();

        // then
        $this->assertEquals($expectedStatusCode, $resultStatusCode);
    }

    /**
     * Test show single category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowCategory(): void
    {
        // given
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_show_category@example.com');
        $this->httpClient->loginUser($adminUser);

        $expectedCategory = new Category();
        $expectedCategory->setTitle('Test 2 category');
        $expectedCategory->setUserOrAdmin('admin');
        $expectedCategory->setAuthor($adminUser);
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($expectedCategory);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedCategory->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('html h1', $expectedCategory->getTitle());
    }

    /**
     * Test create category.
     *
     * @throws OptimisticLockException|ORMException|NotFoundExceptionInterface|ContainerExceptionInterface
     */
    public function testCreateCategory(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_category_create@example.com');
        $this->httpClient->loginUser($user);
        $categoryTitle = 'createdCategory';
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/create'
        );

        // when
        $this->httpClient->submitForm(
            'zapisz',
            [
                'category' => [
                        'title' => $categoryTitle,
                    ],
            ]
        );

        // then
        $savedCategory = $categoryRepository->findOneByTitle($categoryTitle);
        $this->assertEquals($categoryTitle, $savedCategory->getTitle());

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * Test edit category with unauthorized user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditCategoryUnauthorizedUser(): void
    {
        // given
        $expectedHttpStatusCode = 403;

        $user = $this->createUser([UserRole::ROLE_USER->value], 'user_create_category@example.com');
        $unauthorizedUser = $this->createUser([UserRole::ROLE_USER->value], 'unauthorized_category@example.com');
        $this->httpClient->loginUser($unauthorizedUser);
        $category = new Category();
        $category->setTitle('TestEditAnAuthCategory');
        $category->setUserOrAdmin('admin');
        $category->setCreatedAt(new \DateTimeImmutable('now'));
        $category->setUpdatedAt(new \DateTimeImmutable('now'));
        $category->setAuthor($user);
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$category->getId().'/edit');
        $actual = $this->httpClient->getResponse();

        // then
        $this->assertEquals($expectedHttpStatusCode, $actual->getStatusCode());
    }

    /**
     * Test edit category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditCategory(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_category_edit@example.com');
        $this->httpClient->loginUser($user);

        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('edited Category');
        $testCategory->setUserOrAdmin('admin');
        $testCategory->setCreatedAt(new \DateTimeImmutable('now'));
        $testCategory->setUpdatedAt(new \DateTimeImmutable('now'));
        $testCategory->setSlug('edited-category');
        $testCategory->setAuthor($user);
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();
        $expectedNewCategoryTitle = 'Test Category Edit';
        $expectedNewCategoryTitleSlug = 'test-category-edit';

        $this->httpClient->request(
            'GET', self::TEST_ROUTE.'/'.
            $testCategoryId.'/edit'
        );

        // when
        $this->httpClient->submitForm(
            'edytuj',
            ['category' => ['title' => $expectedNewCategoryTitle]]
        );

        // then
        $savedCategory = $categoryRepository->findOneById($testCategoryId);
        $this->assertEquals($expectedNewCategoryTitle, $savedCategory->getTitle());
        $this->assertEquals($expectedNewCategoryTitleSlug, $savedCategory->getSlug());
    }

    /**
     * Test delete category.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testDeleteCategory(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_category_delete@example.com');
        $this->httpClient->loginUser($user);

        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated');
        $testCategory->setUserOrAdmin('admin');
        $testCategory->setCreatedAt(new \DateTimeImmutable('now'));
        $testCategory->setUpdatedAt(new \DateTimeImmutable('now'));
        $testCategory->setAuthor($user);
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testCategoryId.'/delete');

        // when
        $this->httpClient->submitForm(
            'usuń'
        );

        // then
        $this->assertNull($categoryRepository->findOneByTitle('TestCategoryCreated'));
    }

    /**
     * Test if category cant be deleted.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCantDeleteCategory(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_category_can_delete@example.com');
        $this->httpClient->loginUser($user);

        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $testCategory = new Category();
        $testCategory->setTitle('TestCategoryCreated2');
        $testCategory->setUserOrAdmin('admin');
        $testCategory->setCreatedAt(new \DateTimeImmutable('now'));
        $testCategory->setUpdatedAt(new \DateTimeImmutable('now'));
        $testCategory->setAuthor($user);
        $categoryRepository->save($testCategory);
        $testCategoryId = $testCategory->getId();

        $this->createOperation($user, $testCategory);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testCategoryId.'/delete');

        // then
        $this->assertEquals(302, $this->httpClient->getResponse()->getStatusCode());
        $this->assertNotNull($categoryRepository->findOneByTitle('TestCategoryCreated2'));
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
     * Create operation.
     *
     * @param User     $user         User entity
     * @param Category $testCategory Category entity
     *
     * @throws ContainerExceptionInterface
     */
    private function createOperation(User $user, Category $testCategory): Operation
    {
        $operation = new Operation();
        $operation->setTitle('TestOperation');
        $operation->setAmount(1000);
        $operation->setCategory($testCategory);
        $wallet = $this->createWallet($user, 'TestWallet');
        $operation->setWallet($wallet);
        $operation->setAuthor($user);
        $operation->setCurrentBalance($wallet->getBalance());

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
    protected function createWallet(User $user, string $title): Wallet
    {
        $wallet = new Wallet();
        $wallet->setTitle($title);
        $wallet->setType('cash');
        $wallet->setBalance('1000');
        $wallet->setUser($user);
        $walletRepository = self::getContainer()->get(WalletRepository::class);
        $walletRepository->save($wallet);

        return $wallet;
    }
}
