<?php
/**
 * Operation Controller tests.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Enum\UserRole;
use App\Entity\Operation;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Wallet;
use App\Repository\CategoryRepository;
use App\Repository\OperationRepository;
use App\Repository\TagRepository;
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
     * @const string TEST_ROUTE
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
        $expectedStatusCode = 200;
        $adminUser = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 'test_operation_admin@example.com');
        $this->httpClient->loginUser($adminUser);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE);
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
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
        $expectedOperation->setCategory($this->createCategory('testCategoryShowOperation', $adminUser));
        $wallet = $this->createWallet('wallet_show_operation', $adminUser, '100');
        $expectedOperation->setWallet($wallet);
        $expectedOperation->setCurrentBalance($wallet->getBalance());
        $expectedOperation->setAuthor($adminUser);
        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $operationRepository->save($expectedOperation, true);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$expectedOperation->getId());
        $result = $this->httpClient->getResponse();

        // then
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertSelectorTextContains('html h1', $expectedOperation->getTitle());
    }

    /**
     * Test show single operation for unauthorized user.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testShowOperationForUnauthorizedUser(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_operation_unauth@example.com');
        $this->httpClient->loginUser($user);

        $operationUser = $this->createUser([UserRole::ROLE_USER->value], 'operation_owner@example.com');
        $operation = new Operation();
        $operation->setTitle('unauthorized operation');
        $operation->setAmount(100);
        $wallet = $this->createWallet('wallet_show_operation_auth', $operationUser, '0');
        $operation->setWallet($wallet);
        $operation->setCurrentBalance($wallet->getBalance());
        $operation->setCategory($this->createCategory('category_show_operation_auth', $operationUser));
        $operation->setAuthor($operationUser);
        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $operationRepository->save($operation);
        $operationId = $operationRepository->findOneBy(['title' => 'unauthorized operation']);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$operationId->getId());

        // then
        $result = $this->httpClient->getResponse();
        $this->assertEquals(403, $result->getStatusCode());
    }

    /**
     * Test create operation.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateOperation(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_create_operation@example.com');
        $this->httpClient->loginUser($user);
        $operationTitle = 'createdOperation';
        $category = $this->createCategory('testCategoryCreateOperation', $user);
        $wallet = $this->createWallet('wallet_create_operation', $user, '0');
        $tag = $this->createTag('testTagCreateOperation', $user);
        $tag2 = $this->createTag('testTagCreateOperation2', $user);
        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/create'
        );

        // when
        $this->httpClient->submitForm(
            'zapisz',
            ['operation' => [
                'title' => $operationTitle,
                'amount' => '100',
                'category' => $category->getId(),
                'wallet' => $wallet->getId(),
                'tags' => $tag->getTitle().','.$tag2->getTitle(),
                ],
            ]
        );

        // then
        $savedOperation = $operationRepository->findOneBy(['title' => $operationTitle]);
        $this->assertEquals($operationTitle, $savedOperation->getTitle());

        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * Test create operation that would exceed wallet balance.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testCreateOperationExceedWalletBalance(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_operation_balance@example.com');
        $this->httpClient->loginUser($user);
        $category = $this->createCategory('testCategoryCreateOperationBalance', $user);
        $wallet = $this->createWallet('wallet_create_operation_balance', $user, '0');
        $operationTitle = 'test operation balance';
        $operationAmount = '-100';
        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/create'
        );

        // when
        $this->httpClient->submitForm(
            'zapisz',
            ['operation' => [
                'title' => $operationTitle,
                'amount' => $operationAmount,
                'category' => $category->getId(),
                'wallet' => $wallet->getId(),
                'tags' => 'testTagCreateOperationBalance',
                ],
            ]
        );

        // then
        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * Test edit operation.
     *
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditOperation(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'operation_edit_user@example.com');
        $this->httpClient->loginUser($user);

        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $testOperation = new Operation();
        $testOperation->setTitle('TestEditOperation');
        $testOperation->setAmount(100);
        $testOperation->setCategory($this->createCategory('testCategoryEditOperation', $user));
        $testOperationWallet = $this->createWallet('wallet_edit_operation', $user, '0');
        $testOperation->setWallet($testOperationWallet);
        $testOperation->setCurrentBalance($testOperationWallet->getBalance() + $testOperation->getAmount());
        $testOperation->setAuthor($user);
        $testOperation->addTag($this->createTag('testTagEditOperation', $user));
        $testOperation->addTag($this->createTag('testTagEditOperation2', $user));
        $testOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $testOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $operationRepository->save($testOperation);
        $testOperationId = $testOperation->getId();
        $expectedNewOperationTitle = 'TestOperationEdited';

        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/'.$testOperationId.'/edit'
        );

        // when
        $this->httpClient->submitForm(
            'edytuj',
            ['operation' => [
                'title' => $expectedNewOperationTitle,
                'amount' => 200,
                'category' => 2,
                'wallet' => $testOperationWallet->getId(),
                'tags' => 'abc, def',
                ],
            ]
        );

        // then
        $savedOperation = $operationRepository->findOneById($testOperationId);
        $this->assertEquals($expectedNewOperationTitle, $savedOperation->getTitle());

        $this->assertNotNull($savedOperation->getUpdatedAt());
        $this->assertNotNull($savedOperation->getCreatedAt());
    }

    /**
     * Test edit operation that would exceed wallet balance.
     *
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface|ORMException|OptimisticLockException
     */
    public function testEditOperationExceedWalletBalance(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_edit_exceed@example.com');
        $this->httpClient->loginUser($user);

        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $testOperation = new Operation();
        $testOperation->setTitle('TestEditOperationExceed');
        $testOperation->setAmount(100);
        $testOperation->setCategory($this->createCategory('testCategoryEditOperationExceed', $user));
        $testOperationWallet = $this->createWallet('wallet_edit_operation_exceed', $user, '0');
        $testOperation->setWallet($testOperationWallet);
        $testOperation->setCurrentBalance($testOperationWallet->getBalance());
        $testOperation->setAuthor($user);
        $testOperation->addTag($this->createTag('testTagEditOperationExceed', $user));
        $testOperation->addTag($this->createTag('testTagEditOperationExceed2', $user));
        $testOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $testOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $operationRepository->save($testOperation);
        $testOperationId = $testOperation->getId();
        $expectedNewOperationTitle = 'TestOperationEditedExceed';

        $this->httpClient->request(
            'GET',
            self::TEST_ROUTE.'/'.$testOperationId.'/edit'
        );

        // when
        $this->httpClient->submitForm(
            'edytuj',
            ['operation' => [
                'title' => $expectedNewOperationTitle,
                'amount' => -200,
                'category' => 2,
                'wallet' => $testOperationWallet->getId(),
                'tags' => 'abc, def',
                ],
            ]
        );

        // then
        $result = $this->httpClient->getResponse();
        $this->assertEquals(302, $result->getStatusCode());
    }

    /**
     * Test remove tag from operation.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testRemoveTagFromOperation(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_rm_tag_operation@example.com');
        $this->httpClient->loginUser($user);

        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $testOperation = new Operation();
        $testOperation->setTitle('TestRemoveTagOperation');
        $testOperation->setAmount(100);
        $testOperation->setCategory($this->createCategory('testCategoryRemoveTagOperation', $user));
        $testWallet = $this->createWallet('wallet_remove_tag_operation', $user, '0');
        $testOperation->setWallet($testWallet);
        $testOperation->setCurrentBalance($testWallet->getBalance());
        $testOperation->setAuthor($user);
        $testTag = $this->createTag('testTagRemoveOperation', $user);
        $testOperation->addTag($testTag);
        $testOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $testOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $operationRepository->save($testOperation);
        $testOperationId = $testOperation->getId();

        // when
        $testOperation->removeTag($testTag);
        $operationRepository->save($testOperation);
        $testOperationTags = $testOperation->getTags();

        // then
        $this->assertEmpty($testOperationTags);
    }

    /**
     * Test delete operation.
     *
     * @throws NotFoundExceptionInterface|ORMException|ContainerExceptionInterface|OptimisticLockException
     */
    public function testDeleteOperation(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_delete_operation_user@example.com');
        $this->httpClient->loginUser($user);

        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $testOperation = new Operation();
        $testOperation->setTitle('TestOperationDelete');
        $testOperation->setAmount(100);
        $testOperation->setCategory($this->createCategory('testCategoryDeleteOperation', $user));
        $testWallet = $this->createWallet('wallet_delete_operation', $user, '0');
        $testOperation->setWallet($testWallet);
        $testOperation->setCurrentBalance($testWallet->getBalance());
        $testOperation->setAuthor($user);
        $testOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $testOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $operationRepository->save($testOperation);
        $testOperationId = $testOperation->getId();

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testOperationId.'/delete');

        // when
        $this->httpClient->submitForm('usuń');

        // then
        $savedOperation = $operationRepository->findOneById($testOperationId);
        $this->assertNull($savedOperation);
    }

    /**
     * Test search operation by title.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testSearchOperationByTitle(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_search_operation@exampl.com');
        $this->httpClient->loginUser($user);
        $testOperation = new Operation();
        $testOperation->setTitle('TestOperationSearch');
        $testOperation->setAmount(100);
        $testOperation->setCategory($this->createCategory('testCategorySearchOperation', $user));
        $testWallet = $this->createWallet('wallet_search_operation', $user, '0');
        $testOperation->setWallet($testWallet);
        $testOperation->setCurrentBalance($testWallet->getBalance());
        $testOperation->setAuthor($user);
        $testOperation->setCreatedAt(new \DateTimeImmutable('now'));
        $testOperation->setUpdatedAt(new \DateTimeImmutable('now'));
        $operationRepository = static::getContainer()->get(OperationRepository::class);
        $operationRepository->save($testOperation);

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'?filters.operation_title='.$testOperation->getTitle());

        // then
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test operation that is not in the database.
     *
     *  @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    public function testOperationThatIsNotInTheDatabase(): void
    {
        // given
        $user = $this->createUser([UserRole::ROLE_USER->value], 'test_null_user@example.com');
        $this->httpClient->loginUser($user);
        $operationTitle = 'TestOperationThatIsNotInTheDatabase';

        // when
        $this->httpClient->request('GET', self::TEST_ROUTE.'?filters.operation_title='.$operationTitle);

        // then
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html', 'brak elementów');
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
    private function createWallet(string $title, User $user, string $balance = '0'): Wallet
    {
        $wallet = new Wallet();
        $wallet->setTitle($title);
        $wallet->setBalance($balance);
        $wallet->setUser($user);
        $wallet->setType('cash');
        $walletRepository = static::getContainer()->get(WalletRepository::class);
        $walletRepository->save($wallet, true);

        return $wallet;
    }

    /**
     * Create category.
     *
     * @throws ContainerExceptionInterface
     */
    private function createCategory(string $title, User $user): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $category->setAuthor($user);
        $category->setUserOrAdmin('admin');
        $categoryRepository = self::getContainer()->get(CategoryRepository::class);
        $categoryRepository->save($category);

        return $category;
    }

    /**
     * Create tag.
     *
     * @throws ContainerExceptionInterface
     */
    private function createTag(string $string, User $user): Tag
    {
        $tag = new Tag();
        $tag->setTitle($string);
        $tag->setAuthor($user);
        $tag->setUserOrAdmin('admin');
        $tagRepository = self::getContainer()->get(TagRepository::class);
        $tagRepository->save($tag);

        return $tag;
    }
}
